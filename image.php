<?php

function url_is_image($url)
{
    $pattern = '/\.png$|\.jpg|\.jpeg|\.gif/';
    return preg_match($pattern, $url);
}

function get_image_extension($url)
{
    $arr = explode('.', $url);
    return '.' . array_pop($arr);
}

function get_image($url)
{
    return file_get_contents($url);
}

function get_image_name($url)
{
    $arr = explode('/', $url);
    return array_pop($arr);
}
