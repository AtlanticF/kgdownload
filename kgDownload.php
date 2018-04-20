<?php
/**
 * Created by PhpStorm.
 * User: 枚峰
 * Date: 2018/2/11
 * Time: 20:57
 */

date_default_timezone_set("Asia/Shanghai");

function getIp()
{
    $arr_ip_header = array(
        'HTTP_CDN_SRC_IP',
        'HTTP_PROXY_CLIENT_IP',
        'HTTP_WL_PROXY_CLIENT_IP',
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'REMOTE_ADDR',
    );
    $client_ip = 'unknown';
    foreach ($arr_ip_header as $key)
    {
        if (!empty($_SERVER[$key]) && strtolower($_SERVER[$key]) != 'unknown')
        {
            $client_ip = $_SERVER[$key];
            break;
        }
    }
    return $client_ip;
}

//$ip = getIp();
//if ($ip != '47.97.177.62') {
//    header('Content-Type:application/json; charset=utf-8');
//    $result['msg'] = 'access deny.';
//    $result['code'] = 202;
//    $result['data'] = null;
//    echo json_encode($result);
//    return false;
//}

if (empty($_GET)) {
    header('Content-Type:application/json; charset=utf-8');
    $result['msg'] = null;
    $result['code'] = 202;
    $result['data'] = null;
    echo json_encode($result);
    return false;
}
foreach ($_GET as $get_key=>$get_var) {
    if (is_numeric($get_var)) {
        $get[strtolower($get_key)] = get_int($get_var);
    } else {
        $get[strtolower($get_key)] = get_str($get_var);
    }
}

function get_int($number)
{
    return intval($number);
}
//字符串型过滤函数
function get_str($string)
{
    if (!get_magic_quotes_gpc()) {
        return addslashes($string);
    }
    return $string;
}

$musicUrl = $get['music_url'];
$ch = curl_init();

$nodePos = strpos($musicUrl,"node");
$header = array(
    'Accept:text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
    'Cache-Control:max-age=0',
    'Connection:keep-alive',
    'Upgrade-Insecure-Requests:1',
    'User-Agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36'
);

if ($nodePos == 7 || $nodePos == 8) {
    $headerMore = array(
        'Accept-Language:zh-CN,zh;q=0.8',
        'Host:node.kg.qq.com',
        'Referer:http://node.kg.qq.com/personal?uid=639d9d8d252c358f',
        'Cookie:pgv_pvid=493310832; pgv_info=ssid=s4104160758'
    );
} else {
    $headerMore = array(
        'Host:kg.qq.com',
        'Cookie:pgv_pvid=4310131897; pgv_info=ssid=s974827865',
    );
}
$header = array_merge($header, $headerMore);
curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
//构造来路
curl_setopt($ch, CURLOPT_REFERER, "http://kg.qq.com");

//取消ssl验证
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch,CURLOPT_URL, $musicUrl);//需要抓取的页面路径
curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt ($ch, CURLOPT_TIMEOUT, 30);

$file_contents = curl_exec($ch);//抓取的内容放在变量
curl_close($ch);
if ($file_contents == false) {
    header('Content-Type:application/json; charset=utf-8');
    $result['msg'] = '请输入正确的歌曲地址';
    $result['code'] = 201;
    $result['data'] = null;
    echo json_encode($result);
    return false;
}

preg_match("/window.__DATA__.*}/", $file_contents, $matches);
$res = str_replace("window.__DATA__ = ", '', $matches);
if (!$res) {
    header('Content-Type:application/json; charset=utf-8');
    $result['msg'] = '请输入正确的歌曲地址';
    $result['code'] = 201;
    $result['data'] = null;
    echo json_encode($result);
    return false;
}
$info = json_decode($res[0], true);
$song = [];
$song['name'] = $info['detail']['song_name'];
$song['singer'] = $info['detail']['nick'];
$song['url'] = $info['detail']['playurl'] ?: $info['detail']['playurl_video'];
$song['cover'] = $info['detail']['fb_cover'];
//name中包含表情的处理
$song['singer']  = preg_replace("/\[em\]/","<img src ='http://kg.qq.com/em/", $song['singer']);
$song['singer'] = preg_replace("/\[\/em\]/","@2x.gif'>", $song['singer']);

$result = [];
header('Content-Type:application/json; charset=utf-8');
$result['data'] = $song;
if (empty($song)) {
    $result['msg'] = '获取歌曲信息失败';
    $result['code'] = 201;
} else {
    $result['msg'] = '获取歌曲信息成功';
    $result['code'] = 200;
}

echo json_encode($result);
exit;






































