<?php
/*
Plugin Name: EzEngage 
Plugin URI:  http://ezengage.com/plugin/wordpress/
Description: 给你的站点添加通过社交网络和微博帐号登录的功能
Author:  The EzEngage Team
Version: 1.0.2.5
Author URI: http://ezengage.com/blog/
*/   
   
/*  Copyright 2011

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
* Guess the wp-content and plugin urls/paths
*/
// Pre-2.6 compatibility
if ( ! defined( 'WP_CONTENT_URL' ) )
      define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
if ( ! defined( 'WP_CONTENT_DIR' ) )
      define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( ! defined( 'WP_PLUGIN_URL' ) )
      define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
if ( ! defined( 'WP_PLUGIN_DIR' ) )
      define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );


if (!class_exists('EzEngage')) {

    //require the constants 
    require_once('ezengage.conf.php');
    require_once('database.php');
    //Activation hook so the DB is created when plugin is activated
    register_activation_hook(__FILE__, 'ezengage_db_create');

    class EzEngage {
        //This is where the class variables go, don't forget to use @var to tell what they're for
        /**
        * @var string The options string name for this plugin
        */
        var $options_name = 'ezengage_options';
        
        /**
        * @var string $localizationDomain Domain used for localization
        */
        var $localizationDomain = "ezengage";
        
        /**
        * @var string $pluginurl The path to this plugin
        */ 
        var $thispluginurl = '';
        /**
        * @var string $pluginurlpath The path to this plugin
        */
        var $thispluginpath = '';
            
        /**
        * @var array $options Stores the options for this plugin
        */
        var $options = array();
        
        /**
        * @var object the ezengage api client object
        */
        var $api_client = null;

        //Class Functions
        /**
        * PHP 4 Compatible Constructor
        */
        function EzEngage(){$this->__construct();}
        
        /**
        * PHP 5 Constructor
        */        
        function __construct(){
            //Language Setup
            //$locale = get_locale();
            //$mo = dirname(__FILE__) . "/languages/" . $this->localizationDomain . "-".$locale.".mo";
            //load_textdomain($this->localizationDomain, $mo);

            //"Constants" setup
            $this->thispluginurl = WP_PLUGIN_URL . '/' . dirname(plugin_basename(__FILE__)).'/';
            $this->thispluginpath = WP_PLUGIN_DIR . '/' . dirname(plugin_basename(__FILE__)).'/';
            //if plugin_basename not working (may be because symbol link), fail back to ezengage
            if(!is_dir($this->thispluginpath)){
                $this->thispluginurl = WP_PLUGIN_URL . '/'  . 'ezengage' . '/';
                $this->thispluginpath = WP_PLUGIN_DIR . '/' . 'ezengage' . '/';
            }
            
            //Initialize the options
            //This is REQUIRED to initialize the options when the plugin is loaded!
            $this->get_options();
            

            $this->identity_table_name = EZENGAGE_IDENTITY_TABLE_NAME;

            //Actions        
            add_action("admin_menu", array(&$this,"admin_menu_link"));

            if(!$this->options['ezengage_app_domain'] || !$this->options['ezengage_app_id'] || !$this->options['ezengage_app_key']){
                return;
            }
            //如果没有启用
            if(!$this->options['ezengage_enabled']){
                return;
            }

            add_action("admin_menu", array(&$this, "admin_user_link"));

            //Widget Registration Actions
            //add_action('plugins_loaded', array(&$this,'register_widgets'));

            add_action(EZENGAGE_TOKEN_ACTION, array(&$this, 'process_token'));
            add_action(EZENGAGE_UNBIND_ACTION, array(&$this, 'unbind'));
            add_action(EZENGAGE_SET_SYNC_ACTION, array(&$this, 'set_sync_flag'));
            add_action(EZENGAGE_TOGGLE_AVATOR_ACTION, array(&$this, 'toggle_avatar'));

            add_action('init', array(&$this, 'init'));

            add_action('comment_form', array(&$this, 'comment_form'));
            add_action('login_form', array(&$this, 'login_form'));
            add_action('register_form', array(&$this, 'register_form'));

            add_action('comment_post', array(&$this, 'post_comment_to_providers'),1000);
            add_action('publish_post', array(&$this, 'sync_post_to_provider'), 0);

            add_filter('get_avatar', array(&$this, 'get_avatar'), 10, 4);

            add_action("wp_print_styles", array(&$this, 'add_style'));

            //add_action('wp_print_scripts', array(&$this, 'add_js'));

            //Filters
            /*
            add_filter('the_content', array(&$this, 'filter_content'), 0);
            */
        }
        
        /**
        * Retrieves the plugin options from the database.
        * @return array
        */
        function get_options() {
            //Don't forget to set up the default options
            if (!$theOptions = get_option($this->options_name)) {
                //$theOptions = array('default'=>'options');
                $theOptions = array();
                update_option($this->options_name, $theOptions);
            }
            $this->options = $theOptions;
            if(empty($this->options['ezengage_login_style'])){
                $this->options['ezengage_login_style'] = 'small';
            }
            if(empty($this->options['ezengage_comment_style'])){
                $this->options['ezengage_comment_style'] = 'small';
            }
            
            //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
            //There is no return here, because you should use the $this->options variable!!!
            //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        }

        /**
        * Saves the admin options to the database.
        */
        function save_admin_options(){
            return update_option($this->options_name, $this->options);
        }

        function get_api_client(){
            if($this->api_client == null){
                if(!class_exists('EzEngageApiClient')){
                    require_once(dirname(__FILE__) .'/' . 'apiclient.php');
                }
                $this->api_client = new EzEngageApiClient($this->options['ezengage_app_key']);
            }
            return $this->api_client;
        }

        function init(){
            if(!function_exists('ezengage_db_create')){
                require_once('database.php');
            }
            ezengage_db_create();

            switch($_GET['action']){
                case EZENGAGE_TOKEN_ACTION:
                    do_action(EZENGAGE_TOKEN_ACTION);
                    break;
                case EZENGAGE_UNBIND_ACTION:
                    do_action(EZENGAGE_UNBIND_ACTION);
                    break;
                case EZENGAGE_SET_SYNC_ACTION:
                    do_action(EZENGAGE_SET_SYNC_ACTION);
                    break;
                case EZENGAGE_TOGGLE_AVATOR_ACTION:
                    do_action(EZENGAGE_TOGGLE_AVATOR_ACTION);
                    break;
            }
        }

        function add_style(){
            $style_url = $this->thispluginurl . 'files/css/style.css';
            $style_path = $this->thispluginpath . 'files/css/style.css';
            if (file_exists($style_path)) {
                wp_register_style('ezengage_style', $style_url);
                wp_enqueue_style('ezengage_style');
            }
        }

        function add_admin_style(){
            $style_url = $this->thispluginurl . 'files/css/style.css';
            $style_path = $this->thispluginpath . 'files/css/style.css';
            if (file_exists($style_path)) {
                wp_register_style('ezengage_admin_style', $style_url);
                wp_enqueue_style('ezengage_admin_style');
            }
        }

        /**
        * @desc Modify the comment_form, add sync to providers checkbox if logged , or show the ezengage login widget
        */
        function comment_form($post_id=""){
            global $ezengage_providers;
            if(is_user_logged_in()){
                global $user_ID;
                $identities = $this->get_identities($user_ID);
                if(count($identities) > 0){
        ?>
                <p id="ezenage_comment_sync">
                <label>同步评论到</label>
        <?php
                    foreach($identities as $identity){
                        $provider = $identity->provider;
                        if($provider && $ezengage_providers[$provider]){
        ?>
                            <input name="sync_to_provider[]" type="checkbox" checked="checked" id="sync_to_provider_<?php echo $provider;?>"" value="<?php echo $provider;?>" class="sync_to_provider" />
                            <span>
                            <?php echo $ezengage_providers[$provider]['name'];?> 
                            </span>
                    <?php } ?>
                <?php } ?>
                </p>
        <?php	
                }
            }
            else{
                $this->connect_widget(true, $this->options['ezengage_comment_style']);
            } 
        }

        function login_form(){
            $this->connect_widget(false, $this->options['ezengage_login_style']);
        }

        function register_form(){
            $this->connect_widget(false, $this->options['ezengage_login_style']);
        }

        /**
        * @param should_redirect_back  if set to true, we will redirect back to current page after success login,
        *        otherwise, we will redirect to the profile page. Set this to false, if you are in the login /reigster page
        */
        function connect_widget($should_redirect_back = true, $style = 'link', $force_choose = false){
            $token_url = EZENGAGE_TOKEN_URL;
            if($should_redirect_back){
                $current_page = $_SERVER['REQUEST_URI'];
                $token_url .= '&redirect_to='. urlencode($current_page);
            }
            //TODO GET IT FROM OPTIONS
            if($style == 'small'){
                $widget_url = sprintf('http://%s.ezengage.net/login/%s/widget/small?token_cb=%s', 
                    $this->options['ezengage_app_domain'], $this->options['ezengage_app_domain'], urlencode($token_url));
            }
            else{
                $widget_url = sprintf('http://%s.ezengage.net/login/%s/widget?token_cb=%s', 
                    $this->options['ezengage_app_domain'], $this->options['ezengage_app_domain'], urlencode($token_url));
            }
            if($force_choose){
                $widget_url .= "&force_choose=1";
            }
            ?>
            <div class="ezengage_login_widget ezengage_login_widget_<?php echo $style; ?>" style="overflow:hidden;">
            <?
            if ($style == 'link'){
            ?>
            <link rel="stylesheet" type="text/css" href="http://loginmedia.ezengage.com/css/eze.css" />
            <script type="text/javascript" src="http://loginmedia.ezengage.com/js/ezelib-all.js"></script>
            <a href="<?php echo $widget_url;?>" class="ezengage" title="使用微博或社交网络帐号登录" onclick="return false">使用微博或社交网络帐号登录</a>
            <script type="text/javascript">
                EZE.overlay = true;
            </script>
        <?php
            }
            else if ($style == 'small'){
            ?>
            <iframe boder="0" src="<?php echo $widget_url;?>"  scrolling="no" frameBorder="no" style="width:auto;height:130px;overflow:hidden;padding:0;margin:0;"></iframe>
            <?php
            } elseif ($style == 'normal'){
            ?>
            
                <iframe boder="0" src="<?php echo $widget_url;?>"  scrolling="no" frameBorder="no" style="width:350px;height:190px;"></iframe>
            <?php 
            }
            ?>
            </div>
            <?php
        }

        function get_avatar($avatar, $id_or_email='',$size='32') {
            global $wpdb;
            global $comment;
            if(is_object($comment)) {
                $id_or_email = $comment->user_id;
            }
            if (is_object($id_or_email)){
                $id_or_email = $id_or_email->user_id;
            }
            $user_id = $this->get_user_by_id_or_email($id_or_email);
            if($user_id > 0){  
                $sql = "SELECT avatar_url FROM {$this->identity_table_name} 
                        WHERE user_id = %s AND enable_avatar = 1 AND avatar_url IS NOT NULL 
                        ORDER BY id LIMIT 1;";
                $avatar_url = $wpdb->get_var($wpdb->prepare($sql, array($user_id)));
                if($avatar_url){
                    $avatar = "<img alt='' src='{$avatar_url}' class='avatar avatar-{$size}' height='{$size}' width='{$size}' />";
                }
            }
            return $avatar;
        }

        function process_token(){
            $token = $_POST['token'];
            if($token){
                //使用token 和 app_key 请求profile  API 
                //TODO RAD API KEY FROM DATABASE
                $client = $this->get_api_client();
                $profile = $client->getProfile($token);
                if($profile === false){
                    //TODO:you can log the error, report to us. use $client->getLastResponse();
                    return;
                }
                if(is_user_logged_in()){
                    global $user_ID;
                    $this->bind($profile, $user_ID);
                }
                else{
                    $this->bind($profile, null);
                } 
            }
        }

        /**
         * @desc bind a profile to user (and create the user if necessary)
         */
        function bind($profile, $user_id) {
            //TODO MOVE TO SOME WHERE ELSE
            if(!$profile) {
                wp_die("An error occurred while trying to get user profile.");
            }
            global $wpdb;
            global $ezengage_providers;
            $wpuid = $this->get_user_by_identity($profile['identity']);
            //bind to self or not login
            if($wpuid  && (!$user_id || $wpuid == $user_id)){
                $wpdb->update($this->identity_table_name, array('profile' => json_encode($profile)), array(
                        'identity' => $profile['identity'],
                ));
            }
            else if($wpuid && $wpuid != $user_id){
                wp_die("很抱歉，这个帐号已经连接到了另一个用户。");
            }
            else if(!$wpuid){
                //new user
                if(!$user_id){
                    $username = preg_replace('/\W/', '', $profile['preferred_username']);
                    if(strlen($username) <= 0){
                        $username = $profile['provider_code'] . '_user';
                    }
                    $wpuid = $this->get_user_by_login($username);
                    $suffix = 0;
                    while($wpuid){
                        $suffix ++;
                        $the_username = $username . $suffix;
                        $wpuid = $this->get_user_by_login($the_username);
                        if(!$wpuid){
                            $username = $the_username;
                        }
                    }
                    $user_email = $profile['email'] ? $profile['email'] : $username. $ezengage_providers[$profile['provider_code']]['fake_email_suffix'];
                    $userdata = array(
                        'user_pass' => wp_generate_password(),
                        'user_login' => $username,
                        'display_name' => $profile['display_name'],
                        'user_url' => '',
                        //TODO:SHOULD USING A OPTION ? 
                        'user_email' => $user_email,
                    );
                    if(!function_exists('wp_insert_user')){
                        include_once( ABSPATH . WPINC . '/registration.php' );
                    } 
                    $wpuid = wp_insert_user($userdata);
                    if(is_object($wpuid) && $wpuid->errors){
                        wp_die($wpuid->errors);
                    }
                }
                //bind new identity to exisit account
                else{
                    $wpuid = $user_id;
                }
                $ret = $wpdb->insert($this->identity_table_name, array('user_id' => $wpuid,
                        'identity' => $profile['identity'],
                        'provider' => $profile['provider_code'],
                        'profile' => json_encode($profile)
                ));
            }
            if($wpuid) {
                update_usermeta($wpuid, 'ezengage_last_provider', $profile['provider_code']);
                update_usermeta($wpuid, 'ezengage_last_identity', $profile['identity']);
                update_usermeta($wpuid, 'ezengage_avatar_url', $profile['avatar_url']);
                wp_set_auth_cookie($wpuid, true, false);
                wp_set_current_user($wpuid);
                $this->post_bind();
            }
        }

        /**
         * @desc do something after success bind a identity
         */
        function post_bind(){
            if(is_user_logged_in()){
                if($_GET['redirect_to']){
                    $redirect_to = strval($_GET['redirect_to']);
                }
                if(empty($redirect_to)){
                    $redirect_to = admin_url('profile.php');
                }
                wp_safe_redirect($redirect_to);
            }
        }

        /**
         * @desc post comment to providers 
         */
        function post_comment_to_providers($id){
            global $user_ID;
            $comment_post_id = $_POST['comment_post_ID'];
            
            if(!$comment_post_id){
                return;
            }
            $current_comment = get_comment($id);
            $current_post = get_post($comment_post_id);
            if(is_array($_POST['sync_to_provider'])){
                global $wpdb;
                $sql = "SELECT identity FROM {$this->identity_table_name} WHERE user_id = %d AND provider = %s";
                $client = $this->get_api_client();
                $status = $current_comment->comment_content. ' '.get_permalink($comment_post_id)."#comment-".$id;
                foreach($_POST['sync_to_provider'] as $provider){
                    $identity = $wpdb->get_var($wpdb->prepare($sql, $user_ID, $provider));
                    if($identity){
                        $ret = $client->updateStatus($identity, $status);
                    }
                }
            }
        }

        function unbind(){
            global $wpdb;
            #TODO:connect_account should be a constant
            if(is_user_logged_in()){
                global $user_ID;
                if($_GET['identity']){
                    $sql = "DELETE FROM {$this->identity_table_name} WHERE user_id = %d AND identity = %s";
                    $wpdb->query($wpdb->prepare($sql, $user_ID, $_GET['identity']));
                }
            }
            wp_safe_redirect(get_option('siteurl') . '/wp-admin/profile.php?page=' . EZENGAGE_IDENTITES_PAGE);
        }

        function identities_page() {
            global $user_ID;
            global $ezengage_providers;
            $identities = $this->get_identities($user_ID);
        ?>
        <div class="wrap">
            <h2>第三方帐号</h2>
            <h3>已连接的帐号</h3>
            <table id="ezengage_identity_table">
            <tr>
                <th>连接到的服务</th>
                <th>帐号</th>
                <th>同步文章</th>
                <th>头像</th>
                <th>是否使用该头像</th>
                <th>操作</th>
            </tr>
        <?php
            foreach($identities as $identity):
                $profile = json_decode($identity->profile);
                $account_name = $profile->display_name;
        ?>
                <tr>
                    <td><?php echo $ezengage_providers[$identity->provider]['name']; ?></td>
                    <td><?php echo $account_name; ?></td>
                    <td>
                        <form method="post" action="?action=<?php echo EZENGAGE_SET_SYNC_ACTION ?>">
                        <input type="checkbox" id="sync_post_<?php $identity->provider;?>" class="sync_to_post" value="on"
                            name="sync"
                            onclick="this.form.submit();"
                            <?php if ($identity->sync){ ?>checked="checked"<?php }?>/>
                        <input type="hidden" name="identity" value="<?php echo js_escape($identity->identity);?>"/>
                        </form>
                    </td>
                    <td>
                        <img src="<?php echo $identity->avatar_url ?>" height="32"/>
                    </td>
                    <td>
                        <form method="post" action="?action=<?php echo EZENGAGE_TOGGLE_AVATOR_ACTION ?>">
                        <input type="checkbox" id="toggle_avatar_<?php $identity->id;?>" class="enable_avatar" value="on"
                            name="enable_avatar"
                            onclick="this.form.submit();"
                            <?php if ($identity->enable_avatar){ ?>checked="checked"<?php }?>/>
                        <input type="hidden" name="identity" value="<?php echo js_escape($identity->identity);?>"/>
                        </form>
                    </td>
                    <!-- THAT SHOULD BE POST -->
                    <td><a href="?action=<?php echo EZENGAGE_UNBIND_ACTION;?>&identity=<?php echo urlencode($identity->identity);?>">解除连接</a></td>
                </tr>
        <?php
            endforeach;
        ?>
            </table>
            <p>
               关于头像的说明，勾选使用该头像后将在评论等地方使用改头像，如果有多个头像被勾选，会使用第一个。<br/>
               如果是网易微博，头像有防盗链，可能不能显示。　请参考<a href="http://open.t.163.com/wiki/index.php?title=%E9%A6%96%E9%A1%B5#.E5.8A.A0.E5.85.A5.E5.BC.80.E6.94.BE.E5.B9.B3.E5.8F.B0.E4.B8.8E.E6.84.8F.E8.A7.81.E5.8F.8D.E9.A6.88">网易申请域名白名单的方法</a>
            </p>
            <h3>连接更多帐号</h3>
        <?php
            $this->connect_widget(true, 'iframe', true);
        ?>
        </div>
        <?php
        }

        function get_user_by_identity($identity){
            global $wpdb;
            $sql = "SELECT user_id FROM {$this->identity_table_name} WHERE identity = '%s';";
            return $wpdb->get_var($wpdb->prepare($sql, $identity));
        }

        function set_sync_flag(){
            if(!is_user_logged_in()){
                return;
            }
            global $user_ID;
            global $wpdb;
            $identity = $_POST['identity'];
            $sync = $_POST['sync'] == 'on' ? 1 : 0;
            $ret = $wpdb->update($this->identity_table_name, array('sync' => $sync), array('identity' => $identity, 'user_id' => $user_ID));
            wp_safe_redirect(get_option('siteurl') . '/wp-admin/profile.php?page=' . EZENGAGE_IDENTITES_PAGE);
        }

        function toggle_avatar(){
            if(!is_user_logged_in()){
                return;
            }
            global $user_ID;
            global $wpdb;
            $identity = $_POST['identity'];
            $enable_avatar = $_POST['enable_avatar'] == 'on' ? 1 : 0;
            $ret = $wpdb->update($this->identity_table_name, array('enable_avatar' => $enable_avatar), array('identity' => $identity, 'user_id' => $user_ID));
            wp_safe_redirect(get_option('siteurl') . '/wp-admin/profile.php?page=' . EZENGAGE_IDENTITES_PAGE);
        }



        function sync_post_to_provider($post_ID){
            $is_synced = get_post_meta($post_ID, 'ezengage_sync', true);
            if($is_synced) return;

            global $wpdb;
            global $user_ID;
            $c_post = get_post($post_ID);
            $status = $c_post->post_title.' '.get_permalink($post_ID);

            $sql = "SELECT identity FROM {$this->identity_table_name} WHERE user_id = %d AND sync = 1";
            $ids = $wpdb->get_results($wpdb->prepare($sql, $user_ID));
            if(count($ids) > 0){
                $client = $this->get_api_client();
                foreach($ids as $identity){
                    $ret = $client->updateStatus($identity->identity, $status);
                }
            }
            add_post_meta($post_ID, 'ezengage_sync', 'true', true);
        }

        function get_identities($wpuid){
            global $wpdb;
            $sql = "SELECT identity,provider,sync,profile,enable_avatar,avatar_url FROM {$this->identity_table_name} WHERE user_id = %s";
            return $wpdb->get_results($wpdb->prepare($sql, $wpuid));
        }

        function get_user_by_login($user_login) {
            global $wpdb;
            $sql = "SELECT ID FROM $wpdb->users WHERE user_login = '%s'";
            return $wpdb->get_var($wpdb->prepare($sql, $user_login));
        }

        function get_user_by_id_or_email($id_or_email) {
            global $wpdb;
            if(intval($id_or_email) > 0){
                $sql = "SELECT ID FROM $wpdb->users WHERE ID = '%s'";
            }
            else{
                $sql = "SELECT ID FROM $wpdb->users WHERE email = '%s'";
            }
            return $wpdb->get_var($wpdb->prepare($sql, $id_or_email));
        }



        /**
        * @desc Adds the options subpanel
        */
        function admin_menu_link() {
            //If you change this from add_options_page, MAKE SURE you change the filter_plugin_actions function (below) to
            //reflect the page filename (ie - options-general.php) of the page your plugin is under!
            add_options_page('EzEngage', 'EzEngage', 10, basename(__FILE__), array(&$this,'admin_options_page'));
            add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array(&$this, 'filter_plugin_actions'), 10, 2 );
        }

        function admin_user_link() {
            $page = add_submenu_page('users.php', '第三方帐号', '第三方帐号', 'read', EZENGAGE_IDENTITES_PAGE, array(&$this, 'identities_page'));
            add_action('admin_print_styles-' . $page, array($this, 'add_admin_style'));
        }
 
        
        /**
        * @desc Adds the Settings link to the plugin activate/deactivate page
        */
        function filter_plugin_actions($links, $file) {
           //If your plugin is under a different top-level menu than Settiongs (IE - you changed the function above to something other than add_options_page)
           //Then you're going to want to change options-general.php below to the name of your top-level page
           $settings_link = '<a href="options-general.php?page=' . basename(__FILE__) . '">' . __('Settings') . '</a>';
           array_unshift( $links, $settings_link ); // before other links

           return $links;
        }
        
        /**
        * Adds settings/options page
        */
        function admin_options_page() { 
            if($_POST['ezengage_save']){
                if (! wp_verify_nonce($_POST['_wpnonce'], 'ezengage-update-options') ) die('Whoops! There was a problem with the data you posted. Please go back and try again.'); 
                $this->options['ezengage_app_domain'] = $_POST['ezengage_app_domain'];                   
                $this->options['ezengage_app_id'] = $_POST['ezengage_app_id'];
                $this->options['ezengage_app_key'] = $_POST['ezengage_app_key'];
                $this->options['ezengage_enabled'] = ($_POST['ezengage_enabled']=='on')?true:false;

                $this->options['ezengage_login_style'] = $_POST['ezengage_login_style'];
                $this->options['ezengage_comment_style'] = $_POST['ezengage_comment_style'];
                                        
                $this->save_admin_options();
                
                echo '<div class="updated"><p>成功! 你的修改已成功保存!</p></div>';
            }
            $login_styles = array(
                'link' => '链接',
                'small' => '小图标',
            );
            $comment_styles = array(
                'link' => '链接',
                'small' => '小图标',
                'normal' => '大图标',
            );
 
            if(!$this->options['ezengage_app_domain'] || !$this->options['ezengage_app_id'] || !$this->options['ezengage_app_key']){
                echo '<div class="error"><p>你的配置不完整，EzEngage 需要下面的配置才能正常工作。</p></div>';
            }
?>                                   
                <div class="wrap">
                <h2>EzEngage 设置</h2>
                <form method="post" id="ezengage_options">
                <?php wp_nonce_field('ezengage-update-options'); ?>
                    <table width="100%" cellspacing="2" cellpadding="5" class="form-table"> 
                        <tr valign="top"> 
                            <th width="33%" scope="row"><?php _e('App Domain:', $this->localizationDomain); ?></th> 
                            <td><input name="ezengage_app_domain" type="text" id="ezengage_app_domain" size="20" value="<?php echo $this->options['ezengage_app_domain'] ;?>"/>
                        </td> 
                        </tr>
                        <tr valign="top"> 
                            <th width="33%" scope="row"><?php _e('App ID:', $this->localizationDomain); ?></th> 
                            <td><input name="ezengage_app_id" type="text" id="ezengage_app_id" size="20" value="<?php echo $this->options['ezengage_app_id'] ;?>"/>
                            </td> 
                        </tr>
                        <tr valign="top"> 
                            <th width="33%" scope="row"><?php _e('App Key:', $this->localizationDomain); ?></th> 
                            <td><input name="ezengage_app_key" type="text" id="ezengage_app_key" size="50" value="<?php echo $this->options['ezengage_app_key'] ;?>"/>
                            </td> 
                        <tr valign="top"> 
                            <th><label for="ezengage_enabled"><?php _e('启用ezengage:', $this->localizationDomain); ?></label></th><td><input type="checkbox" id="ezengage_enabled" name="ezengage_enabled" <?echo ($this->options['ezengage_enabled']==true)?'checked="checked"':''?>></td>
                        </tr>

                        <tr valign="top"> 
                            <td colspan="2" style="border-top:1px solid gray;">以下为高级选项,一般保持默认即可:</td>
                        </tr>
                        <tr valign="top"> 
                            <th><label for="ezengage_login_style"><?php _e('登录页登录框风格:', $this->localizationDomain); ?></label>
                            </th>
                            <td>
                                <?php $ezengage_login_style = $this->options['ezengage_login_style']; ?>
                                <select id="ezengage_login_style" name="ezengage_login_style">
                                    <?php foreach($login_styles as $key=>$value): ?>
                                    <option value="<?php echo $key; ?>" <?php echo ($ezengage_login_style == $key)?'selected="selected"' : '';?>><?php echo $value; ?></option>
                                    <?php endforeach;?>
                                </select>
                            </td>
                        </tr>
 
                       <tr valign="top"> 
                            <th><label for="ezengage_comment_style"><?php _e('评论页登录框风格:', $this->localizationDomain); ?></label>
                            </th>
                            <td>
                                <?php $ezengage_comment_style = $this->options['ezengage_comment_style']; ?>
                                <select id="ezengage_comment_style" name="ezengage_comment_style">
                                    <?php foreach($comment_styles as $key=>$value): ?>
                                    <option value="<?php echo $key; ?>" <?php echo ($ezengage_comment_style == $key)?'selected="selected"' : '';?>><?php echo $value; ?></option>
                                    <?php endforeach;?>
                                </select>
                            </td>
                        </tr>
 

                        <tr>
                            <th colspan=2><input class="button-primary" type="submit" name="ezengage_save" value="保存" /></th>
                        </tr>
                    </table>
                </form>
                <?php
        }
        
  } //End Class
} //End if class exists statement

//instantiate the class
if (class_exists('EzEngage')) {
    $EzEngage_var = new EzEngage();
}
?>
