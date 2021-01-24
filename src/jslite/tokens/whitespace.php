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
		// $this->scopes = $scopes;
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
		$key = __NAMESPACE__.'\\keyword';
		$not = __NAMESPACE__.'\\brackets';
		$commands = $this->root->commands;
		foreach ($commands AS $i => $item) {
			if ($item === $this) {
				$this->whitespace = $i && get_class($commands[$i -1]) == $key && (!isset($commands[$i + 1]) || get_class($commands[$i + 1]) != $not) ? ' ' : '';
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
