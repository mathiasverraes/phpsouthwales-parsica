<?php declare(strict_types=1);

namespace Tests\Verraes\PHPSouthWales;

use PHPUnit\Framework\TestCase;
use Verraes\Parsica\PHPUnit\ParserAssertions;
use function Verraes\PHPSouthWales\my_first_parser;

final class parsersTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function my_first_parser()
    {
        $parser = my_first_parser();

        $input = "https://parsica.verraes.net/";
        $expected = "https";
        $this->assertParses($input, $parser, $expected);

        $expectedRemaining = "://parsica.verraes.net/";
        $this->assertRemainder($input, $parser, $expectedRemaining);

    }

}
