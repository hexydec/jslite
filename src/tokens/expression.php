<?php
declare(strict_types = 1);
namespace hexydec\jslite;
use \hexydec\tokens\tokenise;

class expression {

	public const significant = true;
	public $commands = [];
	public $eol;
	public $bracket = null;

	public function __construct(?string $bracket = null) {
		$this->bracket = $bracket;
	}

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
						if ($this->isKeyword($last, $tokens)) {
							$obj = new keyword($this);
							if ($obj->parse($tokens)) {
								$commands[] = $obj;
							}
							break;
						}
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

						// regexp is extremely awkward to capture, and because we only look ahead in the regexp, sometimes it can get it wrong
						if (!$last || $this->isRegexpAllowed($last, $beforelast)) {

							// create regexp object
							$obj = new regexp($this);
							if ($obj->parse($tokens)) {
								$commands[] = $obj;
							}

						// if we have got it wrong then the first character will be a divide
						} else {

							// rewind the tokeniser to start the next parse loop from after the divide
							$tokens->rewind(mb_strlen($token['value'])-1, 'operator');
							$tokens->prev(); // move the token pointer back so the operator can be parsed by the normal process
						}
						break;
					case 'whitespace':
						$end = false;

						// catch un-terminated line endings
						if ($last && \mb_strpos($token['value'], "\n") !== false) {
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
				if (($end = \end($commands)) !== false && $end::significant) {
					$beforelast = $last;
					$last = $end;
				}
			} while (($token = $tokens->next()) !== null);
		}
		$this->commands = $commands;
		return $commands || $this->eol;
	}

	protected function isKeyword($last, tokenise $tokens) {
		if (($next = $tokens->next(null, false)) !== null) {
			$tokens->prev();
			if (mb_strpos($next['value'], ':') === 0 || $next['value'] === '.') {
				return false;
			}
		} elseif ($last && get_class($last) === __NAMESPACE__.'\\operator' && $last->content === '.') {
			return false;
		}
		return true;
	}

	/**
	 * Works out whether a regular expression is legal in the current context
	 */
	protected function isRegexpAllowed($prev, $beforeprev = null) : bool {
		$key = __NAMESPACE__.'\\keyword';
		$bra = __NAMESPACE__.'\\brackets';
		$op = __NAMESPACE__.'\\operator';
		$prevclass = get_class($prev);

		// previous object is an operator or keyword, or the previous object is brackets and the one before that is keyword
		return in_array($prevclass, [$op, $key]) || ($beforeprev && $prevclass === $bra && get_class($beforeprev) === $key);
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
		$prevtype = \get_class($prev);
		$beforeprevtype = $beforeprev ? \get_class($beforeprev) : null;

		// class names in vars
		$key = __NAMESPACE__.'\\keyword';
		$bra = __NAMESPACE__.'\\brackets';
		$op = __NAMESPACE__.'\\operator';

		// check for kewords
		$keywords = ['debugger', 'continue', 'break', 'throw', 'return'];
		if ($prevtype === $key && \in_array($prev->content, $keywords, true)) {
			return true;

		// special case for keyword followed by brcket
		} elseif ($prevtype === $bra && $beforeprev && $beforeprevtype === $key) {
			return false;

		// if prev is curly then expression will have already ended
		} elseif ($prevtype === $bra && $prev->bracket === 'curly' && $beforeprevtype !== $op) {
			return false;

		// get next token
		} elseif (($next = $this->getNextSignificantToken($tokens)) === null) {
			return false;

		// next expression starts with a semi-colon
		} elseif ($next['type'] === 'keyword') {
			return true;

		// next value starts with a ~
		} elseif ($next['value'] === '~') {
			return true;

		// next value is a not
		} elseif ($prevtype !== $op && $next['value'] === '!') {
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
				if ('hexydec\\jslite\\'.$key == $prevtype && \in_array($next['type'], $item)) {
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
			if (!\in_array($token['type'], $ignore)) {
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
