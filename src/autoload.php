<?php
\spl_autoload_register(function (string $class) : void {
	$classes = [
		'hexydec\\jslite\\jslite' => __DIR__.'/jslite.php',
		'hexydec\\jslite\\whitespace' => __DIR__.'/tokens/whitespace.php',
		'hexydec\\jslite\\comment' => __DIR__.'/tokens/comment.php',
		'hexydec\\jslite\\keyword' => __DIR__.'/tokens/keyword.php',
		'hexydec\\jslite\\operator' => __DIR__.'/tokens/operator.php',
		'hexydec\\jslite\\increment' => __DIR__.'/tokens/increment.php',
		'hexydec\\jslite\\number' => __DIR__.'/tokens/number.php',
		'hexydec\\jslite\\jsstring' => __DIR__.'/tokens/string.php',
		'hexydec\\jslite\\variable' => __DIR__.'/tokens/variable.php',
		'hexydec\\jslite\\regexp' => __DIR__.'/tokens/regexp.php',
		'hexydec\\jslite\\expression' => __DIR__.'/tokens/expression.php',
		'hexydec\\jslite\\brackets' => __DIR__.'/tokens/brackets.php'
	];
	if (isset($classes[$class])) {
		require($classes[$class]);
	}
});
