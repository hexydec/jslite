<?php
declare(strict_types = 1);
namespace hexydec\jslite;

class operator {

	/**
	 * @var string The text content of this object
	 */
	protected $operator = null;

	/**
	 * Parses an array of tokens
	 *
	 * @param array &$tokens A tokenise object
	 * @return void
	 */
	public function parse(tokenise $tokens) : bool {
		if (($token = $tokens->current()) !== null) {
			$this->operator = $token['value'];
			return true;
		}
		return false;
	}

	/**
	 * Minifies the internal representation of the comment
	 *
	 * @param array $minify An array of minification options controlling which operations are performed
	 * @return void
	 */
	public function minify(array $minify) : void {
	}

	/**
	 * Compile as Javascript
	 *
	 * @param array $options An array indicating output options
	 * @return string The compiled Javascript
	 */
	public function output(array $options = []) : string {
		return $this->operator;
	}
}