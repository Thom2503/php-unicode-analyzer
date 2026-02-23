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

		$traverser = new NodeTraverser();
		$traverser->addVisitor(new class($state) extends NodeVisitorAbstract {

			private FlowState $state;

			public function __construct($state) {
				$this->state = $state;
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
					if ($node->expr instanceof Node\Expr\Variable) {
						$source = '$'.$node->expr->name;
						$type = $this->state->get($source);
						$this->state->set($name, $type);
						return;
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
		});

		$traverser->traverse($ast);

		return $state->all();
	}
}