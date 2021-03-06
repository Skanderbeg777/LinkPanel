<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/wp-load.php';

require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

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

function LM_main_function()
{
    if ( !isset($_POST['request']) || empty($_POST['request']) ) {
        LM_error_log('LM_main_function request is empty');
        exit();
    }

    foreach ($_POST['request'] as $request) {
        LM_process_request($request);
    }
}

function LM_process_request($request)
{
    if (  $request[OPERATION] === 'POST') $request = LM_POST_request($request);
    elseif ( $request[OPERATION] === 'UPD_TEXT' ) $request = LM_UPDATE_request($request);
    elseif ( $request[OPERATION] === 'REMOVE' ) $request = LM_REMOVE_request($request);
    else return;
    send_request($request);
}

function LM_POST_request($request)
{
    $html = $request[POST_CONTENT]['html'];
    $images = $request[POST_CONTENT]['images'];
    if (!empty($images)) $thumb_image = array_shift($images);

    $post_id = LM_post_insert($request[POST_TITLE], $html);
    if (!$post_id) {
        LM_error_log('wp_insert_post() returned wp_error');
        return false;
    }

    if (!empty($images)) {
        $html = LM_replace_images_placeholders($images, $html, $post_id);
    }

    $post_data = array(
        'ID' => $post_id,
        'post_content' => $html
    );
    $post_id = wp_update_post($post_data);
    if (!$post_id) {
        LM_error_log('LM_POST_request wp_update_post() returned 0');
        return false;
    }

    $request[POST] = get_permalink($post_id);

    if ( !empty($images) ) {
        $img_id = media_sideload_image($thumb_image, $post_id, null, 'id');
        if (is_wp_error($img_id)) {
            LM_error_log('media_sideload_image() returned wp_error');
            return false;
        }
        if ( !set_post_thumbnail($post_id, $img_id) ) {
            LM_error_log('set_post_thumbnail() returned false');
            return false;
        }
    }
    $request[STATUS] = 'Done Post';
    $request[POST_CONTENT]['html'] = '-';
    $request[POST_CONTENT]['images'] = '-';
    return $request;
}

function LM_REMOVE_request($request)
{
    if ($request[POST] === '-') {
        LM_error_log('LM_REMOVE_request() cannot remove post without URL');
        return false;
    }
    $post_id = url_to_postid($request[POST]);
    if (!wp_delete_post($post_id, true))
    {
        LM_error_log('LM_REMOVE_request() wp_delete_post() returned null or wp_error');
        return false;
    }
    $request[STATUS] = 'Done Remove';
    return $request;
}

## ?????????????? ?????? ???????????????? ???????????? (?????????????????????????? ????????????????????) ???????????? ???????????? ?? ?????????????? (????????????)
add_action( 'before_delete_post', 'delete_attachments_with_post' );
function delete_attachments_with_post( $postid ){
    $post = get_post( $postid );

    $attachments = get_attached_media( '', $postid );
        if( $attachments ){
            foreach( $attachments as $attachment ) {
                if ( wp_delete_attachment( $attachment->ID, true ) === false ) {
                    LM_error_log('LM_REMOVE_request() wp_delete_attachment() returned false');
                }
            }
        }

}

function LM_UPDATE_request($request)
{
    $html = $request[POST_CONTENT]['html'];
    $images = $request[POST_CONTENT]['images'];

    if (!empty($images)) $thumb_image = array_shift($images);

    $post_id =  url_to_postid($request[POST]);
    if ( !$post_id ) {
        LM_error_log('LM_UPDATE_request url_to_postid() returned 0');
        return false;
    }

    if (!empty($images)) {
        $html = LM_replace_images_placeholders($images, $html, $post_id);
    }

    $post_data = array(
        'ID' => $post_id,
        'post_title' => $request[POST_TITLE],
        'post_content' => $html
    );
    $post_id = wp_update_post($post_data);
    if (!$post_id) {
        LM_error_log('LM_UPDATE_request wp_update_post() returned 0');
        return false;
    }

    if ( !empty($images)) {
        $img_id = media_sideload_image($thumb_image, $post_id, null, 'id');
        if (is_wp_error($img_id)) {
            LM_error_log('media_sideload_image() returned wp_error');
            return false;
        }
        if ( !set_post_thumbnail($post_id, $img_id) ) {
            LM_error_log('set_post_thumbnail() returned false');
            return false;
        }
    }
    $request[STATUS] = 'Done Update';
    $request[POST_CONTENT]['html'] = '-';
    $request[POST_CONTENT]['images'] = '-';
    return $request;
}

function LM_replace_images_placeholders($images, $html, $post_id)
{
    foreach ($images as $image) {
        $img_src = media_sideload_image($image, $post_id, null, 'html');
        if (is_wp_error($img_src)) {
            LM_error_log('media_sideload_image() returned wp_error');
            $placeholder_pattern = '/%%image_placeholder%%/';
            $html = preg_replace($placeholder_pattern, '', $html, 1);
            return $html;
        }
        $placeholder_pattern = '/%%image_placeholder%%/';
        $html = preg_replace($placeholder_pattern, $img_src, $html, 1);
    }

    return $html;
}

function LM_post_insert($post_title, $html)
{
    $slug = $post_title;
    $slug = str2url($slug);

    $post_data = array(
        'post_title' => $post_title,
        'post_content' => $html,
        'post_date' => LM_random_date(),
        'post_status' => 'publish',
        'post_author' => 1,
        'post_name' => $slug,
        'post_category' => array(1)
    );
    $post_id = wp_insert_post($post_data, true);
    if ( is_wp_error($post_id) ) {
        LM_error_log('wp_insert_post() returned wp_error');
        return false;
    }
    return $post_id;
}

function LM_random_date()
{
    $timestamp = current_time('timestamp');
    $timestamp -= rand(0, 60*60*48); // max 24 hours

    return date('Y-m-d H:i:s', $timestamp);
}

function LM_error_log($msg)
{
    $date = date("Y-m-d H:i:s");
    $msg = '[' . $date . ']' . ' LM stopped execution at: ' . $msg . "\n";
    file_put_contents('LM_error_log.txt', $msg, FILE_APPEND);
}

function send_request($request)
{
    $domain = 'doctorschoice.shop';
    $link_manager_slug = '/LinkPanel/Response.php';
    $url = 'http://' . $domain . $link_manager_slug;
    $req = ['request' => $request];
    $options = array(
        'http' => array(
            'method' => 'POST',
            'header' => 'Content-type: application/x-www-form-urlencoded',
            'content' => http_build_query($req)
        )
    );
    $result = file_get_contents($url, false, stream_context_create($options) );
    if ($result === false) var_dump($req);
    //usleep(10);
}

function rus2translit($string) {
    $converter = array(
        '??' => 'a',   '??' => 'b',   '??' => 'v',
        '??' => 'g',   '??' => 'd',   '??' => 'e',
        '??' => 'e',   '??' => 'zh',  '??' => 'z',
        '??' => 'i',   '??' => 'y',   '??' => 'k',
        '??' => 'l',   '??' => 'm',   '??' => 'n',
        '??' => 'o',   '??' => 'p',   '??' => 'r',
        '??' => 's',   '??' => 't',   '??' => 'u',
        '??' => 'f',   '??' => 'h',   '??' => 'c',
        '??' => 'ch',  '??' => 'sh',  '??' => 'sch',
        '??' => '',  '??' => 'y',   '??' => '',
        '??' => 'e',   '??' => 'yu',  '??' => 'ya',

        '??' => 'A',   '??' => 'B',   '??' => 'V',
        '??' => 'G',   '??' => 'D',   '??' => 'E',
        '??' => 'E',   '??' => 'Zh',  '??' => 'Z',
        '??' => 'I',   '??' => 'Y',   '??' => 'K',
        '??' => 'L',   '??' => 'M',   '??' => 'N',
        '??' => 'O',   '??' => 'P',   '??' => 'R',
        '??' => 'S',   '??' => 'T',   '??' => 'U',
        '??' => 'F',   '??' => 'H',   '??' => 'C',
        '??' => 'Ch',  '??' => 'Sh',  '??' => 'Sch',
        '??' => '',  '??' => 'Y',   '??' => '',
        '??' => 'E',   '??' => 'Yu',  '??' => 'Ya',
    );
    return strtr($string, $converter);
}

function str2url($str) {
    // ?????????????????? ?? ????????????????
    $str = rus2translit($str);
    // ?? ???????????? ??????????????
    $str = strtolower($str);
    // ?????????????? ?????? ???????????????? ?????? ???? "-"
    $str = preg_replace('~[^-a-z0-9_]+~u', '-', $str);
    // ?????????????? ?????????????????? ?? ???????????????? '-'
    $str = trim($str, "-");
    return $str;
}


add_action('LM_request_hook', 'LM_main_function');
do_action('LM_request_hook');
