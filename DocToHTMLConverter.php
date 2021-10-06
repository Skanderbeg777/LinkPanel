<?php
require_once __DIR__ . '\image.php';

class DocToHTMLConverter
{
    private $content;
    private $images;
    private $dom;

    function convert($response)
    {
        $this->content = $response->getBody()->getContent();
        $this->images = [];
        $this->dom = new DOMDocument(null, 'HTML-ENTITIES');

        $list_begin = false;
        foreach ($this->content as $structuralElement) {
            if (empty($structuralElement->startIndex)) continue;

            if ( isset($structuralElement->paragraph->bullet) ) {
                if ( !$list_begin ) {
                    $ul = $this->dom->createElement('ul');
                    $this->dom->appendChild($ul);
                    $list_begin = true;
                }

                $this->list_converter($structuralElement->getParagraph(), $ul);
                continue;

            } elseif ( $list_begin ) {
                //$dom->appendChild($ul);
                $list_begin = false;
            }

            $this->paragraph_converter($structuralElement->getParagraph());
        }

        $res = ['html'=> $this->dom->saveHTML(), 'images' => $this->images];
        return $res;
    }

    private function paragraph_converter($paragraph)
    {
        $p_styles = ['NORMAL_TEXT' => 'p',
            'HEADING_1' => 'h1',
            'HEADING_2' => 'h2',
            'HEADING_3' => 'h3',
            'HEADING_4' => 'h4'
        ];

        $p = $this->dom->createElement($p_styles[$paragraph->getParagraphStyle()->namedStyleType]);

        $elements = $paragraph->getElements();
        foreach ($elements as $element){
            $this->element_converter($element, $p);

            $this->dom->appendChild($p);
        }
    }

    private function element_converter($element, $p)
    {
        $textStyle = $element->getTextRun()->getTextStyle();

        if ( empty($textStyle) ) {
            $textNode = $this->dom->createTextNode( $element->getTextRun()->getContent() );
            $p->appendChild($textNode);
        } elseif ( isset( $textStyle->getLink()->url ) ) {
            $url = $textStyle->getLink()->url;
            if (url_is_image($url)) {
                $this->images[] = $url;
                if (count($this->images) > 1) {
                    $img_placeholder = $this->dom->createTextNode('%%image_placeholder%%');
                    $p->appendChild($img_placeholder);
                }
                return;
            }
            $a = $this->dom->createElement('a', $element->getTextRun()->getContent());
            $a->setAttribute('href', $url);
            $p->appendChild($a);
        } else if ( isset( $textStyle->bold ) ) {
            $b = $this->dom->createElement('b', $element->getTextRun()->getContent());
            $p->appendChild($b);
        } else {
            $textNode = $this->dom->createTextNode( $element->getTextRun()->getContent() );
            $p->appendChild($textNode);
        }
    }

    private function list_converter($paragraph, $ul)
    {
        $li = $this->dom->createElement('li');

        $elements = $paragraph->getElements();
        foreach ($elements as $element){
            $this->element_converter($element, $li);
            $ul->appendChild($li);
        }
    }

    function convertAnchor($anchor, $url, $html)
    {
        if ($url === '-' || $anchor === '-') return $html;
        if (!$this->isValidURL($url)) $url = 'https://'.$url;

        $b_pattern = "~<b>".$anchor."<\/b>~";

        $replacement_string = '<a href="'.$url.'">'.$anchor.'</a>';

        return preg_replace($b_pattern, $replacement_string, $html, 1);
    }

    private function isValidURL($url)
    {
        if (preg_match('~^http://|^https://~', $url)) return true;
        else return false;
    }
}