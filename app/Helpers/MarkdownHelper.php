<?php

namespace App\Helpers;

class MarkdownHelper
{
    public static function parse($text)
    {
        $parsedownPath = dirname(__DIR__, 2) . '/vendor/parsedown/parsedown/Parsedown.php';
        
        if (file_exists($parsedownPath) && !class_exists('Parsedown')) {
            require_once $parsedownPath;
        }
        
        if (class_exists('Parsedown')) {
            return (new \Parsedown())->text($text);
        }
        
        // Fallback: just escape the text
        return htmlspecialchars($text);
    }
}
