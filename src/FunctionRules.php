<?php

namespace Thom2503\PhpUnicodeAnalyzer;

class FunctionRules {
	private array $rules = [
		'strlen' => ['arg' => StringKind::BINARY],
		'mb_strlen' => ['arg' => StringKind::UNICODE],
		'substr' => ['return' => StringKind::BINARY],
		'mb_substr' => ['return' => StringKind::UNICODE],
		'file_get_contents' => ['return' => StringKind::BINARY],
		'json_decode' => ['return' => StringKind::UNICODE],
		'Str::len' => ['return' => StringKind::UNICODE],
	];

	public function get(string $name): ?array {
		return $this->rules[strtolower($name)] ?? null;
	}
}