<?php
declare(strict_types = 1);
namespace hexydec\jslite;
use \hexydec\tokens\tokenise;

class comment {

	/**
	 * @var bool Denotes whether the class represents significant javascript
	 */
	public const significant = false;

	/**
	 * @var string The text content of this object
	 */
	protected ?string $content = null;

	/**
	 * @var bool Denotes whether the comment object represents a single or multi-line comment
	 */
	protected bool $multi = false;

	/**
	 * Parses an array of tokens
	 *
	 * @param tokenise $tokens A tokenise object
	 * @return bool Whether there were any tokens to parse
	 */
	public function parse(tokenise $tokens) : bool {
		if (($token = $tokens->current()) !== null) {
			$this->multi = $token['type'] === 'commentmulti';
			$this->content = \mb_substr($token['value'], 2, $this->multi ? -2 : null);
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
		if ($minify['comments']) {
			$this->content = null;
		}
	}

	/**
	 * Compile as Javascript
	 *
	 * @return string The compiled Javascript
	 */
	public function compile() : string {
		if ($this->content === null) {
			return '';
		} elseif ($this->multi) {
			return '/*'.$this->content.'*/';
		} else {
			return '//'.$this->content;
		}
	}
}
