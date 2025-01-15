<?php

require __DIR__ . '/vendor/autoload.php';

use Mike42\Escpos\PrintConnectors\CupsPrintConnector;
use Mike42\Escpos\Printer;

$dt = new DateTime("now", new DateTimeZone('America/Los_Angeles'));

$date = $dt->format('F jS, Y');

$title = "Good Morning! It's " . $date . "\n";

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

$lat = 37.4241;
$long = -122.1661;
// Fetch the weather for Stanford, CA from WeatherKit
$weatherUrl = 'https://api.weather.gov/gridpoints/MTR/90,88/forecast';

$opts = [
    'http' => [
        'method' => 'GET',
        'header' => 'User-Agent: Mozilla/5.0 (compatible; MyApp/1.0)'
    ]
];

$context = stream_context_create($opts);
$weather = json_decode(file_get_contents($weatherUrl, false, $context), true);

$weatherToday = $weather['properties']['periods'][0];

$weatherSummary = $weatherToday['detailedForecast'];

$body = "Today's weather for Stanford, CA:\n" . $weatherSummary . "\n\n";

$printer->text(wordwrap($body, 42));

$schedules = [
  'monday' => [
    'CS 107 at 10:30AM',
    'COLLEGE102 at 3:00PM'
  ],
  'tuesday' => [
    'ARHIST 142 at 10:30AM',
  ],
  'wednesday' => [
    'FRENLANG 30 at 9:30AM',
    'COLLEGE 102 at 3:00PM',
    'Carta Work Session at 9:00 PM'
  ],
  'thursday' => [
    'ARHIST 142 at 10:45AM',
    'CS 107 LAB at 1:30PM'
  ],
  'friday' => [
    'CS 107 at 10:30AM',
  ]
];

$dayOfWeek = strtolower($dt->format('l'));

$schedule = "You have " . count($schedules[$dayOfWeek]) . " class".
    (count($schedules[$dayOfWeek]) != 1 ? "es" : "") . " today:\n";
foreach ($schedules[$dayOfWeek] as $class) {
    $schedule .= $class . "\n";
}

$printer->text(wordwrap($schedule, 42));
$printer->feed(1);

$date_for_nyt = $dt->format('Y-m-d');

$jsonData = file_get_contents("https://www.nytimes.com/svc/connections/v2/$date_for_nyt.json");

// Assuming you've already fetched and decoded the JSON data
$data = json_decode($jsonData, true);

// Collect all words into a single array
$words = [];
foreach ($data['categories'] as $category) {
    foreach ($category['cards'] as $card) {
        $words[] = $card['content'];
    }
}

// Shuffle the words
shuffle($words);

// Function to create a centered string with padding
function centerText($text, $width)
{
    $padding = max(0, ($width - strlen($text)) / 2);
    return str_repeat(' ', floor($padding)) . $text . str_repeat(' ', ceil($padding));
}

// Create the grid output
$gridOutput = "The Connections (via NYT Games):\n";
$cellWidth = 10; // Adjust based on your printer's width and font size

// Print 4x4 grid
for ($row = 0; $row < 4; $row++) {
    // Top border of cells
    $gridOutput .= str_repeat('-', ($cellWidth + 1) * 4 + 1) . "\n";

    // Cell contents
    $rowOutput = "|";
    for ($col = 0; $col < 4; $col++) {
        $index = $row * 4 + $col;
        $word = $words[$index];
        $rowOutput .= centerText($word, $cellWidth) . "|";
    }
    $gridOutput .= $rowOutput . "\n";
}
// Bottom border
$gridOutput .= str_repeat('-', ($cellWidth + 1) * 4 + 1) . "\n\n";

// Print to receipt printer
$printer->text($gridOutput);

$wordOfTheDayUrl = 'https://wordsmith.org/awad/rss1.xml';

$wordOfTheDay = simplexml_load_file($wordOfTheDayUrl);

$word = $wordOfTheDay->channel->item[0]->title;

$definition = $wordOfTheDay->channel->item[0]->description;

$wordOfTheDayOutput = "Word of the Day:\n" . $word . "\n" . $definition . "\n\n";

$printer->text($wordOfTheDayOutput);
$printer->feed(2);

$printer->cut();
$printer->close();

return 0;
