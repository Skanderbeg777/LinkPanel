<?php
require_once __DIR__ . '\GetSheet.php';

define('OPERATION', 0);
define('DOCUMENT', 1);
define('POST', 2);
define('DATE', 3);
define('ANCHOR_1', 4);
define('URL_1', 5);
define('ANCHOR_2', 6);
define('URL_2', 7);
define('POST_CONTENT', 8);
define('POST_TITLE', 9);
define('ROW', 10);
define('STATUS', 11);

$_POST['request'] = array(
        'POST',
        '-',
        'https://beverlyhillsacupuncturedoctors.com/2021/04/testovyy-dok/',
        '27-03-2021',
        'анкор1',
        'wikipedia.org',
        'first',
        'google.com',
        '-',
    '-',
    '4',
    'Done'
);
if ( !isset($_POST['request']) || empty($_POST['request']) ) {
    exit();
} else {
    put_response($_POST['request']);
}

function put_response($request)
{
    $sheet = new GetSheet();
    $sheet->setGoogleAccountKeyFilePath(__DIR__ . '\linkmanager-305415-7af4ba4f61e5.json');
    $sheet->setScope('https://www.googleapis.com/auth/spreadsheets');
    $service = $sheet->getNewService();
    $opt = [ 'valueInputOption' => 'RAW' ];

    $values = [];
    if (isset($request[POST]) && isset($request[ROW])) {
        $values['values'] = [ [$request[POST]] ];
        $range = 'Лист3!C';
        $range .= $request[ROW];
        $b = new Google_Service_Sheets_ValueRange($values);
        $service->spreadsheets_values->update('1b79oU0tz9x30i55kdOsTxT5SsO0saSwmVwMCPTBtSRk', $range, $b, $opt);
    }
    if (isset($request[STATUS]) && isset($request[ROW])) {
        $values['values'] = [ [$request[STATUS]] ];
        $range = 'Лист3!J';
        $range .= $request[ROW];
        $b = new Google_Service_Sheets_ValueRange($values);
        $service->spreadsheets_values->update('1b79oU0tz9x30i55kdOsTxT5SsO0saSwmVwMCPTBtSRk', $range, $b, $opt);
    }
}
