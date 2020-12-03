<?php declare(strict_types=1);

namespace Tests\Verraes\PHPSouthWales;

use PHPUnit\Framework\TestCase;
use Verraes\Parsica\PHPUnit\ParserAssertions;
use function Verraes\Parsica\char;

final class ScratchTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function demo()
    {

        $a = fn($input) => $input == "a" ? "a" : false;
        $this->assertEquals("a", $a("a"));

        $b = fn($input) => $input == "b" ? "b" : false;
        $or = fn($p1, $p2) => fn($input) => $p1($input) ?: $p2($input) ?: false;

        $parser = $or($a, $b);
        $this->assertEquals("a", $parser("a"));

        $head = fn($s) => substr($s, 0, 1);
        $tail = fn($s) => substr($s, 1);
        $a = fn($input) => $head($input) == "a" ? [$head($input), $tail($input)] : false;
        $b = fn($input) => $head($input) == "b" ? [$head($input), $tail($input)] : false;

        $this->assertEquals(["a", "xyz"], $a("axyz"));


        $sequence = fn($p1, $p2) => function ($input) use ($p1, $p2) {
            $r1 = $p1($input);
            if ($r1) {
                $r2 = $p2($r1[1]);
                if ($r2) return $r2;
            }
            return false;
        };

        $parser = $sequence($a, $b);
        $this->assertEquals(["b", ""], $parser("ab"));


        $parser = char('a');

        $input = "a";
        $result = $parser->tryString($input);
        $output = $result->output();

        $expected = "a";
        $this->assertEquals($expected, $output);

    }


}
