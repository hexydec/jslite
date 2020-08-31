<?php
use hexydec\jslite\jslite;

final class jsliteTest extends \PHPUnit\Framework\TestCase {

	public function testCanStripStart() {
		$tests = [
			[
				'input' => '
					  var item = 42;',
				'output' => 'var item=42;'
			],
			[
				'input' => '     var item = 42;',
				'output' => 'var item=42;'
			]
		];
		$this->compareMinify($tests);
	}

	public function testCanStripEnd() {
		$tests = [
			[
				'input' => 'var item = 42;         		',
				'output' => 'var item=42;'
			],
			[
				'input' => 'var item = 42;

				   ',
				'output' => 'var item=42;'
			]
		];
		$this->compareMinify($tests);
	}

	public function testCanStripMultiLineComments() {
		$tests = [
			[
				'input' => 'var item = 42; /* comment */',
				'output' => 'var item=42;'
			],
			[
				'input' => 'var item  /* comment */  = 42;',
				'output' => 'var item=42;'
			],
			[
				'input' => 'var it/* comment */em    = 42;',
				'output' => 'var item=42;'
			],
			[
				'input' => 'var item = 42; /* multi line

				comment */',
				'output' => 'var item=42;'
			],
			[
				'input' => 'var item  /* multi line

				comment */  = 42;',
				'output' => 'var item=42;'
			],
			[
				'input' => 'var it/* multi line

				comment */em    = 42;',
				'output' => 'var item=42;'
			],
			[
				'input' => '
					/** multi line
					 *
					 * @param string item The number 42
 				 	 */
				var item    = 42;',
				'output' => 'var item=42;'
			],
			[
				'input' => '
					/** multi line
					 *
					 * @param string item The number 42
 				 	 */
				var item  /** multi line
				 *
				 * @param string item The number 42
				 */  = 42;',
				'output' => 'var item=42;'
			],
			[
				'input' => 'item = (item)/ 42;
					var item2 = item / 42;',
				'output' => 'item=(item)/42;var item2=item/42;'
			]
		];
		$this->compareMinify($tests);
	}

	public function testCanStripSingleLineComments() {
		$tests = [
			[
				'input' => 'var   item = "test  this"   ;   // this is single line comment',
				'output' => 'var item="test  this";'
			],
			[
				'input' => 'export function   ( item1  , item2  )
				{ //remove "this"
					return item1  *  item2;// this "should be removed
				}

				// remove this',
				'output' => 'export function(item1,item2){return item1*item2;}'
			]
		];
		$this->compareMinify($tests);
	}

	public function testCanProtectQuotedStrings() {
		$tests = [
			[
				'input' => 'var   item = "test  this"   ;   ',
				'output' => 'var item="test  this";'
			],
			[
				'input' => 'var   item = "test \" this"   ;   ',
				'output' => 'var item="test \" this";'
			],
			[
				'input' => 'let item = "  the answer" + " is   42" ; ',
				'output' => 'let item="  the answer"+" is   42";'
			],
			[
				'input' => 'let item = "  the answer"
								+ " is   42" ; ',
				'output' => 'let item="  the answer"+" is   42";'
			],
			[
				'input' => 'let item = "  the answer"
								+ " is
								42" ; ',
				'output' => 'let item="  the answer"+" is
								42";'
			]
		];
		$this->compareMinify($tests);
	}

	public function testCanProtectRegexpPatterns() {
		$tests = [
			[
				'input' => 'var regexp = /^-?\d{1,3}(?:\.\d{1,19})?,-?\d{1,3}(?:\.\d{1,19})?$/;',
				'output' => 'var regexp=/^-?\d{1,3}(?:\.\d{1,19})?,-?\d{1,3}(?:\.\d{1,19})?$/;'
			],
			[
				'input' => 'var regexp = /^-?\d{1,3}(?:\.\d{1,19})?,-?\d{1,3}(?:\.\d{1,19})?$/.test();',
				'output' => 'var regexp=/^-?\d{1,3}(?:\.\d{1,19})?,-?\d{1,3}(?:\.\d{1,19})?$/.test();'
			],
			[
				'input' => 'string.replace(/[.*+\-?^${}()|[\]\\]/g, \'\\$&\');',
				'output' => 'string.replace(/[.*+\-?^${}()|[\]\\]/g,\'\\$&\');'
			],
			[
				'input' => 'item = 26 / 42 / 60;',
				'output' => 'item=26/42/60;'
			],
			[
				'input' => 'item = (item)/ 42;
					var item2 = item / 42;',
				'output' => 'item=(item)/42;var item2=item/42;'
			]
		];
		$this->compareMinify($tests);
	}

	public function testCanStripWhitespaceAroundControlChars() {
		$tests = [
			[
				'input' => 'var item = [
					test  :  "item"    ,
					test2 : 42,
					test3   :  true
				];',
				'output' => 'var item=[test:"item",test2:42,test3:true];'
			],
			[
				'input' => 'var item = 42  >=
				43;',
				'output' => 'var item=42>=43;'
			],
			[
				'input' => 'export function   ( item1  , item2  )
				{
					return item1  *  item2;
				}',
				'output' => 'export function(item1,item2){return item1*item2;}'
			]
		];
		$this->compareMinify($tests);
	}

	public function testCanStripWhitespaceAroundIncrements() {
		$tests = [
			[
				'input' => 'var item = 0;
							item++;',
				'output' => 'var item=0;item++;'
			],
			[
				'input' => 'let item = 0;
							if (item++ + 42 === 42) {
								console.log( "yes" );
							}',
				'output' => 'let item=0;if(item++ +42===42){console.log("yes");}'
			],
			[
				'input' => 'let item = 0;
							if (true && ( --item + 42 === 41 || item-- + 42 === 41)) {
								console.log( "correct" );
							}',
				'output' => 'let item=0;if(true&&(--item+42===41||item-- +42===41)){console.log("correct");}'
			]
		];
		$this->compareMinify($tests);
	}

	public function testCanCompressWhitespace() {
		$tests = [
			[
				'input' => 'var   item = "test  this"   ;   ',
				'output' => 'var item="test  this";'
			],
			[
				'input' => 'export function   ( item1  , item2  )
				{
					return item1  *  item2;
				}',
				'output' => 'export function(item1,item2){return item1*item2;}'
			]
		];
		$this->compareMinify($tests);
	}

	public function testHandleDifficultJavascript() {
		$tests = [
			[
				'input' => 'var   item = "test  this"
					var item2 = 42
					',
				'output' => 'var item="test  this"'."\n".'var item2=42'
			]
		];
		$this->compareMinify($tests);
	}

	protected function compareMinify(array $tests) {
		$obj = new jslite();
		foreach ($tests AS $item) {
			$obj->load($item['input']);
			$obj->minify();
			$this->assertEquals($item['output'], $obj->save());
		}
	}
}
