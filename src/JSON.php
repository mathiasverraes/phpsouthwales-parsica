<?php declare(strict_types=1);

namespace Verraes\PHPSouthWales;

use Verraes\Parsica\Parser;
use function Verraes\Parsica\{isCharCode, satisfy, string, zeroOrMore};

/**
 * Ignore whitespace
 */
function whitespace(): Parser
{
    return zeroOrMore(satisfy(isCharCode([0x20, 0x0A, 0x0D, 0x09])))->voidLeft(null)
        ->label('whitespace');
}

function true() : Parser
{
    /*
    return
        $STRING_PARSER_HERE
        ->map($A_CALLABLE)
        ->label("true")
    ;
    */
}

