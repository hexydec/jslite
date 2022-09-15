<?php
declare(strict_types = 1);
namespace hexydec\jslite;
use \hexydec\tokens\tokenise;

class keyword {

	/**
	 * @var bool Denotes whether the class represents significant javascript
	 */
	public const significant = true;

	/**
	 * @var string The captured keyword
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
	 * Minifies the internal representation of the document
	 *
	 * @param array $minify An array indicating which minification operations to perform, this is merged with htmldoc::$config['minify']
	 * @return void
	 */
	public function minify(array $minify = []) : void {
		switch ($this->content) {
			case 'true':
				if ($minify['booleans']) {
					$this->content = '!0';
				}
				break;
			case 'false':
				if ($minify['booleans']) {
					$this->content = '!1';
				}
				break;
			// case 'undefined':
			// 	if ($minify['undefined']) {
			// 		$this->content = 'void 0';
			// 	}
			// 	break;
		}
	}

	/**
	 * Compile as Javascript
	 *
	 * @return string The compiled HTML
	 */
	public function compile() : string {
		return $this->content;
	}
}
