<?php

namespace Thom2503\PhpUnicodeAnalyzer;

/**
 * Rules to specify what functions have what kind of string kinds as either arguments or return types.
 * 
 * @property array $rules - private array of rules to determine the kinds for several functions
 * 
 * @method ?array get(string $name) - search for a name in the $rules to get the StringKind.
 */
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