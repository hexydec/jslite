<?php
use hexydec\jslite\jslite;

final class jsliteTest extends \PHPUnit\Framework\TestCase {

	public function testCanParseJavascript() {
		$tests = [
			'var item = 42;',
			'if (foo) {
				var bar = "foo";
			} else {
				var bar = "bar";
			}',
			'if (test) {
				context = [document];
			}'
		];
		$obj = new jslite();
		foreach ($tests AS $item) {
			$obj->load($item);
			$this->assertEquals($item, $obj->compile());
		}

		// test downloading a file, also compare to itself
		$url = 'https://github.com/hexydec/dabby/releases/download/0.9.12/dabby.min.js';
		if (($js = $obj->open($url)) !== false) {
			$this->assertEquals($js, $obj->compile());
		}
	}

	public function testCanStripStart() {
		$tests = [
			[
				'input' => '
					  var item = 42;',
				'output' => 'var item=42'
			],
			[
				'input' => '     var item = 42;',
				'output' => 'var item=42'
			]
		];
		$this->compareMinify($tests, ['semicolon' => false]);
	}

	public function testCanStripEnd() {
		$tests = [
			[
				'input' => 'var item = 42;         		',
				'output' => 'var item=42'
			],
			[
				'input' => 'var item = 42;

				   ',
				'output' => 'var item=42'
			]
		];
		$this->compareMinify($tests, ['semicolon' => false]);
	}

	public function testCanStripMultiLineComments() {
		$tests = [
			[
				'input' => 'var item = 42; /* comment */',
				'output' => 'var item=42'
			],
			[
				'input' => 'var item  /* comment */  = 42;',
				'output' => 'var item=42'
			],
			[
				'input' => 'var it/* comment */em    = 42;',
				'output' => 'var item=42'
			],
			[
				'input' => 'var item = 42; /* multi line

				comment */',
				'output' => 'var item=42'
			],
			[
				'input' => 'var item  /* multi line

				comment */  = 42;',
				'output' => 'var item=42'
			],
			[
				'input' => 'var it/* multi line

				comment */em    = 42;',
				'output' => 'var item=42'
			],
			[
				'input' => '
					/** multi line
					 *
					 * @param string item The number 42
 				 	 */
				var item    = 42;',
				'output' => 'var item=42'
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
				'output' => 'var item=42'
			],
			[
				'input' => 'item = (item)/ 42;
					var item2 = item / 42;',
				'output' => 'item=(item)/42;var item2=item/42'
			]
		];
		$this->compareMinify($tests, ['semicolon' => false]);
	}

	public function testCanStripSingleLineComments() {
		$tests = [
			[
				'input' => 'var   item = "test  this"   ;   // this is single line comment',
				'output' => 'var item="test  this"'
			],
			[
				'input' => 'export function   ( item1  , item2  )
				{ //remove "this"
					return item1  *  item2;// this "should be removed
				}

				// remove this',
				'output' => 'export function(item1,item2){return item1*item2}'
			],
			[
				'input' => 'var item = "https://this-is-not-a-comment.com/";',
				'output' => 'var item="https://this-is-not-a-comment.com/"'
			],
		];
		$this->compareMinify($tests, ['semicolon' => false]);
	}

	public function testCanProtectQuotedStrings() {
		$tests = [
			[
				'input' => 'var   item = "test  this"   ;   ',
				'output' => 'var item="test  this"'
			],
			[
				'input' => 'var   item = "test \" this"   ;   ',
				'output' => 'var item="test \" this"'
			],
			[
				'input' => 'let item = "  the answer" + " is   42" ; ',
				'output' => 'let item="  the answer"+" is   42"'
			],
			[
				'input' => 'let item = "  the answer"
								+ " is   42" ; ',
				'output' => 'let item="  the answer"+" is   42"'
			],
			[
				'input' => 'let item = "  the answer"
								+ " is 42" ; ',
				'output' => 'let item="  the answer"+" is 42"'
			],
		];
		$this->compareMinify($tests, ['semicolon' => false]);
	}

	public function testCanProtectTemplateLiterals() {
		$tests = [
			[
				'input' => 'let item = `this
					is a template literal
				the answer is ${item2}`; ',
				'output' => 'let item=`this
					is a template literal
				the answer is ${item2}`'
			]
		];
		$this->compareMinify($tests, ['semicolon' => false]);
	}

	public function testCanProtectRegexpPatterns() {
		$tests = [
			[
				'input' => 'var regexp = /^-?\d{1,3}(?:\.\d{1,19})?,-?\d{1,3}(?:\.\d{1,19})?$/;',
				'output' => 'var regexp=/^-?\d{1,3}(?:\.\d{1,19})?,-?\d{1,3}(?:\.\d{1,19})?$/'
			],
			[
				'input' => 'var regexp = /^-?\d{1,3}(?:\.\d{1,19})?,-?\d{1,3}(?:\.\d{1,19})?$/.test();',
				'output' => 'var regexp=/^-?\d{1,3}(?:\.\d{1,19})?,-?\d{1,3}(?:\.\d{1,19})?$/.test()'
			],
			[
				'input' => 'string.replace(/[.*+\-?^${}()|[\]\\]/g, \'\\$&\');',
				'output' => 'string.replace(/[.*+\-?^${}()|[\]\\]/g,"\\$&")'
			],
			[
				'input' => 'var item = 42
					/[9-0]+/.test( item );',
				'output' => 'var item=42;/[9-0]+/.test(item)'
			],
			[
				'input' => '/[9-0]+/
					.test( item );',
				'output' => '/[9-0]+/.test(item)'
			],
			[
				'input' => 'item = 26 / 42 / 60;',
				'output' => 'item=26/42/60'
			],
			[
				'input' => 'item = (item)/ 42;
					var item2 = item / 42;',
				'output' => 'item=(item)/42;var item2=item/42'
			],
			[
				'input' => 'e.replace(/\'/g,"%27"); item = "\'";',
				'output' => 'e.replace(/\'/g,"%27");item="\'"'
			],
			[ // javascrupt regexp can contain a forward slash in a character class
				'input' => 'var re = /[+/-]*/g;',
				'output' => 'var re=/[+/-]*/g'
			],
			[
				'input' => 'var re = /[+/-]*/g,
								foo = "bar";',
				'output' => 'var re=/[+/-]*/g,foo="bar"'
			]
		];
		$this->compareMinify($tests, ['semicolon' => false]);
	}

	public function testCanStripWhitespaceAroundControlChars() {
		$tests = [
			[
				'input' => 'var item = [
					test  :  "item"    ,
					test2 : 42,
					test3   :  true
				];',
				'output' => 'var item=[test:"item",test2:42,test3:true]'
			],
			[
				'input' => 'var item = 42  >=
				43;',
				'output' => 'var item=42>=43'
			],
			[
				'input' => 'export function   ( item1  , item2  )
				{
					return item1  *  item2;
				}',
				'output' => 'export function(item1,item2){return item1*item2}'
			],
			[
				'input' => 'const item = {
					[ 1 , 2,  	3	],
					[	"hi",  " there "]
				}',
				'output' => 'const item={[1,2,3],["hi"," there "]}'
			],
			[
				'input' => 'const item = test ? "yes" : "no";',
				'output' => 'const item=test?"yes":"no"'
			]
		];
		$this->compareMinify($tests, ['booleans' => false]);
	}

	public function testCanStripWhitespaceAroundIncrements() {
		$tests = [
			[
				'input' => 'var item = 0;
							item++;',
				'output' => 'var item=0;item++'
			],
			[
				'input' => 'let item = 0;
							if (item++ + 42 === 42) {
								console.log( "yes" );
							}',
				'output' => 'let item=0;if(item+++42===42){console.log("yes")}'
			],
			[
				'input' => 'item + ++item2;',
				'output' => 'item+ ++item2'
			],
			[
				'input' => 'item - --item2;',
				'output' => 'item- --item2'
			],
			[
				'input' => 'item++ +item2;',
				'output' => 'item+++item2'
			],
			[
				'input' => 'item++ + ++item2;',
				'output' => 'item+++ ++item2' // could be +++++ but literally not worth fixing
			],
			[
				'input' => 'item-- -item2;',
				'output' => 'item---item2'
			],
			[
				'input' => 'item- ++item2;',
				'output' => 'item-++item2'
			],
			[
				'input' => 'let item = 0;
							if (true && ( --item + 42 === 41 || item-- + 42 === 41)) {
								console.log( "correct" );
							}',
				'output' => 'let item=0;if(true&&(--item+42===41||item--+42===41)){console.log("correct")}'
			]
		];
		$this->compareMinify($tests, ['booleans' => false]);
	}

	public function testCanCompressWhitespace() {
		$tests = [
			[
				'input' => 'var   item = "test  this"   ;   ',
				'output' => 'var item="test  this"'
			],
			[
				'input' => 'export function   ( item1  , item2  )
				{
					return item1  *  item2;
				}',
				'output' => 'export function(item1,item2){return item1*item2}'
			],
			[
				'input' => 'var   item = 42 > 1 ?	test1  :  test2  ;   ',
				'output' => 'var item=42>1?test1:test2'
			],
		];
		$this->compareMinify($tests, ['semicolon' => false]);
	}

	public function testCanRemoveSemicolons() {
		$tests = [
			[
				'input' => 'var item = () => {
					return "This";
				};',
				'output' => 'var item=()=>{return "This"}'
			],
			[
				'input' => 'var item = () => {
					return "I want " + (val ? "this" : "something else");
				};

				',
				'output' => 'var item=()=>{return "I want "+(val?"this":"something else")}'
			],
			[
				'input' => 'var item = " ;) ";',
				'output' => 'var item=" ;) "'
			]
		];
		$this->compareMinify($tests);
	}

	public function testCanLowercaseKeywords() {
		$tests = [
			[
				'input' => 'VAR item = TRUE;',
				'output' => 'var item=!0'
			],
			[
				'input' => 'FOR (VAR i = 0; i < TEST; i++) {
					IF (i) {
						LET foo = "bar";
					}
				}',
				'output' => 'for(var i=0;i<TEST;i++){if(i){let foo="bar"}}'
			],
			[
				'input' => 'dO {
					something++;
				} wHiLe (foo);',
				'output' => 'do{something++}while(foo)'
			],
		];
		$this->compareMinify($tests);
	}

	public function testCanConvertBooleans() {
		$tests = [
			[
				'input' => 'var item = true;',
				'output' => 'var item=!0'
			],
			[
				'input' => 'var item = false;',
				'output' => 'var item=!1'
			],
			[
				'input' => 'if ((item = func()) !== false) {
					const test = true;
				}',
				'output' => 'if((item=func())!==!1){const test=!0}'
			],
		];
		$this->compareMinify($tests);
	}

	public function testHandleDifficultJavascript() {
		$tests = [
			[
				'input' => 'var   item = "test  this"
					var item2 = 42
					',
				'output' => 'var item="test  this";var item2=42'
			],
			[
				'input' => 'var   item = "test  this"

				;
					var item2 = 42
					',
				'output' => 'var item="test  this";var item2=42'
			],
			[
				'input' => 'var item = "/*" + "*/";',
				'output' => 'var item="/*"+"*/"'
			],
			[ // keep last semi-colon on for loop
				'input' => 'for (let i = 10; i--;) {}',
				'output' => 'for(let i=10;i--;){}'
			],
			[ // keep last semi-colon in for loop
				'input' => 'for (let i = 10,a = 1; i--;) {}',
				'output' => 'for(let i=10,a=1;i--;){}'
			],
			[
				'input' => 'for (let i = 10, a = 1; i < a; i++) {}',
				'output' => 'for(let i=10,a=1;i<a;i++){}'
			],
			[ // disperate pluses
				'input' => '"hi" + +new Date();',
				'output' => '"hi"+ +new Date()'
			],
			[ // this did just return 'var A=e=>'
				'input' => 'var A = e => {
					    for (; o < r && (s = t(e / a[o]), !(s && (l += n[o], e %= a[o]))); o += 1);
					};
		            (
						isNaN(l[c]) && -1 === l[c].indexOf("px") && (
							this[c].style[a] = l[c],
							i.push(a),
							l[c] = 0
						),
						n = getComputedStyle(this[c]),
						i.forEach(e => l[c] -= parseFloat(n[e]))
					),
					this[c].style[a] = l[c] + (isNaN(l[c]) ? "" : "px");',
				'output' => 'var A=e=>{for(;o<r&&(s=t(e/a[o]),!(s&&(l+=n[o],e%=a[o])));o+=1)};(isNaN(l[c])&&-1===l[c].indexOf("px")&&(this[c].style[a]=l[c],i.push(a),l[c]=0),n=getComputedStyle(this[c]),i.forEach(e=>l[c]-=parseFloat(n[e]))),this[c].style[a]=l[c]+(isNaN(l[c])?"":"px")'
			],
			[
				'input' => 'var foo = {
					true: true,
					false: false,
					var: bar,
					let: bar,
					do: bar,
					while: bar,
					for: bar,
					if: bar,
					else: bar
				};',
				'output' => 'var foo={true:!0,false:!1,var:bar,let:bar,do:bar,while:bar,for:bar,if:bar,else:bar}'
			]
		];
		$this->compareMinify($tests);
	}

	public function testCanInsertAutomaticSemicolons() {
		$tests = [
			[
				'input' => 'var foo = 1
					var bar = 2
					var baz = 3',
				'output' => 'var foo=1;var bar=2;var baz=3'
			],
			[
				'input' => 'var foo
				 	= 1',
				'output' => 'var foo=1'
			],
			[
				'input' => 'var foo =
					1',
				'output' => 'var foo=1'
			],
			[
				'input' => 'var foo
					=
					1',
				'output' => 'var foo=1'
			],
			[
				'input' => 'var a
					a
					=
					1',
				'output' => 'var a;a=1'
			],
			[
				'input' => 'var a
					;
					a
					=
					1',
				'output' => 'var a;a=1'
			],
			[
				'input' => 'return
      				"something";',
				'output' => 'return;"something";'
			],
			[
				'input' => 'a = b
    				++c',
				'output' => 'a=b;++c'
			],
			[
				'input' => 'break
				var i = 0;',
				'output' => 'break;var i=0;'
			],
			[
				'input' => 'throw
				var i = 0;',
				'output' => 'throw;var i=0;'
			],
			[
				'input' => 'continue
				var i = 0;',
				'output' => 'continue;var i=0;'
			],
			[
				'input' => 'const bar = "bar"
					const foo
					["foo", "bar"].forEach(item => console.log(item));',
				'output' => 'const bar="bar";const foo["foo","bar"].forEach(item=>console.log(item));'
			],
			[
				'input' => 'const a = 1
					const b = 2
					const c = a + b
					(a + b).toString()',
				'output' => 'const a=1;const b=2;const c=a+b(a+b).toString()'
			],
			[
				'input' => '(() => {
						return
						{
							color: "white"
						}
					})()',
				'output' => '(()=>{return;{color:"white"};})()'
			],
			[
				'input' => '(() => {
						return {
							color: "white"
						}
					})()',
				'output' => '(()=>{return{color:"white"};})()'
			],
			[
				'input' => '1 + 1
					-1 + 1 === 0 ? alert(0) : alert(2)',
				'output' => '1+1-1+1===0?alert(0):alert(2)'
			]
		];
		$this->compareMinify($tests, ['semicolons' => false]);
	}

	protected function compareMinify(array $tests, array $minify = []) {
		$obj = new jslite();
		foreach ($tests AS $item) {
			$obj->load($item['input']);

			// make sure it compiles to the same as the input
			$compiled = $obj->compile();
			$this->assertEquals($item['input'], $compiled);

			// minify and check against the output
			$obj->minify($minify);
			$compiled = $obj->compile();
			$this->assertEquals($item['output'], $compiled);

			// recycle the output
			$obj->load($compiled);
			$obj->minify($minify);
			$this->assertEquals($item['output'], $obj->compile());
		}
	}
}
