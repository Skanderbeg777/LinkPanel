<?php
require_once __DIR__ . '\GetDocument.php';
require_once __DIR__ . '\DocToHTMLConverter.php';

define('OPERATION', 0);
define('DOCUMENT', 1);
define('POST', 2);
define('DATE', 3);
define('ANCHOR_1', 4);
define('URL_1', 5);
define('ANCHOR_2', 6);
define('URL_2', 7);
define('POST_CONTENT', 8);
define('ACTION', 8);
define('POST_TITLE', 9);
define('ROW', 10);

function compare_post_values($a, $b)
{
    //$length = count($a);
    for ($i = 0; $i < ACTION+1; $i++) {
        if ($i === POST) continue;
        if (strpos($a[$i], $b[$i]) !== 0) return 1;
    }
    return 0;
}

function table_diff($resp, $links, $delete_flag = false)
{
    $length_resp = count($resp);
    $length_links = count($links);
    $diff = [];
    for ($i = 0; $i < $length_resp; $i++) {
        $unique_flag = true;
        for ($j = 0; $j < $length_links; $j++) {
            if (!compare_post_values($resp[$i], $links[$j])) $unique_flag = false;
        }
        $resp[$i][ROW] = $i+1;

        if ($unique_flag) $diff[] = $resp[$i];
    }
    return $diff;
}

function prepare_request($new_values, $del_values)
{
    $doc = new GetDocument();
    $doc->setGoogleAccountKeyFilePath(__DIR__ . '\linkmanager-305415-7af4ba4f61e5.json');
    $doc->setScope('https://www.googleapis.com/auth/documents');

    $req = [];
    foreach ($del_values as $value) {
        $domain = $value[0];
        $value[OPERATION] = 'DELETE';

    }

    foreach ($new_values as $value) {
        $domain = $value[0];

        $value[OPERATION] = set_action($value);
        if ($value[OPERATION] === false) continue;
        if ($value[OPERATION] === 'POST' || $value[OPERATION] === 'UPD_TEXT') {
            if ($value[DOCUMENT] !== '-') {
                $response = $doc->getResponse($value[DOCUMENT]);

                $converter = new DocToHTMLConverter();
                $value[POST_CONTENT] = $converter->convert($response);

                $value = convert_to_htmlentities($value);

                $value[POST_CONTENT]['html'] = $converter->convertAnchor(
                    $value[ANCHOR_1],
                    $value[URL_1],
                    $value[POST_CONTENT]['html']);

                $value[POST_CONTENT]['html'] = $converter->convertAnchor(
                    $value[ANCHOR_2],
                    $value[URL_2],
                    $value[POST_CONTENT]['html']);

                $value[POST_TITLE] = $doc->getTitle();
            }
        }

        $req[$domain][] = $value;
    }

    return $req;
}

function set_action($value)
{
    if (empty($value[ACTION])) return false;
    $actions = [
        'Post' => 'POST',
        'Update Text' => 'UPD_TEXT',
        'Remove' => 'REMOVE',
        //'Update Links' => 'CREATE'
        'Update Links' => 'UPD_TEXT'
    ];
    return $actions[$value[ACTION]];
}

function convert_to_htmlentities($value)
{
    $value[POST_CONTENT]['html'] = mb_convert_encoding($value[POST_CONTENT]['html'], 'UTF-8', 'HTML-ENTITIES');
    return $value;
}

function send_requests($requests)
{
    $link_manager_slug = '/wp-content/plugins/LinkManager/validate.php';
    foreach ($requests as $domain => $req) {
        $url = 'https://' . $domain . $link_manager_slug;
        $req = ['request' => $req];
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => http_build_query($req)
            )
        );
        $result = file_get_contents($url, false, stream_context_create($options) );
        if ($result === false) var_dump($req);
        usleep(100);
    }
}
