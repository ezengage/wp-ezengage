<?php
define('EZENGAGE_TOKEN_ACTION', 'ezengage_token');
define('EZENGAGE_UNBIND_ACTION', 'ezengage_unbind');
define('EZENGAGE_TOGGLE_AVATOR_ACTION', 'ezengage_toggle_avatar');
define('EZENGAGE_SET_SYNC_ACTION', 'set_sync');
define('EZENGAGE_TOKEN_URL', get_option('siteurl') . '/wp-login.php?action='.EZENGAGE_TOKEN_ACTION);
define('EZENGAGE_IDENTITES_PAGE', 'ezenage_identities');

$ezengage_providers = array(
    'sinaweibo' => array('name' => '新浪微博',  'fake_email_suffix' => '@sinaweibo.ezengage.net'),
    'tencentweibo' => array('name' => '腾讯微博', 'fake_email_suffix' => '@tencentweibo.ezengage.net'),
    'neteaseweibo' => array('name' => '网易微博', 'fake_email_suffix' => '@neteaseweibo.ezengage.net'),
    'renren' => array('name' => '人人网', 'fake_email_suffix' => '@renren.ezengage.net'),
    'sohuweibo' => array('name' => '搜狐微博', 'fake_email_suffix' => '@sohuweibo.ezengage.net'),
    'douban' => array('name' => '豆瓣', 'fake_email_suffix' => '@douban.ezengage.net'),
);

if(true || !function_exists('json_decode')){
    define("EZE_USE_SERVICE_JSON", 1);
}
else{
    define("EZE_USE_SERVICE_JSON", 0);
}


if(EZE_USE_SERVICE_JSON){
    if(!class_exists('Services_JSON')){
        require_once(dirname(__FILE__) .'/' . 'service_json.php');
    }
    $GLOBALS['eze_json'] = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
    function eze_json_decode($s){
        return $GLOBALS['eze_json']->decode($s);
    }
    function eze_json_encode($s){
        return $GLOBALS['eze_json']->encode($s);
    }
}
else{
    function eze_json_decode($s){
        return json_decode($s, true);
    }
    function eze_json_encode($s){
        return json_encode($s);
    }
}
