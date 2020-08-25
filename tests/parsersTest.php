<?php declare(strict_types=1);

namespace Tests\Verraes\PHPSouthWales;

use PHPUnit\Framework\TestCase;
use Verraes\Parsica\Parser;
use Verraes\Parsica\PHPUnit\ParserAssertions;
use function Verraes\Parsica\{alphaChar, alphaNumChar, atLeastOne, between, char, collect, float, punctuationChar, recursive, sepBy1, sequence, string};
use function Verraes\PHPSouthWales\{true, whitespace};

final class parsersTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function my_first_parser()
    {
        $parser = string("bar");

        $input = "foo";
        $expected = "foo";
        $this->assertParses($input, $parser, $expected, "Fix \$parser");
    }

    /**
     * @test
     * @depends my_first_parser
     */
    public function or()
    {
        $parser = string('http')->or(string('https'));

        $input = "https://parsica.verraes.net/";
        $expected = "https";
        $this->assertParses($input, $parser, $expected, "Hint: ¡sɹǝʇʇɐɯ ɹǝpɹO");
    }

    /**
     * @test
     * @depends or
     */
    public function sequence()
    {
        $parser = sequence(string('foo'), $SOMETHING);

        $input = "foobar";
        $expected = "bar";
        $this->assertParses($input, $parser, $expected);
    }


    /**
     * @test
     * @depends sequence
     */
    public function map()
    {
        $parser = string('foo');

        $input = "foo";
        $expected = "FOO";
        $this->assertParses($input, $parser, $expected, "Use map and strtoupper");
    }


    /**
     * @test
     * @depends map
     */
    public function collect()
    {
        $parser = collect(
            $SOMETING,
            $SOMETHING,
            $SOMETHING->map($SOMETHING)
        )->map(fn(array $output) => new Money($SOMETHING, $SOMETHING));

        $input = "EUR 5";
        $expected = new Money(5, "EUR");
        $this->assertParses($input, $parser, $expected);
    }

    /**
     * @test
     * @depends collect
     */
    public function atLeastOne()
    {
        $parser = alphaNumChar()->or(punctuationChar());

        $input = "a5b.3";
        $expected = "a5b.3";
        $this->assertParses($input, $parser, $expected);
    }

    /**
     * @test
     * @depends atLeastOne
     */
    public function between()
    {
        $float = float();
        $parser = between($LEFTPARSER, $RIGHTPARSER, $MIDDLEPARSER);

        $input = "(12.1)";
        $expected = "12.1";
        $this->assertParses($input, $parser, $expected);
    }


    /**
     * @test
     * @depends between
     */
    public function sepBy1()
    {
        $parser = sepBy1($SEPARATOR, $PARSER);

        $input = "1.5+2+3.5";
        $expected = 7.0;
        $this->assertParses($input, $parser, $expected);
    }


    /**
     * @test
     * @depends sepBy1
     */
    public function eof()
    {
        $word = atLeastOne(alphaChar());

        $input = "something!!!";
        $expected = "something";
        // This succeeds...
        $this->assertParses($input, $word, $expected);
        // ... but we want it to fail when there is additional input beyond the alpha chars.

        $parser = $SOMETHING(
            $word,
            $SOMETHING
        );

        $this->assertParseFails($input, $parser, null, "Hint: ()ɟoǝ ǝs∩");
    }

    /**
     * @test
     * @depends eof
     */
    public function recurse()
    {
        $word = atLeastOne(alphaChar());
        $parens = fn(Parser $parser): Parser => between(char('('), char(')'), $parser);

        $parser = recursive();
        $parser->recurse(
            $SOMETHING->or($SOMETHING)
        );

        $expected = "hello";

        $input = "hello";
        $this->assertParses($input, $parser, $expected);

        $input = "(hello)";
        $this->assertParses($input, $parser, $expected);

        $input = "((hello))";
        $this->assertParses($input, $parser, $expected);

        $input = "(((hello)))";
        $this->assertParses($input, $parser, $expected);
    }


    /**
     * @test
     * @depends recurse
     */
    public function true()
    {
        // We'll build a JSON parser now.
        // JSON has true, false, and null keywords, let's start with those.
        // Fix the true() parser in /src/JSON.php.

        $parser = true();

        $input = "true";
        $expected = true;
        $this->assertParses($input, $parser, $expected);
    }

    /**
     * @test
     * @depends true
     */
    public function false()
    {
        $parser = false();

        $input = "false";
        $expected = false;
        $this->assertParses($input, $parser, $expected);
    }

    /**
     * @test
     * @depends false
     */
    public function null()
    {
        $parser = null();

        $input = "null";
        $expected = null;
        $this->assertParses($input, $parser, $expected);
    }

    /**
     * @test
     * @depends null
     */
    public function whitespace()
    {
        // You deserve a break, so you get the whitespace() parser as a gift. It consumes all the whitespace.
        $parser = whitespace();

        $input = "  \n \r \t something";
        $expected = $WHAT_IS_THE_OUTPUT;
        $this->assertParses($input, $parser, $expected);
        $this->assertRemainder($input, $parser, "something");
    }

    /**
     * @test
     * @depends whitespace
     */
    public function token()
    {
        // To make dealing with whitespace easier, we introduce token().
        // You wrap token() around another parser, and you get a new parser that cleans up the
        // the whitespace that follows the parsed input.

        $someParser = string("foo");
        $parser = token($someParser);

        $input = "foo  \n \r \t bar";
        $expected = "foo";
        $this->assertParses($input, $parser, $expected);
        $expectedRemainder = "bar";
        $this->assertRemainder($input, $parser, $expectedRemainder);

    }


    /**
     * @test
     * @depends token
     */
    public function using_token()
    {
        // Use token() inside the true(), false(), and null() parsers

        $this->assertParses("true    ", true(), true);
        $this->assertParses("false \t  ", false(), false);
        $this->assertParses("null\n\n  ", null(), null);

        // Remember to use token() for all the other components of the JSON parser
    }

    /**
     * @test
     * @depends using_token
     */
    public function number()
    {
        // You can use float(), or implement this from scratch if you like

        $this->assertParses("0", number(), 0.0);
        $this->assertParses("0.15", number(), 0.15);
        $this->assertParses("0.10", number(), 0.1);
        $this->assertParses("-0.1", number(), -0.1);
        $this->assertParses("1.2345678", number(), 1.2345678);
        $this->assertParses("-1.2345678  ", number(), -1.2345678);
        $this->assertParses("-1.23456789E+123", number(), -1.23456789E+123);
        $this->assertParses("-1.23456789e-123", number(), -1.23456789E-123);
        $this->assertParses("-1E-123  ", number(), -1E-123);
        $this->assertParses("-1E-123          ", number(), -1E-123);
    }

    /**
     * @test
     * @depends number
     */
    public function stringLiteral()
    {
        // You get stringLiteral for free, but perhaps write some tests here to understand how handles escaped characters?
        $this->assertTrue(false);// just a placeholder
    }


    /**
     * @test
     * @depends      stringLiteral
     * @dataProvider arrayExamples
     */
    public function jsonArray(string $input, $expected)
    {
        // JSON arrays are lists of elements, wrapped in [], separated by comma's
        // Values are any of these:
        // object, array, stringLiteral, number, true, false, null
        // (We don't have object yet, that's next)

        $parser = jsonArray();
        $this->assertParses($input, $parser, $expected);
    }

    public function arrayExamples()
    {
        return [
            ['[]', []],
            ['[ ] ', []],
            ['[ 1 ] ', [1.0]],
            ['[ true ] ', [true]],
            ['[ 1.23, "abc", null, false ] ', [1.23, "abc", null, false]],
        ];
    }

    /**
     * @test
     * @depends jsonArray
     */
    public function member()
    {
        // A member is a key and value stringLiterals, separated by a colon.
        // Remember to use token()
        $input = '"foo": "bar"';
        $parser = member();
        $this->assertParses($input, $parser, ["foo", "bar"]);
    }

    /**
     * @test
     * @depends member
     */
    public function object()
    {
        $input = '{"foo":"bar","bar":"foo"}';
        $parser = object();
        $this->assertParses($input, $parser, (object)["foo" => "bar", "bar" => "foo"]);
    }

    public static function JSONExamples(): array
    {
        return [
            ['true'],
            ['false'],
            ['null'],
            ['"abc"'],
            ['{"a b":"c d"}'],
            [' { " a b  " : " c  d " } '],
            [' [ { " a b  " : " c  d " } ] '],
            [' [ { " a b  " : " c  d " } , { "ef" : "gh" } ] '],
            [<<<JSON
                [
                    -1.23,
                    null,
                    true,
                    [
                        [
                            {
                                "a": true
                            },
                            {
                                "b": false,
                                "c": -1.23456789E+123
                            }
                        ]
                    ]
                ]
                JSON,
            ],
            [file_get_contents(__DIR__ . '/../composer.json')],
        ];
    }

    /**
     * @test
     * @dataProvider JSONExamples
     * @depends object
     */
    public function compare_to_json_decode(string $input)
    {
        $native = json_decode($input);
        $parsica = json()->tryString($input)->output();
        $this->assertEquals($native, $parsica);
    }

    /**
     * @test
     * @depends compare_to_json_decode
     */
    public function the_end()
    {
        $ascii = <<<ASCII

Congratulations, you made it!
You win a Hypsilophodont!

                            ___......__             _
                        _.-'           ~-_       _.=a~~-_
--=====-.-.-_----------~   .--.       _   -.__.-~ ( ___===>
              '''--...__  (    \ \\\ { )       _.-~
                        =_ ~_  \\-~~~//~~~~-=-~
                         |-=-~_ \\   \\
                         |_/   =. )   ~}
                         |}      ||
                        //       ||
                      _//        {{
                   '='~'          \\_    =
                                   ~~'
ASCII;

        $this->assertTrue(false, $ascii);
    }


}

class Money
{
    private float $amount;
    private string $currency;

    function __construct(float $amount, string $currency)
    {
        $this->amount = $amount;
        $this->currency = $currency;
    }
}