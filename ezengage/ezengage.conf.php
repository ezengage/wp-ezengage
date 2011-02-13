<?php
define('EZENGAGE_TOKEN_ACTION', 'ezengage_token');
define('EZENGAGE_UNBIND_ACTION', 'ezengage_unbind');
define('EZENGAGE_SET_SYNC_ACTION', 'set_sync');
define('EZENGAGE_TOKEN_URL', get_option('siteurl') . '/wp-login.php?action='.EZENGAGE_TOKEN_ACTION);
define('EZENGAGE_IDENTITES_PAGE', 'ezenage_identities');

$ezengage_providers = array(
    'sinaweibo' => array('name' => '新浪微博',  'fake_email_suffix' => '@t.sina.com.cn'),
    'tencentweibo' => array('name' => '腾讯微博', 'fake_email_suffix' => '@t.qq.com'),
    'neteaseweibo' => array('name' => '网易微博', 'fake_email_suffix' => '@t.163.com'),
    'renren' => array('name' => '人人网', 'fake_email_suffix' => '@renren.com'),
);


