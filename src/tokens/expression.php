<?php
declare(strict_types = 1);
namespace hexydec\jslite;
use \hexydec\tokens\tokenise;

class expression {

	/**
	 * @var bool Denotes whether the class represents significant javascript
	 */
	public const significant = true;

	/**
	 * @var array An array of command objects stored within this expression
	 */
	public array $commands = [];

	/**
	 * @var ?string The end of line marker
	 */
	public ?string $eol = null;

	/**
	 * @var ?string The type of bracket of the parent
	 */
	public ?string $bracket = null;

	/**
	 * Constructs the expression object
	 * 
	 * @param ?string $bracket The type of bracket of the parent
	 */
	public function __construct(?string $bracket = null) {
		$this->bracket = $bracket;
	}

	/**
	 * Parses an array of tokens
	 *
	 * @param tokenise &$tokens A tokenise object
	 * @return bool Whether any commands or an EOL was captured
	 */
	public function parse(tokenise $tokens) : bool {
		$commands = [];
		if (($token = $tokens->current()) !== null) {
			$beforelast = null;
			$last = null;
			$assignment = false;
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
							if ($token['value'] === '=') {
								$assignment = true;
							}
						}
						break;
					case 'increment':
						$obj = new increment();
						if ($obj->parse($tokens)) {
							$commands[] = $obj;
						}
						break;
					case 'keyword':
						if ($this->isKeyword($last, $token, $tokens)) {
							$obj = new keyword();
							if ($obj->parse($tokens)) {
								$commands[] = $obj;
							}
							break;
						}
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
					case 'templateliterals':
						$obj = new jsstring();
						if ($obj->parse($tokens)) {
							$commands[] = $obj;
						}
						break;
					case 'regexp':

						// regexp is extremely awkward to capture, and because we only look ahead in the regexp, sometimes it can get it wrong
						if (!$last || $this->isRegexpAllowed($last, $beforelast)) {

							// create regexp object
							$obj = new regexp();
							if ($obj->parse($tokens)) {
								$commands[] = $obj;
							}

						// if we have got it wrong then the first character will be a divide
						} else {

							// rewind the tokeniser to start the next parse loop from after the divide
							$tokens->rewind(\mb_strlen($token['value'])-1, 'operator');
							$tokens->prev(); // move the token pointer back so the operator can be parsed by the normal process
						}
						break;
					case 'whitespace':
						$end = false;

						// catch un-terminated line endings
						if ($last && \mb_strpos($token['value'], "\n") !== false) {
							$end = $this->isEol($tokens, $last, $beforelast, $assignment);
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

	/**
	 * Works out whether a keyword is legal in the current context
	 */
	protected function isKeyword($prev, array $current, tokenise $tokens) {
		if (($next = $this->getNextSignificantToken($tokens)) !== null) {

			// property name
			if (\mb_strpos($next['value'], ':') === 0 || $next['value'] === '.') {
				return false;

			// var undefined
			// } elseif ($current['value'] === 'undefined') {
			//
			// 	// is a variable definition
			// 	if ($prev && \in_array($prev->content, ['const', 'let', 'var'])) {
			// 		return false;
			//
			// 	// followed by an assignment, comma, or EOL
			// 	} elseif (!$prev && \in_array($next['value'], ['=', ',', ';'])) {
			// 		return false;
			// 	}
			}
		} elseif ($prev && \get_class($prev) === __NAMESPACE__.'\\operator' && $prev->content === '.') {
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
		$prevclass = \get_class($prev);

		// previous object is an operator or keyword
		if (\in_array($prevclass, [$op, $key], true)) {
			return true;

		//, or the previous object is brackets and the one before that is keyword
		} elseif ($beforeprev && $prevclass === $bra && $prev->bracket === 'bracket' && \get_class($beforeprev) === $key && $beforeprev->content !== 'return') {
			return true;
		}
		return false;
	}

	/**
	 * Determines if an expression should be ended when there is a line break between two commands
	 *
	 * @param tokenise $tokens A tokenise object to get the next tokens from
	 * @param mixed $prev The previous command object
	 * @param mixed $beforeprev The command object before the previous command object
	 * @return bool Whether the expression should end at the previous command
	 */
	protected function isEol(tokenise $tokens, $prev = null, $beforeprev = null, bool $assignment = false) : bool {
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

		// special case for keyword followed by bracket
		} elseif ($prevtype === $bra && $beforeprevtype === $key && !\in_array($beforeprev->content, $keywords, true)) {
			return false;

		// if prev is curly then expression will have already ended
		} elseif ($prevtype === $bra && $prev->bracket === 'curly' && $beforeprevtype !== $op) {
			return $assignment;

		// get next token
		} elseif (($next = $this->getNextSignificantToken($tokens)) === null) {
			return false;

		// if the previous expression is an operator, like + or =, then the expression must end if next not an operator
		} elseif ($beforeprevtype === $op && !\in_array($next['type'], ['operator', 'openbracket', 'eol'])) {
			return true;

		// next expression starts with a keyword
		} elseif ($prevtype !== $op && $next['type'] === 'keyword') {
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
				if ('hexydec\\jslite\\'.$key === $prevtype && \in_array($next['type'], $item, true)) {
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
			if (!\in_array($token['type'], $ignore, true)) {
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
	 * @return string The compiled HTML
	 */
	public function compile() : string {
		$js = '';
		foreach ($this->commands AS $item) {
			$js .= $item->compile();
		}
		return $js.$this->eol;
	}
}
