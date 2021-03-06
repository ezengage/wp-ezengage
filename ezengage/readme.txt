﻿=== Plugin Name ===
Contributors: ftao
Donate link: http://ezengage.com/
Tags:social, sns, weibo, 新浪微博, 腾讯微博, 网易微博, 人人网, tencent, qq, sina, renren, netease, oauth, social api, login, register
Requires at least: 3.0.0
Tested up to: 3.1
Stable tag: 1.0.3.2

ezEngage - 使用新浪微博，腾讯微博，人人网, 网易微博，搜狐微博等帐号登录并可以同步文章和评论

== Description ==

[ezEngage](http://ezengage.com/ "ezEngage") 提供简单的接口给你的站点增加社会化登录(Social Login)和社会化分享(Socail Shareing)。 

= 通过社交网络帐号登录和注册 =
支持的网络有新浪微博，腾讯微博，人人网,网易微博, 搜狐微博, 豆瓣网更多网络不断添加中。

= 将社交网络帐号绑定到现有帐号: =
用户可以将社交网络帐号绑定到现有的帐号，并可设置将文章同步到各个网络。 

= 同步评论 =
通过社交网络登录后发表评论时可以选择同步到社交网络。 如果绑定了多个账户，多个账户可以同时同步。
评论的头像也支持使用社交网络帐号的头像。 

= 更多信息: =
*  [ezEngage](http://ezengage.com "ezEngage")
*  [使用教程](http://ezengage.com/support/wordpress-plugin "ezEngage WordPress Plugin 使用方法")


== Installation ==

要求 Wordpress 3.0 及以上版本

1. 复制 'ezengage' 目录和其中的内容到你的 `/wp-content/plugins/` 目录 或者在Wordpress 后台安装。

2. 通过'插件'('Plugins') 菜单激活 ezEngage 插件。

3. 访问'设置'('Settings') -> 'ezEngage' 来访问 ezEngage 配置页面。
如果你还没有ezEngage 帐号和App Key , 请访问[获得ezEngage帐号](http://ezengage.com/signup "获得ezEngage 帐号")。
如果你已经有了ezEngage 帐号和App , 将App Domain, App Id, App Key 填入表单，勾选启用ezEngage并点击保存。

== Upgrade Notice == 

= 1.0.3.1 = 
这个版本修正了未注册用户的头像出错的问题.

= 1.0.3 = 
这个版本移除curl和json_decode的依赖,同时修正了一个头像bug,建议升级。

= 1.0.2.7 = 
这个版本加入豆瓣支持

= 1.0.2.6 = 
这个版本修正了1.0.2.5中绑定多个帐号功能出现的问题的bug,建议立刻升级。

= 1.0.2.5 = 
这个版本修改登录和评论页面上ezEngage登录控件的样式,不再是单调的链接了。


== Changelog ==

= 1.0.3.1 = 
*  修正未注册用户的头像出错的问题

= 1.0.3 = 
*  移除curl和json_decode的依赖
*  同时修正了一个头像bug,建议升级

= Version 1.0.2.7 =
*  加入豆瓣支持

= Version 1.0.2.6 =
*  修复1.0.2.5引入的bug(绑定更多帐号的界面没有显示)
*  允许用户选择不显示登录界面，可以自动手工修改模版加入登录界面

= Version 1.0.2.5 =
*  提供登录界面小图表风格(默认)，并可以在后台配置

= Version 1.0.2.4 =
*  修正readme 错误

= Version 1.0.2.3 =
*  允许用户选择是否使用，使用哪一个绑定的帐号的头像

= Version 1.0.2.2 =
*  添加对搜狐微博的支持

= Version 1.0.2.1 =
*  修正评论后头像没有使用社区的头像的bug。

= Version 1.0.2 =
*  登录Widget 使用 <app-domain>.ezengage.net 域名
*  修改一些地址的引用

= Version 1.0.1 =
*  修正中文用户名可能无法登录的问题

= Version 1.0 =
*  初始版本


== Screenshots ==

1. Wordpres登录界面加入使用第三方帐号登录
2. 登录界面
3. 之前登录过的用户的登录界面
4. 评论时分享
5. 绑定帐号,设置同步和头像
6. 配置界面
7. 评论时的登录界面
