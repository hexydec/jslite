<?php
spl_autoload_register(function (string $class) : bool {
	$dir = __DIR__.'/jslite';
	// $dir = __DIR__;
	$classes = [
		'hexydec\\jslite\\jslite' => $dir.'/jslite.php',
		'hexydec\\jslite\\tokenise' => $dir.'/tokenise.php',
		'hexydec\\jslite\\whitespace' => $dir.'/tokens/whitespace.php',
		'hexydec\\jslite\\comment' => $dir.'/tokens/comment.php',
		'hexydec\\jslite\\keyword' => $dir.'/tokens/keyword.php',
		'hexydec\\jslite\\operator' => $dir.'/tokens/operator.php',
		'hexydec\\jslite\\increment' => $dir.'/tokens/increment.php',
		'hexydec\\jslite\\number' => $dir.'/tokens/number.php',
		'hexydec\\jslite\\jsstring' => $dir.'/tokens/string.php',
		'hexydec\\jslite\\variable' => $dir.'/tokens/variable.php',
		'hexydec\\jslite\\regexp' => $dir.'/tokens/regexp.php',
		'hexydec\\jslite\\expression' => $dir.'/tokens/expression.php',
		'hexydec\\jslite\\brackets' => $dir.'/tokens/brackets.php',
		// 'hexydec\\html\\token' => $dir.'/tokens/interfaces/token.php',
		// 'hexydec\\html\\comment' => $dir.'/tokens/comment.php',
		// 'hexydec\\html\\doctype' => $dir.'/tokens/doctype.php',
		// 'hexydec\\html\\pre' => $dir.'/tokens/pre.php',
		// 'hexydec\\html\\script' => $dir.'/tokens/script.php',
		// 'hexydec\\html\\style' => $dir.'/tokens/style.php',
		// 'hexydec\\html\\tag' => $dir.'/tokens/tag.php',
		// 'hexydec\\html\\text' => $dir.'/tokens/text.php'
	];
	if (isset($classes[$class])) {
		return require($classes[$class]);
	}
	return false;
});
