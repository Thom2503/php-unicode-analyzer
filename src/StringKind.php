<?php

namespace Thom2503\PhpUnicodeAnalyzer;

/**
 * StrinKind enum, to specify what kind of string the variable is. 
 */
enum StringKind: string {
	case UNKNOWN = "UNKNOWN";
	case UNICODE = "UNICODE";
	case BINARY = "BINARY";
	case MIXED = "MIXED";
}