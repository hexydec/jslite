<?php
declare(strict_types = 1);
namespace hexydec\jslite;
use \hexydec\tokens\tokenise;

class jsstring {

	const type = 'string';
	const significant = true;
	protected $string;
	protected $quote = '"';
	protected $process = false;

	/**
	 * Parses an array of tokens
	 *
	 * @param array &$tokens A tokenise object
	 * @return void
	 */
	public function parse(tokenise $tokens) : bool {
		if (($token = $tokens->current()) !== null) {
			$this->quote = mb_substr($token['value'], 0, 1);
			$this->process = in_array($this->quote, ['"', "'"]);
			$this->string = $this->process ? str_replace('\\'.$this->quote, $this->quote, mb_substr($token['value'], 1, -1)) : $token['value'];
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
		if ($minify['quotestyle'] && $this->quote != $minify['quotestyle']) {
			$this->quote = $minify['quotestyle'];
		}
	}

	/**
	 * Compile as Javascript
	 *
	 * @param array $options An array indicating output options
	 * @return string The compiled HTML
	 */
	public function compile(array $options = []) : string {
		if ($this->process) {
			return $this->quote.str_replace($this->quote, '\\'.$this->quote, $this->string).$this->quote;
		} else {
			return $this->string;
		}
	}
}