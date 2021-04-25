<?php
declare(strict_types = 1);
namespace hexydec\jslite;
use \hexydec\tokens\tokenise;

class comment {

	const type = 'comment';
	const significant = false;
	/**
	 * @var string The text content of this object
	 */
	protected $content = null;
	protected $multi = false;

	/**
	 * Parses an array of tokens
	 *
	 * @param array &$tokens A tokenise object
	 * @return void
	 */
	public function parse(tokenise $tokens) : bool {
		if (($token = $tokens->current()) !== null) {
			$this->multi = $token['type'] === 'commentmulti';
			$this->content = mb_substr($token['value'], 2, $this->multi ? -2 : null);
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
	 * @param array $options An array indicating output options
	 * @return string The compiled Javascript
	 */
	public function compile(array $options = []) : string {
		if ($this->content === null) {
			return '';
		} elseif ($this->multi) {
			return '/*'.$this->content.'*/';
		} else {
			return '//'.$this->content;
		}
	}
}
