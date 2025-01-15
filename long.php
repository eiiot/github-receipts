<?php

require __DIR__ . '/vendor/autoload.php';

use Mike42\Escpos\PrintConnectors\CupsPrintConnector;
use Mike42\Escpos\Printer;

$dt = new DateTime("now", new DateTimeZone('America/Los_Angeles'));

$date = $dt->format('F jS, Y');

$title = "A tale of two cities";

// print out different sections of the receipt
// breaking this up into functions for clarity and readability
// $printer->setJustification(Printer::JUSTIFY_CENTER);
// $printer->setTextSize(2, 2);
// $printer->setUnderline(true);
// $printer->setEmphasis(true);
// $printer->text("Breaking News\n");
// $printer->feed(2);


$connector = new CupsPrintConnector("EPSON_TM_T20II");

$printer = new Printer($connector);

$printer->setJustification(Printer::JUSTIFY_LEFT);
$printer->setTextSize(1, 1);
$printer->setUnderline(false);
$printer->setEmphasis(false);

$printer->setEmphasis(true);
$printer->text($title);
$printer->setEmphasis(false);
$printer->feed(1);

$body = file_get_contents('./tale.txt');

$printer->text(wordwrap($body, 42));

$printer->cut();
$printer->close();
