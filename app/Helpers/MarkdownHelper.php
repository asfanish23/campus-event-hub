<?php

namespace App\Helpers;

class MarkdownHelper
{
    public static function parse($text)
    {
        if (empty($text)) {
            return '';
        }

        // Ensure Parsedown is loaded
        if (!class_exists('\Parsedown')) {
            $parsedownFile = dirname(__DIR__, 2) . '/vendor/parsedown/parsedown/Parsedown.php';
            if (file_exists($parsedownFile)) {
                require_once $parsedownFile;
            }
        }

        // Parse with Parsedown
        if (class_exists('\Parsedown')) {
            $parsedown = new \Parsedown();
            return $parsedown->text($text);
        }

        // Fallback: return as-is if Parsedown not available
        return $text;
    }
}
