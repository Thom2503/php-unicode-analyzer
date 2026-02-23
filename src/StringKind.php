<?php

namespace Thom2503\PhpUnicodeAnalyzer;

enum StringKind: string {
	case UNKNOWN = "UNKNOWN";
	case UNICODE = "UNICODE";
	case BINARY = "BINARY";
	case MIXED = "MIXED";
}