<?php
require_once __DIR__ . '\GetDocument.php';

//DocToHTMLConverter($response);

function DocToHTMLConverter($response)
{
    $content = $response->getBody()->getContent();
    //print_r($content);

    $dom = new DOMDocument(null, 'HTML-ENTITIES');
    $images = [];

    $list_begin = false;
    foreach ($content as $structuralElement) {
        if (empty($structuralElement->startIndex)) continue;
        //print_r($structuralElement);

        if ( isset($structuralElement->paragraph->bullet) ) {
            if ( !$list_begin ) {
                $ul = $dom->createElement('ul');
                $dom->appendChild($ul);
                $list_begin = true;
            }

            list_converter($structuralElement->getParagraph(), $dom, $ul);
            continue;

        } elseif ( $list_begin ) {
            //$dom->appendChild($ul);
            $list_begin = false;
        }

        paragraph_converter($structuralElement->getParagraph(), $dom);
    }

    $res = ['html'=> $dom->saveHTML(), 'images' => $images];
    return $res;
}

function paragraph_converter($paragraph, $dom)
{
    $p_styles = ['NORMAL_TEXT' => 'p',
        'HEADING_1' => 'h1',
        'HEADING_2' => 'h2',
        'HEADING_3' => 'h3',
        'HEADING_4' => 'h4'
    ];

    $p = $dom->createElement($p_styles[$paragraph->getParagraphStyle()->namedStyleType]);

    $elements = $paragraph->getElements();
    foreach ($elements as $element){
        element_converter($element, $dom, $p);

        $dom->appendChild($p);
    }
}

function element_converter($element, $dom, $p)
{
    $textStyle = $element->getTextRun()->getTextStyle();

    if ( empty($textStyle) ) {
        $textNode = $dom->createTextNode( $element->getTextRun()->getContent() );
        $p->appendChild($textNode);
    } elseif ( isset( $textStyle->getLink()->url ) ) {
        $url = $textStyle->getLink()->url;
        $a = $dom->createElement('a', $element->getTextRun()->getContent());
        $a->setAttribute('href', $url);
        $p->appendChild($a);
    } else if ( isset( $textStyle->bold ) ) {
        $b = $dom->createElement('b', $element->getTextRun()->getContent());
        $p->appendChild($b);
    } else {
        $textNode = $dom->createTextNode( $element->getTextRun()->getContent() );
        $p->appendChild($textNode);
    }
}

function list_converter($paragraph, $dom, $ul)
{
    $li = $dom->createElement('li');

    $elements = $paragraph->getElements();
    foreach ($elements as $element){
        element_converter($element, $dom, $li);
        $ul->appendChild($li);
    }
}