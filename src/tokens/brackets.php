<?php
declare(strict_types = 1);
namespace hexydec\jslite;
use \hexydec\tokens\tokenise;

class brackets implements command {

	/**
	 * @var bool Denotes whether the class represents significant javascript
	 */
	public const significant = true;

	/**
	 * @var expression The parent expression object
	 */
	protected expression $root;

	/**
	 * @var array<expression> An array of child expression objects
	 */
	protected array $expressions = [];

	/**
	 * @var string The type of bracket this object represents, bracket|square|curly
	 */
	public string $bracket = 'bracket'; // square or bracket or curly

	/**
	 * Constructs the comment object
	 *
	 * @param expression $root The parent expression object
	 */
	public function __construct(expression $root) {
		$this->root = $root;
	}

	/**
	 * Parses an array of tokens
	 *
	 * @param tokenise $tokens A tokenise object
	 * @return bool Whether any tokens were parsed
	 */
	public function parse(tokenise $tokens) : bool {
		if (($token = $tokens->current()) !== null) {
			$bracket = $this->bracket = \mb_substr($token['type'], 4);
			while ($tokens->next() !== null) {
				$obj = new expression($bracket);
				if ($obj->parse($tokens)) {
					$this->expressions[] = $obj;
				}
				if (($token = $tokens->current()) !== null && $token['type'] === 'close'.$bracket) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Minifies the internal representation of the document
	 *
	 * @param array<string,mixed> $minify An array indicating which minification operations to perform
	 * @return void
	 */
	public function minify(array $minify = []) : void {
		$expressions = $this->expressions;

		// minify expressions
		$last = null;
		foreach ($expressions AS $item) {
			$item->minify($minify);

			// get last expression that contains significant code
			if ($minify['semicolons']) {
				foreach ($item->commands AS $comm) {
					if ($comm::significant) {
						$last = $item;
						break;
					}
				}
			}
		}

		// remove last eol if not keyword-bracket or in for loop
		if ($last !== null && !$this->isKeywordBracket($last) && !$this->isInForLoop($expressions)) {
			$last->eol = null;
		}
	}

	/**
	 * Checks to see if the last expression is a keyword followed by brackets, with no other commands - semi-colon must not be removed
	 * 
	 * @param expression $last The last JSlite object that is being checked for semi-colon removal
	 * @return bool Whether the object contains a keyword-bracket expression
	 */
	protected function isKeywordBracket(expression $last) : bool {
		$key = __NAMESPACE__.'\\keyword';
		$bra = __NAMESPACE__.'\\brackets';

		// don't remove semi-colon from keyword + brackets with no following commands
		if ($this->bracket === 'curly') {
			$sigcomms = [];
			foreach ($last->commands AS $comm) {
				if ($comm::significant) {
					$sigcomms[] = $comm;
				}
			}
			if (\count($sigcomms) === 2 && \get_class($sigcomms[0]) === $key && $sigcomms[0]->content !== 'return' && \get_class($sigcomms[1]) === $bra && $sigcomms[1]->bracket === 'bracket') {
				return true;
			}
		}
		return false;
	}

	/**
	 * Analyses the cirrent expression set to see if it is contained within a for loop with a specific pattern of semi-colons, where the final one should not be removed
	 * 
	 * @param array<expression> $expressions An array containing the current expressesion set
	 * @return bool WHether the current expresiion set is wrapped in a for loop
	 */
	protected function isInForLoop(array $expressions) : bool {
		$key = __NAMESPACE__.'\\keyword';

		// must not remove eol if for loop
		$prev = null;
		foreach ($this->root->commands AS $item) {
			if ($item === $this) {
				if (\is_object($prev) && \get_class($prev) === $key && $prev->content === 'for') {

					// count expressions where the EOL is ; (Could be comma)
					$count = 0;
					foreach ($expressions AS $expr) {
						if ($expr->eol === ';') {
							$count++;
						}
					}
					if ($count !== 3) {
						return true;
					}
				}
				break;
			} elseif ($item::significant) {
				$prev = $item;
			}
		}
		return false;
	}

	/**
	 * Compile as Javascript
	 *
	 * @return string The compiled Javascript
	 */
	public function compile() : string {

		// compile child expressions
		$js = '';
		foreach ($this->expressions AS $item) {
			$js .= $item->compile();
		}

		// wrap in brackets
		$brackets = [
			'square' => ['[', ']'],
			'bracket' => ['(', ')'],
			'curly' => ['{', '}'],
		];
		$bracket = $brackets[$this->bracket];
		return $bracket[0].$js.$bracket[1];
	}
}
