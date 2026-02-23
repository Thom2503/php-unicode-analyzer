<?php

namespace Thom2503\PhpUnicodeAnalyzer;

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