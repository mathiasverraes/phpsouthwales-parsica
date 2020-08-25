<?php declare(strict_types=1);

namespace Verraes\PHPSouthWales;

use Verraes\Parsica\Parser;
use function Verraes\Parsica\{string};

function my_first_parser() : Parser
{
    return string('https')->or(string('http'));
}