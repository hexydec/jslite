<?php
declare(strict_types = 1);
namespace hexydec\jslite;

class brackets {

	protected $expressions = [];

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
		while (($token = $tokens->next()) !== null) {
			if ($token['type'] != 'comma') {
				$obj = new expression();
				if ($obj->parse($tokens)) {
					$this->expressions[] = $obj;
				}
				if (($token = $tokens->current()) !== null && $token['type'] == 'closebracket') {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Compile as Javascript
	 *
	 * @param array $options An array indicating output options
	 * @return string The compiled HTML
	 */
	public function output(array $options = []) : string {
		$js = '';
		foreach ($this->expressions AS $key => $item) {
			if ($key) {
				$js .= ',';
			}
			$js .= $item->output($options);
		}
		return '('.$js.')';
	}
}
