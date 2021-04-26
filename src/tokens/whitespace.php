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
			$count = count($commands);
			$not = ['whitespace', 'comment'];
			foreach ($commands AS $i => $item) {
				if ($item === $this) {

					// first item
					if (!$i || !$prev) {
						$this->whitespace = '';

					} else {

						// get the next command that is not
						$prevtype = get_class($prev);
						$next = null;
						$nexttype = null;
						for ($n = $i + 1; $n < $count; $n++) {
							if ($commands[$n]::significant) {
								$next = $commands[$n];
								$nexttype = get_class($next);
								break;
							}
						}

						// remove whitespace if last in the parent expression
						if (!$next) {
							$this->whitespace = '';

							// terminate any statements that are not terminated
							if (!$eol) {
								$this->root->eol = ';';
							}

						// handle operators next to an increment
						} elseif ($prevtype === 'hexydec\\jslite\\operator' && $nexttype === 'hexydec\\jslite\\increment' && mb_strpos($commands[$i + 1]->compile(), $commands[$i - 1]->compile()) !== false) {
							$this->whitespace = ' ';

						// handled + + and - -
						} elseif ($prevtype == 'hexydec\\jslite\\operator' && $nexttype === 'hexydec\\jslite\\operator' && $prev->operator === $next->operator) {
							$this->whitespace = ' ';

						// keyword not followed by bracket
						} elseif (in_array('hexydec\\jslite\\keyword', [$prevtype, $nexttype]) && !in_array('hexydec\\jslite\\brackets', [$prevtype, $nexttype]) && !in_array('hexydec\\jslite\\operator', [$prevtype, $nexttype])) {
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
