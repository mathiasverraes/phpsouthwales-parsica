<?php declare(strict_types=1);

namespace Tests\Verraes\PHPSouthWales;

use PHPUnit\Framework\TestCase;
use Verraes\Parsica\Parser;
use Verraes\Parsica\PHPUnit\ParserAssertions;
use function Verraes\Parsica\{alphaChar,
    alphaNumChar,
    atLeastOne,
    between,
    char,
    float,
    punctuationChar,
    recursive,
    sepBy1,
    sequence,
    string
};
use function Verraes\PHPSouthWales\{whitespace};

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
        $parser = $SOMETHING;

        $input = "EUR 5";
        $expected = new Money(5, "EUR");
        $this->assertParses($input, $parser, $expected, "Hint: ()dɐɯ puɐ '()ʇᴉƃᴉp '()ʇɔǝlloɔ ǝsn");
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
        $parser = sepBy1($SEPARATOR, $PARSER)->map($SOMETHING);

        $input = "1.5+2+3.5";
        $expected = 7.0;
        $this->assertParses($input, $parser, $expected);
    }


    /**
     * @test
     * @depends between
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
     * @depends
     */
    public function true()
    {
        // We'll build a JSON parser. First, well need

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
        // You get the ws() parser for free. It gets rid of whitespace for you.
        $parser = ws();

        $input = "  \n \r \t something";
        $expected = null;
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


}

class Money
{
    private int $amount;
    private string $currency;

    function __construct(int $amount, string $currency)
    {
        $this->amount = $amount;
        $this->currency = $currency;
    }
}