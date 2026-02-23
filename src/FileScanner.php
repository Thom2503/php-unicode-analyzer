<?php

namespace Thom2503\PhpUnicodeAnalyzer;

class FileScanner {
	public function scan(string $dir): array {
		$files = [];
	
		$iterator = new \RecursiveIteratorIterator(
		    new \RecursiveDirectoryIterator($dir)
		);
	
		foreach ($iterator as $file) {
			if ($file->isFile() && $file->getExtension() === 'php') {
				$files[] = $file->getPathname();
			}
		}
	
		return $files;
	}
}