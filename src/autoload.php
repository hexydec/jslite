<?php
spl_autoload_register(function (string $class) : bool {
	$dir = __DIR__.'/jslite';
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
		'hexydec\\jslite\\brackets' => $dir.'/tokens/brackets.php'
	];
	if (isset($classes[$class])) {
		return require($classes[$class]);
	}
	return false;
});
