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
    'douban' => array('name' => 'douban', 'fake_email_suffix' => '@douban.ezengage.net'),
);


