<?php

namespace PhpUnicodeAnalyzer;

enum StringKind {
	case UNKNOWN;
	case UNICODE;
	case BINARY;
	case MIXED;
}