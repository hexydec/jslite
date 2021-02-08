<?php
namespace hexydec\jslite;

class expression {

	const type = 'expression';
	const significant = true;
	public $commands = [];
	public $eol;

	/**
	 * Parses an array of tokens
	 *
	 * @param array &$tokens A tokenise object
	 * @return bool Whether any commands or an EOL was captured
	 */
	public function parse(tokenise $tokens) : bool {
		$commands = [];
		if (($token = $tokens->current()) !== null) {
			$beforelast = null;
			$last = null;
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
					case 'increment':
						$obj = new increment($this);
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
					case 'templateliterals':
						$obj = new jsstring($this);
						if ($obj->parse($tokens)) {
							$commands[] = $obj;
						}
						break;
					case 'regexp':
						$obj = new regexp($this);
						if ($obj->parse($tokens)) {
							$commands[] = $obj;
						}
						break;
					case 'whitespace':
						$end = false;

						// catch un-terminated line endings
						if ($last && mb_strpos($token['value'], "\n") !== false) {

							// check previous token
							if (!in_array($last, ['operator', 'keyword']) && ($last != 'brackets' || $beforelast != 'keyword')) {
								$next = null;
								$rewind = 0;
								while (($token = $tokens->next(false)) !== null) {
									$rewind++;
									if (!in_array($token['type'], ['whitespace', 'commentsingle', 'commentmulti'])) {
										$next = $token;
										break;
									}
								}
								for ($i = 0; $i < $rewind; $i++) {
									$tokens->prev();
								}
								if ($next) {
									// var_dump($beforelast, $last, $next['type']);

									// if the next significant token is a new command, then start a new expression
									if ((!in_array($token['type'], ['operator', 'openbracket', 'opensquare', 'opencurly', 'closebracket', 'closesquare', 'closecurly', 'eol']) && ($last != 'brackets' || $token['type'] != 'keyword')) || ($token['type'] == 'operator' && mb_strpos($token['value'], '!') === 0)) { // ! is a special case here
										$end = true;
									}
								}
							}
						}

						// create whitespace object
						$obj = new whitespace($this);
						if ($obj->parse($tokens)) {
							$commands[] = $obj;
						}
						if ($end) {
							break 2;
						} else {
							break;
						}
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

				// record as previous items
				$end = end($commands);
				if ($end::significant) {
					$beforelast = $last;
					$last = $end::type;
				}
			} while (($token = $tokens->next()) !== null);
		}
		$this->commands = $commands;
		return $commands || $this->eol;
	}

	/**
	 * Minifies the internal representation of the document
	 *
	 * @param array $minify An array indicating which minification operations to perform, this is merged with htmldoc::$config['minify']
	 * @return void
	 */
	public function minify(array $minify = []) : void {

		// make sure all expressions are terminated
		if (!$this->eol) {
			foreach ($this->commands AS $item) {
				if ($item::significant) {
					$this->eol = ';';
					break;
				}
			}
		}

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
	public function compile(array $options = []) : string {
		$js = '';
		foreach ($this->commands AS $item) {
			$js .= $item->compile($options);
		}
		return $js.$this->eol;
	}
}
