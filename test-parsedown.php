<?php
require 'vendor/parsedown/parsedown/Parsedown.php';

$pd = new Parsedown();
$test = "# Heading\n\n**Bold** text\n\n- Item 1\n- Item 2";
echo $pd->text($test);
