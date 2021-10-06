<?php
require_once __DIR__ . '\GetSheet.php';
require_once __DIR__ . '\functions.php';

$sheet = new GetSheet();
$sheet->setGoogleAccountKeyFilePath(__DIR__ . '\linkmanager-305415-7af4ba4f61e5.json');
$sheet->setScope('https://www.googleapis.com/auth/spreadsheets');
$response_values = $sheet->getValues(
    'https://docs.google.com/spreadsheets/d/1b79oU0tz9x30i55kdOsTxT5SsO0saSwmVwMCPTBtSRk/edit?usp=sharing',
    'Размещения2'
);

$last_links_values = json_decode(file_get_contents('LAST-LINKS.json'));

file_put_contents('LAST-LINKS.json', json_encode($response_values, JSON_PRETTY_PRINT));

$new_values = table_diff($response_values, $last_links_values);
$deleted_values = table_diff($last_links_values, $response_values, true);

$request = prepare_request($new_values, $deleted_values);
var_dump($request);
