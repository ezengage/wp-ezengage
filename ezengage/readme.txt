=== Plugin Name ===
Contributors: ftao
Donate link: http://ezengage.com/
Tags:social, sns, weibo, 新浪微博, 腾讯微博, 网易微博, 人人网, tencent, qq, sina, renren, netease, oauth, social api, login, register
Requires at least: 3.0.0
Tested up to: 3.0
Stable tag: 1.0.2.2

ezEngage - 使用新浪微博，腾讯微博，人人网, 网易微博，搜狐微博等社交网络帐号登录你的wordpress 博客并可以同步文章和评论

== Description ==

[ezEngage](http://ezengage.com/ "ezEngage") 提供简单的接口给你的站点增加社会化登录(Social Login)和社会化分享(Socail Shareing)。 

= 通过社交网络帐号登录和注册 =
支持的网络有新浪微博，腾讯微博，人人网,网易微博, 更多网络不断添加中。

= 将社交网络帐号绑定到现有帐号: =
用户可以将社交网络帐号绑定到现有的帐号，并可设置将文章同步到各个网络。 

= 同步评论 =
通过社交网络登录后发表评论时可以选择同步到社交网络。 如果绑定了多个账户，多个账户可以同时同步。
评论的头像也支持使用社交网络帐号的头像。 

= 更多信息: =
*  [ezEngage](http://ezengage.com "ezEngage")


== Installation ==

要求PHP 5.2 (带JSON) 以上版本，要求 PHP/cURL 扩展, 要求 Wordpress 3.0

1. 复制 'ezengage' 目录和其中的内容到你的 `/wp-content/plugins/` 目录 或者在Wordpress 后台安装。

2. 通过'插件'('Plugins') 菜单激活 ezEngage 插件。

3. 访问'设置'('Settings') -> 'ezEngage' 来访问 ezEngage 配置页面。
如果你还没有ezEngage 帐号和App Key , 请访问[获得ezEngage帐号](http://ezengage.com/signup "获得ezEngage 帐号")。
如果你已经有了ezEngage 帐号和App , 将App Domain, App Id, App Key 填入表单，勾选启用ezEngage并点击保存。

== Changelog ==

= Version 1.0 =
*  初始版本

= Version 1.0.1 =
*  修正中文用户名可能无法登录的问题

= Version 1.0.2 =
*  登录Widget 使用 <app-domain>.ezengage.net 域名
*  修改一些地址的引用

= Version 1.0.2.1 =
*  修正评论后头像没有使用社区的头像的bug。

= Version 1.0.2.2 =
*  添加对搜狐微博的支持



== Screenshots ==

1. 登录界面包含"使用社交网络帐号登录链接"
2. 登录界面
3. 之前登录过的用户的登录界面
4. 评论时分享
5. 绑定帐号
6. 配置界面
