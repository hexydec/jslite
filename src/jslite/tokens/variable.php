<?php
declare(strict_types = 1);
namespace hexydec\jslite;

class variable {

	protected $root;
	protected $scopes = [];
	protected $name = [];

	/**
	 * Constructs the comment object
	 *
	 * @param jslite $root The parent jslite object
	 * @param array $scopes An array of variables that are available in this scope, where the key is the variable name and the value is the scope object
	 */
	public function __construct() {
		// $this->root = $root;
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
			$var = true;
			do {
				if ($var) {
					if ($token['type'] == 'variable') {
						$this->name[] = $token['value'];
						$var = false;
					} else {
						break;
					}
				} elseif ($token['type'] == 'dot') {
					$var = true;
				} elseif ($token['type'] !== 'whitespace') {
					break;
				}
			} while (($token = $tokens->next()) !== null);
			$token = $tokens->prev();
			if ($token['type'] == 'whitespace') {
				$tokens->prev();
			}
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
	}

	/**
	 * Compile as Javascript
	 *
	 * @param array $options An array indicating output options
	 * @return string The compiled HTML
	 */
	public function output(array $options = []) : string {
		return implode('.', $this->name);
	}
}