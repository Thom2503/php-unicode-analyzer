<?php

namespace Thom2503\PhpUnicodeAnalyzer;

use PhpParser\ParserFactory;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

class Analyzer {
	public function analyzeDirectory(string $dir): array {
		$scanner = new FileScanner();
		$files = $scanner->scan($dir);

		$results = [];

		foreach ($files as $file) {
			$results[$file] = $this->analyzeFile($file);
		}

		return $results;
	}

	private function analyzeFile(string $file): array {
		$code = file_get_contents($file);

		$parser = (new ParserFactory())->createForNewestSupportedVersion();
		$ast = $parser->parse($code);

		$state = new FlowState();
		$rules = new FunctionRules();

		$traverser = new NodeTraverser();
		$visitor = new class($state, $rules) extends NodeVisitorAbstract {

			private FlowState $state;
			private FunctionRules $rules;
			private array $violations = [];

			public function __construct($state, $rules) {
				$this->state = $state;
				$this->rules = $rules;
			}

			public function enterNode(Node $node) {
				if ($node instanceof Node\Expr\Assign) {
					$name = $this->resolveVariableName($node->var);
					if ($name === null) return;
			
					$type = $this->resolveExprType($node->expr);
					$this->state->set($name, $type);
					return;
				}
				if ($node instanceof Node\Expr\FuncCall) {
					$this->isValidFunctionCall($node);
					return;
				}
			}

			private function resolveVariableName($var): ?string {
				if ($var instanceof Node\Expr\Variable) {
					return is_string($var->name) ? '$'.$var->name : null;
				}
				if ($var instanceof Node\Expr\ArrayDimFetch) {
					$root = $this->resolveVariableName($var->var);
					if ($root === null) return null;

					return $root.'[]';
				}
				if ($var instanceof Node\Expr\PropertyFetch) {
					if ($var->name instanceof Node\Identifier) {
						return '$obj->'.$var->name->name;
					}
					return null;
				}
				return null;
			}

			private function resolveExprType($expr): StringKind {
				if ($expr instanceof Node\Scalar\String_) {
					return StringKind::UNICODE;
				}

				if ($expr instanceof Node\Expr\Variable) {
					$name = is_string($expr->name) ? '$' . $expr->name : null;
					return $name ? $this->state->get($name) : StringKind::UNKNOWN;
				}

				// To handle $a . $b etc.
				if ($expr instanceof Node\Expr\BinaryOp\Concat) {
					$left = $this->resolveExprType($expr->left);
					$right = $this->resolveExprType($expr->right);
					return $this->state->merge($left, $right);
				}

				if ($expr instanceof Node\Expr\FuncCall) {
					if ($expr->name instanceof Node\Name) {
						$fn = strtolower($expr->name->toString());
			
						$rule = $this->rules->get($fn);
						if ($rule && isset($rule['return'])) {
							return $rule['return'];
						}
					}
					return StringKind::UNKNOWN;
				}

				return StringKind::UNKNOWN;
			}

			private function isValidFunctionCall($call): void {
				if (!$call->name instanceof Node\Name) {
					return;
				}

				$fun = strtolower($call->name->toString());
				$rule = $this->rules->get($fun);

				if (!$rule) return;
				if (!isset($rule['arg'])) return;
				if (!isset($call->args[0])) return;

				$expected = $rule['arg'];
				$actual = $this->resolveExprType($call->args[0]->value);

				if (!self::isCompatible($actual, $expected)) {
					$line = $call->getStartLine();
					$this->violations[] = [
						'line' => $line,
						'function' => $fun,
						'expected' => $expected->value,
						'actual' => $actual->value,
					];
				}
			}

			private static function isCompatible(StringKind $actual, StringKind $expected): bool {
				if ($actual === StringKind::UNKNOWN) return true;
				if ($actual === $expected) return true;
				return false;
			}

			public function getViolations(): array {
				return $this->violations;
			}
		};

		$traverser->addVisitor($visitor);
		$traverser->traverse($ast);

		return ['state' => $state->all(), 'violations' => $visitor->getViolations()];
	}
}