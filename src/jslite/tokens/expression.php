<?php
namespace hexydec\jslite;

class expression {

	public $commands = [];
	public $eol;

	/**
	 * Parses an array of tokens
	 *
	 * @param array &$tokens A tokenise object
	 * @return void
	 */
	public function parse(tokenise $tokens) : bool {
		$commands = [];
		if (($token = $tokens->current()) !== null) {
			do {
				switch ($token['type']) {
					case 'commentsingle':
					case 'commentmulti':
						$obj = new comment($this);
						if ($obj->parse($tokens)) {
							$commands[] = $obj;
						}
						break;
					case 'operator':
						$obj = new operator($this);
						if ($obj->parse($tokens)) {
							$commands[] = $obj;
						}
						break;
					case 'keyword':
						$obj = new keyword($this);
						if ($obj->parse($tokens)) {
							$commands[] = $obj;
						}
						break;
					case 'variable':
						$obj = new variable($this);
						if ($obj->parse($tokens)) {
							$commands[] = $obj;
						}
						break;
					case 'number':
						$obj = new number($this);
						if ($obj->parse($tokens)) {
							$commands[] = $obj;
						}
						break;
					case 'doublequotes':
					case 'singlequotes':
						$obj = new jsstring($this);
						if ($obj->parse($tokens)) {
							$commands[] = $obj;
						}
						break;
					case 'whitespace':
						$obj = new whitespace($this);
						if ($obj->parse($tokens)) {
							$commands[] = $obj;
						}
						break;
					case 'openbracket':
					case 'opensquare':
					case 'opencurly':
						$obj = new brackets($this);
						if ($obj->parse($tokens)) {
							$commands[] = $obj;
						}
						break;
					case 'eol':
					case 'comma':
						$this->eol = $token['value'];
					case 'closebracket':
					case 'closesquare':
					case 'closecurly':
						break 2;
				}
			} while (($token = $tokens->next()) !== null);
		}
		$this->commands = $commands;
		return !!$commands;
	}

	/**
	 * Minifies the internal representation of the document
	 *
	 * @param array $minify An array indicating which minification operations to perform, this is merged with htmldoc::$config['minify']
	 * @return void
	 */
	public function minify(array $minify = []) : void {

		// minify expressions
		foreach ($this->commands AS $item) {
			$item->minify($minify);
		}
	}

	/**
	 * Compile as Javascript
	 *
	 * @param array $options An array indicating output options
	 * @return string The compiled HTML
	 */
	public function output(array $options = []) : string {
		$js = '';
		foreach ($this->commands AS $item) {
			$js .= $item->output($options);
		}
		return $js.$this->eol;
	}
}
