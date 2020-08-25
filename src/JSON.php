<?php declare(strict_types=1);

namespace Verraes\PHPSouthWales;

use Verraes\Parsica\Parser;
use function Verraes\Parsica\{anySingleBut,
    between,
    char,
    choice,
    hexDigitChar,
    isCharCode,
    repeat,
    satisfy,
    string,
    zeroOrMore};

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

/**
 * Ignore whitespace
 */
function whitespace(): Parser
{
    return zeroOrMore(satisfy(isCharCode([0x20, 0x0A, 0x0D, 0x09])))->voidLeft(null)
        ->label('whitespace');
}

function stringLiteral(): Parser
{
    return token(
        between(
            char('"'),
            char('"'),
            zeroOrMore(
                choice(
                    string("\\\"")->map(fn($_) => '"'),
                    string("\\\\")->map(fn($_) => '\\'),
                    string("\\/")->map(fn($_) => '/'),
                    string("\\b")->map(fn($_) => mb_chr(8)),
                    string("\\f")->map(fn($_) => mb_chr(12)),
                    string("\\n")->map(fn($_) => "\n"),
                    string("\\r")->map(fn($_) => "\r"),
                    string("\\t")->map(fn($_) => "\t"),
                    string("\\u")->sequence(repeat(4, hexDigitChar()))->map(fn($o) => mb_chr(hexdec($o))),
                    anySingleBut('"')
                )
            )
        )->map(fn($o) => (string)$o) // because the empty json string returns null
    )->label("string literal");
}