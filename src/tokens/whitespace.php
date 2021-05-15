<?php
declare(strict_types = 1);
namespace hexydec\jslite;
use \hexydec\tokens\tokenise;

class whitespace {

	public const significant = false;
	protected $root;
	protected $whitespace;

	/**
	 * Constructs the comment object
	 *
	 * @param jslite $root The parent jslite object
	 * @param array $scopes An array of variables that are available in this scope, where the key is the variable name and the value is the scope object
	 */
	public function __construct(expression $root) {
		$this->root = $root;
	}

	/**
	 * Parses an array of tokens
	 *
	 * @param array &$tokens A tokenise object
	 * @return void
	 */
	public function parse(tokenise $tokens) : bool {
		if (($token = $tokens->current()) !== null) {
			$this->whitespace = $token['value'];
			return true;
		}
		return false;
	}

	/**
	 * Minifies the internal representation of the document
	 *
	 * @param array $minify An array indicating which minification operations to perform, this is merged with htmldoc::$config['minify']
	 * @return void
	 */
	public function minify(array $minify = []) : void {
		if ($minify['whitespace']) {
			$commands = $this->root->commands;
			$eol = $this->root->eol;
			$prev = null;
			$count = \count($commands);
			$not = ['whitespace', 'comment'];

			// specify class names
			$op = __NAMESPACE__.'\\operator';
			$inc = __NAMESPACE__.'\\increment';
			$key = __NAMESPACE__.'\\keyword';
			$bra = __NAMESPACE__.'\\brackets';

			// loop through commands
			foreach ($commands AS $i => $item) {
				if ($item === $this) {

					// first item
					if (!$i || !$prev) {
						$this->whitespace = '';

					} else {

						// get the next command that is not
						$prevtype = \get_class($prev);
						$next = null;
						$nexttype = null;
						for ($n = $i + 1; $n < $count; $n++) {
							if ($commands[$n]::significant) {
								$next = $commands[$n];
								$nexttype = \get_class($next);
								break;
							}
						}

						// remove whitespace if last in the parent expression
						if (!$next) {

							// terminate any statements that are not terminated
							if (!$eol && mb_strpos($this->whitespace, "\n") !== false) {
								$this->root->eol = ';';
							}
							$this->whitespace = '';

						// handle operators next to an increment
						} elseif ($prevtype === $op && $nexttype === $inc && \mb_strpos($next->compile(), $prev->compile()) !== false) {
							$this->whitespace = ' ';

						// handled + + and - -
						} elseif ($prevtype == $op && $nexttype === $op && $prev->content === $next->content) {
							$this->whitespace = ' ';

						// keyword not followed by bracket
						} elseif (\in_array($key, [$prevtype, $nexttype], true) && !\in_array($bra, [$prevtype, $nexttype], true) && !\in_array($op, [$prevtype, $nexttype], true)) {
							$this->whitespace = ' ';

						// sqaure bracket next to keyword, leave a space
						} elseif ($prevtype === $bra && $nexttype === $key && $prev->bracket === 'square') {
							$this->whitespace = ' ';

						// remove whitespace
						} else {
							$this->whitespace = '';
						}
					}
					break;
				} elseif ($item::significant) {
					$prev = $item;
				}
			}
		}
	}

	/**
	 * Compile as Javascript
	 *
	 * @param array $options An array indicating output options
	 * @return string The compiled HTML
	 */
	public function compile(array $options = []) : string {
		return $this->whitespace;
	}
}
