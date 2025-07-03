<?php

/* Copyright (C) Anton Unger */

$source = file_get_contents(__FILE__);
$tokens = PhpToken::tokenize($source);
$num = count($tokens);
$prev = new PhpToken(T_BAD_CHARACTER, ' ');
$prev->prev = null;
$current = null;
$j = 0;
$vars = [];
$vi = 0;
$body = '';
while ($j < $num)
{
	$current = $tokens[$j];
	switch ($current->id)
	{
		case T_WHITESPACE:
		case T_COMMENT:
		case T_DOC_COMMENT:
			goto next;
		case T_VARIABLE:
			goto variable;
		default:
			goto out;
	}
	variable:
	$id = substr($current->text, 1);
	if ($id === 'GLOBALS' ||
		$id === '_SERVER' ||
		$id === '_GET' ||
		$id === '_POST' ||
		$id === '_FILES' ||
		$id === '_REQUEST'||
		$id === '_SESSION' ||
		$id === '_ENV' ||
		$id === '_COOKIE' ||
		$id === 'argc' ||
		$id === 'argv' ||
		$id === 'this')
	{
		goto out;
	}
	elseif ($prev->id === T_GLOBAL ||
			$prev->id === T_STATIC ||
			$prev->id === T_PUBLIC ||
			$prev->id === T_PRIVATE ||
			$prev->id === T_PROTECTED)
	{
		goto out;
	}
	elseif (!isset($vars[$id]))
	{
		$vars[$id] = '_' . dechex($vi++);
	}
	$current->text = "\${$vars[$id]}";

	out:
	$first = $current->text[0];
	$last  = $prev->text[strlen($prev->text) - 1];
	if (($last >= 'A' && $last <= 'Z' ||
		 $last >= 'a' && $last <= 'z' ||
		 $last >= '0' && $last <= '9' ||
		 $last == '_'
		) &&
		($first >= 'A' && $first <= 'Z' ||
		 $first >= 'a' && $first <= 'z' ||
		 $first >= '0' && $first <= '9' ||
		 $first == '_' || $first == '\\'))
	{
		$body .= ' ';
	}
	$body .= $current->text;
	$current->prev = $prev;
	$prev = $current;

	next:
	$j++;
}
echo $body;
