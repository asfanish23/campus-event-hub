<?php

namespace App\Helpers;

use Parsedown;

class MarkdownHelper
{
    public static function parse($text)
    {
        return (new Parsedown())->text($text);
    }
}
