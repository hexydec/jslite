<?php
spl_autoload_register(function (string $class) : bool {
	if ($class == 'hexydec\\jslite\\jslite') {
		return require(__DIR__.'/jslite.php');
	}
	return false;
});
