<?php declare(strict_types=1);

namespace Tests\Verraes\PHPSouthWales;

use PHPUnit\Framework\TestCase;
use Verraes\Parsica\PHPUnit\ParserAssertions;
use function Verraes\Parsica\alphaNumChar;
use function Verraes\Parsica\atLeastOne;
use function Verraes\Parsica\char;
use function Verraes\Parsica\collect;
use function Verraes\Parsica\digitChar;
use function Verraes\Parsica\sequence;
use function Verraes\Parsica\skipSpace;

final class PHPSouthWalesTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function demo()
    {
        $head = fn($s) => substr($s, 0, 1);
        $tail = fn($s) => substr($s, 1);

        $char = fn($char) =>
                    fn($input)
                        => $head($input) == $char
                        ? ["output" => $head($input), "remainder" => $tail($input)]
                        : false;
        $parser = $char('x');
        $input = "xabc";
        $output = "x";
        $remainder = "abc";
        $result = $parser($input);
        $this->assertEquals(["output" => $output, "remainder" =>$remainder], $result);
/*
        $or = fn($p1, $p2) => fn($input) => $p1($input) ?: $p2($input) ?: false;
        $parser = $or($char('a'), $char('b'));
        $input = "a1";
        $expected = "b";
        $output = $parser($input);
        $this->assertEquals($expected, $output);
*/

        $sequence = fn($p1, $p2) => function ($input) use ($p2, $p1) {
            $r1 = $p1($input);
            if($r1) {
                $r2 = $p2($r1["remainder"]);
                return $r2;
            }
            return false;
        };

        $input = "abc";
        $parser = $sequence($char('a'), $char('b'));
        $result = $parser($input);
        $this->assertEquals(["output" => "b", "remainder" => "c"], $result);

    }

    /** @test */
    public function Name()
    {
        $parser = atLeastOne(alphaNumChar())->followedBy(skipSpace())->followedBy(atLeastOne(alphaNumChar()));



        $result = $parser->tryString("abc123 xyz");
        $this->assertEquals("xyz", $result->output());
        $this->assertEquals("", $result->remainder());


    }

    /** @test */
    public function map()
    {
        $number = atLeastOne(digitChar())->map(fn($output) => (int)$output);
        $twoNumbers = collect(
            $number,
            skipSpace(),
            $number,
        )->map(fn($o) => [$o[0], $o[2]]);
        $result = $twoNumbers->tryString("123 456");
        $x = 1;

        between(char('['), char(']'), sepBy(char('|'), $number));
    }


}
