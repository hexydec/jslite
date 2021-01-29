<?php
declare(strict_types = 1);
namespace hexydec\jslite;

class brackets {

	protected $expressions = [];
	protected $type = 'bracket'; // square or bracket

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
		if (($token = $tokens->current()) !== false) {
			$this->type = mb_substr($token['type'], 4);
			while (($token = $tokens->next()) !== null) {
				if ($token['type'] != 'comma') {
					$obj = new expression();
					if ($obj->parse($tokens)) {
						$this->expressions[] = $obj;
					}
					if (($token = $tokens->current()) !== null && $token['type'] == 'close'.$this->type) {
						return true;
					}
				}
			}
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

		// minify expressions
		foreach ($this->expressions AS $item) {
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
		$brackets = [
			'square' => ['[', ']'],
			'bracket' => ['(', ')'],
			'curly' => ['{', '}'],
		];
		$bracket = $brackets[$this->type];
		$js = '';
		if ($this->expressions) {
			foreach ($this->expressions AS $key => $item) {
				$js .= $item->compile($options);
			}
			$item->eol = null;
		}
		return $bracket[0].$js.$bracket[1];
	}
}
