# php-unicode-analyzer
To analyze a directory where each variable can be flagged to be UNICODE or not.

## Requirements

- PHP 8.1 or newer
- Composer

## Installation

Clone or create the project, then install dependencies:
```bash
composer install
```

Generate autoload if needed:
```bash
composer dump-autoload -or
```

Make the CLI executable:
```bash
chmod +x bin/analyze
```

## Usage
Analyze a directory with PHP files:
```bash
./bin/analyze /path/to/php/project
```

## Output
The output is determined on rules based in `src/FunctionRules.php`.
This is an example of the output:
```
File: file.php 
  $var                     => UNICODE
  $arr[]                   => UNICODE
  $bar[][]                 => UNKNOWN
```
Based on the rules given it will say `UNICODE | BYTE | UNKNOWN`.

### Rules
This is an example of how the rules can be made it is a variable in `src/FunctionRules.php`.
``php
	private array $rules = [
		'strlen' => ['arg' => StringKind::BINARY],
		'mb_strlen' => ['arg' => StringKind::UNICODE],
		'substr' => ['return' => StringKind::BINARY],
		'mb_substr' => ['return' => StringKind::UNICODE],
		'file_get_contents' => ['return' => StringKind::BINARY],
		'json_decode' => ['return' => StringKind::UNICODE],
	];
```

## Structure
Here is the project structure:
```
bin/
  analyze             CLI file
src/
  Analyzer.php        Main engine 
  FileScanner.php     Recursively finds PHP files
  FlowState.php       Variable type tracking
  FunctionRules.php   Function and variable behaviour rules
  StringKind.php      Type constants
```
