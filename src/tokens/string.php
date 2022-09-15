<?php
declare(strict_types = 1);
namespace hexydec\jslite;
use \hexydec\tokens\tokenise;

class jsstring {

	/**
	 * @var bool Denotes whether the class represents significant javascript
	 */
	public const significant = true;

	/**
	 * @var string The captured string
	 */
	public string $content = '';

	/**
	 * @var string The quote character used to encapsulate the string
	 */
	protected string $quote = '"';

	/**
	 * @var bool Whether the string has been de-encapsulated and processed
	 */
	protected bool $process = false;

	/**
	 * Parses an array of tokens
	 *
	 * @param tokenise $tokens A tokenise object
	 * @return bool Whether any tokens were parsed
	 */
	public function parse(tokenise $tokens) : bool {
		if (($token = $tokens->current()) !== null) {
			$this->quote = $quote = \mb_substr($token['value'], 0, 1);
			if (($this->process = \in_array($quote, ['"', "'"], true))) {
				$this->content = \str_replace('\\'.$quote, $quote, \mb_substr($token['value'], 1, -1));
			} else {
				$this->content = $token['value'];
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
	 * @return string The compiled HTML
	 */
	public function compile() : string {
		if ($this->process) {
			$quote = $this->quote;
			return $quote.\str_replace($quote, '\\'.$quote, $this->content).$quote;
		} else {
			return $this->content;
		}
	}
}
