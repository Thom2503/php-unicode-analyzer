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
		$traverser->addVisitor(new class($state, $rules) extends NodeVisitorAbstract {

			private FlowState $state;
			private FunctionRules $rules;

			public function __construct($state, $rules) {
				$this->state = $state;
				$this->rules = $rules;
			}

			public function enterNode(Node $node) {
				// $a = "text"
				if ($node instanceof Node\Expr\Assign) {
					$name = $this->resolveVariableName($node->var);
					if ($name === null) return;
					if ($node->expr instanceof Node\Scalar\String_) {
						$this->state->set($name, StringKind::UNICODE);
						return;
					}
					if ($node instanceof Node\Expr\Assign) {
						$name = $this->resolveVariableName($node->var);
						if ($name === null) return;
						$type = $this->resolveExprType($node->expr);
						$this->state->set($name, $type);
					}
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
		});

		$traverser->traverse($ast);

		return $state->all();
	}
}