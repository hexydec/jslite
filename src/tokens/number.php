<?php
declare(strict_types = 1);
namespace hexydec\jslite;
use \hexydec\tokens\tokenise;

class number {

	public const significant = true;
	protected $content;

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
	 * Minifies the internal representation of the document
	 *
	 * @return void
	 */
	public function minify() : void {

	}

	/**
	 * Compile as Javascript
	 *
	 * @param array $options An array indicating output options
	 * @return string The compiled HTML
	 */
	public function compile(array $options = []) : string {
		return (string) $this->content;
	}
}
