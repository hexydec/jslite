<?php
declare(strict_types = 1);
namespace hexydec\jslite;
use \hexydec\tokens\tokenise;

class expression {

	public const significant = true;
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
							$end = $this->isEol($tokens, $last, $beforelast, $commands);
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

				// record previous items
				if (($end = end($commands)) !== false && $end::significant) {
					$beforelast = $last;
					$last = $end;
				}
			} while (($token = $tokens->next()) !== null);
		}
		$this->commands = $commands;
		return $commands || $this->eol;
	}

	/**
	 * Determines if an expression should be ended when there is a line break between two commands
	 *
	 * @param tokenise $tokens A tokenise object to get the next tokens from
	 * @param mixed $prev The previous command object
	 * @param mixed $beforeprev The command object before the previous command object
	 * @return bool Whether the expression should end at the previous command
	 */
	protected function isEol(tokenise $tokens, $prev = null, $beforeprev = null) : bool {
		$prevtype = get_class($prev);
		$beforeprevtype = $beforeprev ? get_class($beforeprev) : null;

		// check for kewords
		$keywords = ['debugger', 'continue', 'break', 'throw', 'return'];
		if ($prevtype === 'hexydec\\jslite\\keyword' && in_array($prev->keyword, $keywords, true)) {
			return true;

		// special case for keyword followed by brcket
		} elseif ($prevtype === 'hexydec\\jslite\\brackets' && $beforeprev && $beforeprevtype === 'hexydec\\jslite\\keyword') {
			return false;

		// if prev is curly then expression will have already ended
		} elseif ($prevtype === 'hexydec\\jslite\\brackets' && $prev->bracket === 'curly' && $beforeprevtype !== 'hexydec\\jslite\\operator') {
			return false;

		// get next token
		} elseif (($next = $this->getNextSignificantToken($tokens)) === null) {
			return false;

		// next expression starts with a semi-colon
		} elseif ($next['type'] === 'keyword') {
			return true;

		// next value is a not
		} elseif ($prevtype !== 'hexydec\\jslite\\operator' && $next['value'] === '!') {
			return true;

		// see if the statement needs to be terminated
		} else {
			$end = [ // previous type => [next types]
				'brackets' => ['variable', 'number', 'string', 'increment'],
				'variable' => ['variable', 'string', 'number', 'regexp', 'opencurly', 'increment'],
				'number' => ['variable', 'number', 'string', 'regexp', 'openbracket', 'opensquare', 'opencurly', 'increment'],
				'string' => ['variable', 'number', 'string', 'regexp', 'openbracket', 'opensquare', 'opencurly', 'increment'],
				'regexp' => ['variable', 'number', 'string', 'regexp', 'openbracket', 'opensquare', 'opencurly', 'increment'],
				'increment' => ['variable', 'number', 'string', 'regexp', 'openbracket', 'opensquare', 'opencurly', 'increment']
			];
			foreach ($end AS $key => $item) {
				if ('hexydec\\jslite\\'.$key == $prevtype && in_array($next['type'], $item)) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Retrieve the next significant token, leaving the pointer at the current position
	 *
	 * @param tokenise $tokens A tokenise object to get the next tokens from
	 * @return ?array An array containing the next token or null if there is no next significant token
	 */
	protected function getNextSignificantToken(tokenise $tokens) : ?array {

		// get next tokens
		$rewind = 0;
		$next = null;
		$ignore = ['whitespace', 'commentsingle', 'commentmulti'];
		while (($token = $tokens->next(null, false)) !== null) {
			$rewind++;
			if (!in_array($token['type'], $ignore)) {
				$next = $token;
				break;
			}
		}
		if ($rewind) {
			$tokens->prev($rewind);
		}
		return $next;
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
	public function compile(array $options = []) : string {
		$js = '';
		foreach ($this->commands AS $item) {
			$js .= $item->compile($options);
		}
		return $js.$this->eol;
	}
}
