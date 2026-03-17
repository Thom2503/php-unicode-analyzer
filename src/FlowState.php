<?php

namespace Thom2503\PhpUnicodeAnalyzer;

/**
 * Keep track of the variables what kind of types they are etc.
 * 
 * @property array $vars - all the variables with their types
 * 
 * @method void set(string $name, StringKind $type) - set a variable in the $vars array
 * @method StringKind get(string $name) - fetch a variable from $vars
 * @method StringKind merge(StringKind $lhs, StringKind $rhs) - merge two variables if needed, MIXED is most likely the result
 * @method array all() - get all the vars
 */
class FlowState {
	private array $vars = [];

	public function set(string $name, StringKind $type): void {
		$this->vars[$name] = $type;
	}

	public function get(string $name): StringKind {
		return $this->vars[$name] ?? StringKind::UNKNOWN;
	}

	public function merge(StringKind $lhs, StringKind $rhs): StringKind {
		if ($lhs === $rhs) return $lhs;
		if ($lhs === StringKind::UNKNOWN) return $rhs;
		if ($rhs === StringKind::UNKNOWN) return $lhs;
		return StringKind::MIXED;
	}

	public function all(): array {
		return $this->vars;
	}
}