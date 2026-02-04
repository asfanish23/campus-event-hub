<?php

namespace App\Helpers;

class MarkdownHelper
{
    public static function parse($text)
    {
        if (!class_exists('Parsedown')) {
            require_once base_path('vendor/parsedown/parsedown/Parsedown.php');
        }
        return (new \Parsedown())->text($text);
    }
}
