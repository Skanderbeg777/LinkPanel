<?php
require_once __DIR__ . '\vendor\autoload.php';

/*$googleAccountKeyFilePath = __DIR__ . '\linkmanager-305415-7af4ba4f61e5.json';
putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $googleAccountKeyFilePath);

$client = new Google_Client();
$client->useApplicationDefaultCredentials();

$client->addScope('https://www.googleapis.com/auth/spreadsheets');

$service = new Google_Service_Sheets($client);
$spreadsheetId = '1b79oU0tz9x30i55kdOsTxT5SsO0saSwmVwMCPTBtSRk';

//$response = $service->spreadsheets->get($spreadsheetId);
$response = $service->spreadsheets_values->get($spreadsheetId, 'Размещения2');*/

class GetSheet
{
    public $service;
    protected $googleAccountKeyFilePath;
    protected $scope;

    function getValues($url, $range)
    {
        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $this->googleAccountKeyFilePath);

        $client = new Google_Client();
        $client->useApplicationDefaultCredentials();
        $client->addScope($this->scope);

        $service = new Google_Service_Sheets($client);
        $this->service = $service;
        $response = $service->spreadsheets_values->get($this->getIdFromURL($url), $range);
        return $response->getValues();
    }

    function getNewService()
    {
        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $this->googleAccountKeyFilePath);

        $client = new Google_Client();
        $client->useApplicationDefaultCredentials();
        $client->addScope($this->scope);

        $service = new Google_Service_Sheets($client);
        return $service;
    }

    function setGoogleAccountKeyFilePath($path)
    {
        $this->googleAccountKeyFilePath = $path;
    }

    function setScope($scope)
    {
        $this->scope = $scope;
    }

    protected function getIdFromURL($url)
    {
        $arr = explode('/', $url);
        return $arr[5];
    }

}
