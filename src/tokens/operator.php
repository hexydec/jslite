<?php
declare(strict_types = 1);
namespace hexydec\jslite;
use \hexydec\tokens\tokenise;

class operator {

	/**
	 * @var bool Denotes whether the class represents significant javascript
	 */
	public const significant = true;

	/**
	 * @var string The text content of this object
	 */
	public string $content = '';

	/**
	 * Parses an array of tokens
	 *
	 * @param tokenise $tokens A tokenise object
	 * @return bool Whether any tokens were parsed
	 */
	public function parse(tokenise $tokens) : bool {
		if (($token = $tokens->current()) !== null) {
			$this->content = $token['value'];
			return true;
		}
		return false;
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
	 * @return string The compiled Javascript
	 */
	public function compile() : string {
		return $this->content;
	}
}
