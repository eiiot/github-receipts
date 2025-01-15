<?php

require __DIR__ . '/vendor/autoload.php';

use Mike42\Escpos\PrintConnectors\CupsPrintConnector;
use Mike42\Escpos\Printer;

$url = 'https://public.api.bsky.app/xrpc/app.bsky.feed.getAuthorFeed?actor=did%3Aplc%3A6wpkkitfdkgthatfvspcfmjo&filter=posts_and_author_threads&includePins=false&limit=1';

// get the feed[0]['post']['record']['text'] as the text
// the timestamp is at post->indexedAt
// the username is at post->author->displayName

// compare the timestamp to the last timestamp (cached)

$feed = json_decode(file_get_contents($url), true);

$timestamp = file_get_contents('~/Developer/github-receipts/cache.txt');

if ($timestamp == $feed['feed'][0]['post']['indexedAt']) {
    // no new posts
    return 0;
}

// save the new timestamp
file_put_contents('~/Developer/github-receipts/cache.txt', $feed['feed'][0]['post']['indexedAt']);

$displayName = $feed['feed'][0]['post']['author']['displayName'];
$body = $feed['feed'][0]['post']['record']['text'];
$date = $feed['feed'][0]['post']['indexedAt']; // this is an ISO string
$date_formatted = date('Y-m-d H:i:s', strtotime($date));
$title = "$displayName (@dril) at " . $date_formatted;

echo $title . "\n";
echo $body . "\n";

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
$printer->feed(2);

$printer->text(wordwrap($body, 42));
$printer->feed(2);

$printer->cut();
$printer -> close();

// all good!
return 0;
