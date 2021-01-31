<?php
declare(strict_types = 1);
namespace hexydec\jslite;

class whitespace {

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
		$commands = $this->root->commands;
		$prev = null;
		$count = count($commands);
		$not = [__NAMESPACE__.'\\whitespace', __NAMESPACE__.'\\comment'];
		foreach ($commands AS $i => $item) {
			if ($item === $this) {

				// first item
				if (!$i || !isset($commands[$i + 1])) {
					$this->whitespace = '';

				} else {

					// get the next command that is not
					$next = null;
					for ($n = $i + 1; $n < $count; $n++) {
						$cls = get_class($commands[$n]);
						if (!in_array($cls, $not)) {
							$next = $cls;
							break;
						}
					}

					// remove whitespace if last in the parent exprssion
					if (!$next) {
						$this->whitespace = '';

					// handle operators next to an increment
					} elseif ($next == __NAMESPACE__.'\\increment' && $prev == __NAMESPACE__.'\\operator' && mb_strpos($commands[$i + 1]->compile(), $commands[$i - 1]->compile()) !== false) {
						$this->whitespace = ' ';

					// keyword not followed by bracket
					} elseif (in_array(__NAMESPACE__.'\\keyword', [$prev, $next]) && !in_array(__NAMESPACE__.'\\brackets', [$prev, $next])) {
						$this->whitespace = ' ';

					// remove whitespace
					} else {
						$this->whitespace = '';
					}
				}
				break;
			} else {
				$cls = get_class($item);
				if (!in_array($cls, $not)) {
					$prev = $cls;
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
