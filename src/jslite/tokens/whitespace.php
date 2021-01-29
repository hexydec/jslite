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
		foreach ($commands AS $i => $item) {
			if ($item === $this) {

				// first item
				if (!$i || !isset($commands[$i + 1])) {
					$this->whitespace = '';

				} else {
					$prev = get_class($commands[$i - 1]);
					$next = get_class($commands[$i + 1]);

					// handle operators next to an increment
					if ($next == __NAMESPACE__.'\\increment' && $prev == __NAMESPACE__.'\\operator' && mb_strpos($commands[$i + 1]->compile(), $commands[$i - 1]->compile()) !== false) {
						$this->whitespace = ' ';

					// keyword not followed by bracket
					} elseif ($prev == __NAMESPACE__.'\\keyword' && $next != __NAMESPACE__.'\\brackets') {
						$this->whitespace = ' ';

					// remove whitespace
					} else {
						$this->whitespace = '';
					}
				}
				break;
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
