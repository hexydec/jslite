<?php
declare(strict_types = 1);
namespace hexydec\jslite;

class comment {

	/**
	 * @var string The text content of this object
	 */
	protected $content = null;

	/**
	 * Parses an array of tokens
	 *
	 * @param array &$tokens A tokenise object
	 * @return void
	 */
	public function parse(tokenise $tokens) : bool {
		if (($token = $tokens->current()) !== null) {
			$this->content = mb_substr($token['value'], 2, $token['type'] == 'commentmulti' ? -2 : 0);
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
		if (!empty($minify['comments']['remove'])) {
			$this->content = null;
		}
	}

	/**
	 * Compile as Javascript
	 *
	 * @param array $options An array indicating output options
	 * @return string The compiled Javascript
	 */
	public function output(array $options = []) : string {
		if ($this->content === null) {
			return '';
		} elseif (mb_strpos($this->content, "\n") !== false) {
			return '/*'.$this->content.'*/';
		} else {
			return '//'.$this->content;
		}
	}
}
