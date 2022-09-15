<?php
declare(strict_types = 1);
namespace hexydec\jslite;
use \hexydec\tokens\tokenise;

class whitespace {

	/**
	 * @var bool Denotes whether the class represents significant javascript
	 */
	public const significant = false;

	/**
	 * @var expression The parent expression object
	 */
	protected expression $parent;

	/**
	 * @var string The captured whitespace
	 */
	protected string $content;

	/**
	 * Constructs the comment object
	 *
	 * @param expression $parent The parent expression object
	 */
	public function __construct(expression $parent) {
		$this->parent = $parent;
	}

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
		if ($minify['whitespace']) {
			$commands = $this->parent->commands;
			$eol = $this->parent->eol;
			$prev = null;
			$beforeprev = null;
			$count = \count($commands);
			$not = ['whitespace', 'comment'];

			// specify class names
			$op = __NAMESPACE__.'\\operator';
			$inc = __NAMESPACE__.'\\increment';
			$key = __NAMESPACE__.'\\keyword';
			$bra = __NAMESPACE__.'\\brackets';

			// loop through commands
			$json = false;
			$assignment = false;
			$return = false;
			foreach ($commands AS $i => $item) {
				if ($item === $this) {

					// first item
					if (!$i || !$prev) {
						$this->content = '';

					} else {

						// get the next command that is not
						$prevtype = \get_class($prev);
						$next = null;
						$nexttype = null;
						for ($n = $i + 1; $n < $count; $n++) {
							if ($commands[$n]::significant) {
								$next = $commands[$n];
								$nexttype = \get_class($next);
								break;
							}
						}

						// remove whitespace if last in the parent expression
						if (!$next) {

							// terminate any statements that are not terminated
							if (!$eol && \mb_strpos($this->content, "\n") !== false) {

								// always terminate return
								if ($return) {
									$this->parent->eol = ';';

								// only terminate if not object
								} elseif (!\in_array($this->parent->bracket, ['square', 'bracket'], true) && ($this->parent->bracket !== 'curly' || !$json)) {
									$this->parent->eol = ';';
								}
							}
							$this->content = '';

						// handle operators next to an increment
						} elseif ($prevtype === $op && $nexttype === $inc && \mb_strpos($next->compile(), $prev->compile()) !== false) {
							$this->content = ' ';

						// handled + + and - -
						} elseif ($prevtype === $op && $nexttype === $op && $prev->content === $next->content) {
							$this->content = ' ';

						// keyword not followed by bracket
						} elseif (\in_array($key, [$prevtype, $nexttype], true) && !\in_array($bra, [$prevtype, $nexttype], true) && !\in_array($op, [$prevtype, $nexttype], true)) {
							$this->content = ' ';

						// sqaure bracket next to keyword, leave a space
						} elseif ($prevtype === $bra && $nexttype === $key && $prev->bracket === 'square') {
							$this->content = ' ';

						// special case for when return true is converted to return !0, causes the whitespace to flip
						} elseif ($prevtype === $key && \mb_strpos($next->compile(), '!') === 0) {
							$this->content = ' ';

						// remove whitespace
						} else {
							$this->content = '';
						}
					}
					break;

				// record the previous significant objects
				} elseif ($item::significant) {
					$beforeprev = $prev;
					$prev = $item;

					// return statement
					if (($item->content ?? null) === 'return') {
						$return = true;

					// track assignemt types
					} elseif (($item->content ?? null) === '?') {
						$assignment = true;

					// not allowed if already assigned - ternary
					} elseif (!$assignment && ($item->content ?? null) === ':') {
						$json = true;
					}
				}
			}
		}
	}

	/**
	 * Compile as Javascript
	 *
	 * @param array $options An array indicating output options
	 * @return string The compiled HTML
	 */
	public function compile(array $options = []) : string {
		return $this->content;
	}
}
