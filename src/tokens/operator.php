<?php
declare(strict_types = 1);
namespace hexydec\jslite;
use \hexydec\tokens\tokenise;

class operator {

	public const significant = true;
	/**
	 * @var string The text content of this object
	 */
	public $content = null;

	/**
	 * Parses an array of tokens
	 *
	 * @param array &$tokens A tokenise object
	 * @return void
	 */
	public function parse(tokenise $tokens) : bool {
		if (($token = $tokens->current()) !== null) {
			$this->content = $token['value'];
			return true;
		}
		return false;
	}

	/**
	 * Directly set the value
	 *
	 * @param string $value The value
	 * @return void
	 */
	public function set(string $value) : void {
		$this->content = $value;
	}

	/**
	 * Minifies the internal representation of the comment
	 *
	 * @return void
	 */
	public function minify() : void {

	}

	/**
	 * Compile as Javascript
	 *
	 * @param array $options An array indicating output options
	 * @return string The compiled Javascript
	 */
	public function compile(array $options = []) : string {
		return $this->content;
	}
}
