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
			$this->quote = $quote = mb_substr($token['value'], 0, 1);
			if (($this->process = in_array($quote, ['"', "'"]))) {
				$this->string = str_replace('\\'.$quote, $quote, mb_substr($token['value'], 1, -1));
			} else {
				$this->string = $token['value'];
			}
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
		if ($minify['quotestyle'] && $this->quote !== $minify['quotestyle']) {
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
			$quote = $this->quote;
			return $quote.str_replace($quote, '\\'.$quote, $this->string).$quote;
		} else {
			return $this->string;
		}
	}
}
