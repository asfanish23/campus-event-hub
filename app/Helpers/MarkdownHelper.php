<?php

namespace App\Helpers;

class MarkdownHelper
{
    public static function parse($text)
    {
        return (new \Parsedown())->text($text);
    }
}
