<?php
namespace hexydec\jslite;

class expression {

	protected $commands = [];
	protected $eol;

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
						$obj = new comment();
						if ($obj->parse($tokens)) {
							$commands[] = $obj;
						}
						break;
					case 'operator':
						$obj = new operator();
						if ($obj->parse($tokens)) {
							$commands[] = $obj;
						}
						break;
					case 'keyword':
						$obj = new keyword();
						if ($obj->parse($tokens)) {
							$commands[] = $obj;
						}
						break;
					case 'variable':
						$obj = new variable();
						if ($obj->parse($tokens)) {
							$commands[] = $obj;
						}
						break;
					case 'number':
						$obj = new number();
						if ($obj->parse($tokens)) {
							$commands[] = $obj;
						}
						break;
					case 'doublequotes':
					case 'singlequotes':
						$obj = new jsstring();
						if ($obj->parse($tokens)) {
							$commands[] = $obj;
						}
						break;
					case 'whitespace':
						$obj = new whitespace();
						if ($obj->parse($tokens)) {
							$commands[] = $obj;
						}
						break;
					case 'openbracket':
						$obj = new brackets();
						if ($obj->parse($tokens)) {
							$commands[] = $obj;
						}
						break;
					case 'eol':
						$this->eol = $token['value'];
					case 'comma':
					case 'closebracket':
						break 2;
				}
			} while (($token = $tokens->next()) !== null);
		}
		$this->commands = $commands;
		return !!$commands;
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
