<?php
require_once __DIR__ . '\vendor\autoload.php';
require_once __DIR__ . '\GetSheet.php';

class GetDocument extends GetSheet
{
    private $response;

    function getResponse($url)
    {
        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $this->googleAccountKeyFilePath);

        $client = new Google_Client();
        $client->useApplicationDefaultCredentials();
        $client->addScope($this->scope);

        $service = new Google_Service_Docs($client);
        $this->response = $service->documents->get($this->getIdFromURL($url));
        return $this->response;
    }

    function getTitle()
    {
        return $this->response->getTitle();
    }
}
