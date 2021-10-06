<?php


class Post
{
    public $domain;
    public $text_url;
    public $post_url;
    public $anchor_1;
    public $url_1;
    public $anchor_2;
    public $url_2;

    public function __construct($values)
    {
        $this->setDomain($values[0]);
        $this->setTextUrl($values[1]);
        $this->setPostUrl($values[2]);
        $this->setAnchor1($values[4]);
        $this->setUrl1($values[5]);
        $this->setAnchor2($values[6]);
        $this->setUrl2($values[7]);
    }

    public function __toString()
    {
        return $this->domain .
                $this->text_url .
                $this->post_url .
                $this->anchor_1 .
                $this->url_1 .
                $this->anchor_2 .
                $this->url_2;
    }

    /**
     * @return mixed
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param mixed $domain
     */
    public function setDomain($domain): void
    {
        $this->domain = $domain;
    }

    /**
     * @return mixed
     */
    public function getTextUrl()
    {
        return $this->text_url;
    }

    /**
     * @param mixed $text_url
     */
    public function setTextUrl($text_url): void
    {
        $this->text_url = $text_url;
    }

    /**
     * @return mixed
     */
    public function getPostUrl()
    {
        return $this->post_url;
    }

    /**
     * @param mixed $post_url
     */
    public function setPostUrl($post_url): void
    {
        $this->post_url = $post_url;
    }

    /**
     * @return mixed
     */
    public function getAnchor1()
    {
        return $this->anchor_1;
    }

    /**
     * @param mixed $anchor_1
     */
    public function setAnchor1($anchor_1): void
    {
        $this->anchor_1 = $anchor_1;
    }

    /**
     * @return mixed
     */
    public function getUrl1()
    {
        return $this->url_1;
    }

    /**
     * @param mixed $url_1
     */
    public function setUrl1($url_1): void
    {
        $this->url_1 = $url_1;
    }

    /**
     * @return mixed
     */
    public function getAnchor2()
    {
        return $this->anchor_2;
    }

    /**
     * @param mixed $anchor_2
     */
    public function setAnchor2($anchor_2): void
    {
        $this->anchor_2 = $anchor_2;
    }

    /**
     * @return mixed
     */
    public function getUrl2()
    {
        return $this->url_2;
    }

    /**
     * @param mixed $url_2
     */
    public function setUrl2($url_2): void
    {
        $this->url_2 = $url_2;
    }

}