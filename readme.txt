=== Aliyun CDN Helper ===
Contributors: 0xJacky
Donate link: https://jackyu.cn/projects/aliyun-cdn-helper
Tags: aliyun, cdn
Requires at least: 4.6
Tested up to: 4.8.2
Stable tag: 2.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

本插件为 WordPress 的管理页面的工具栏添加快捷按钮，方便站长在修改完静态资源后能快速刷新 CDN 缓存。

This plugin adds a shortcut button to the toolbar of WordPress's admin page, which can quickly refresh the CDN cache after modifying the static resources.

== Description ==

本插件为 WordPress 的管理页面的工具栏添加快捷按钮，方便站长在修改完静态资源后能快速刷新 CDN 缓存。

This plugin adds a shortcut button to the toolbar of WordPress's admin page, which can quickly refresh the CDN cache after modifying the static resources.

使用阿里云官方出品的 `aliyun-openapi-php-sdk` 作为 SDK，以确保您的隐私不会被泄露

This plugin use `aliyun-openapi-php-sdk` as the SDK, all the request will only sent to Alibaba Cloud Computing Ltd.


### 插件特色
1. 一键刷新缓存
2. 配置简单
3. 设置页面可以显示当日的刷新剩余量
4. 中英文支持
5. 支持文件刷新和目录刷新
6. 支持批量预热文件

### The features of this plugin
1. One click to refresh the CDN cache.
2. Easy to configure.
3. Setting page can display the refresh amount of the day.
4. Supports Chinese and English.
5. Supports file refresh and directory refresh.
6. Support batch files preheat.

== Installation ==

1. 将压缩包解压，上传到 `/wp-content/plugins/` 或通过 WordPress 插件商店安装
2. 启用插件
3. 进入 `设置->阿里云 CDN` 完成配置后即可使用

1. Upload the plugin files to the `/wp-content/plugins/plugin-name` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Settings->Aliyun CDN screen to configure the plugin


== Frequently Asked Questions ==

= 刷新文件类型 =

这个是对快捷按钮的设置，如果选择 `刷新当前主题目录内的静态文件（目录）` 或 `全站刷新（目录）` 则请求强制回源拉取更新的目录（使用一个刷新目录配额）。

= URL 预热 =

预热可以将源站的内容主动预热到 L2 Cache 节点上，用户首次访问可直接命中缓存，缓解源站压力，

如果您是首次使用 CDN 和这个插件，您可以点击预热按钮，插件将会自动搜索当前主题目录类的静态资源文件，处理成标准的 URL 格式后提交到预热接口。

由于阿里云的 SDK 不支持批量刷新，所以现在使用循环提交的方式，主题内文件较多的话等待时间可能稍微长一点，具体使用 URL 配额由文件数量决定。

= 刷新自定义 URL =

功能如题所示，对应阿里云 CDN 控制台的 URL 刷新，如果强行要输入目录的话不要忘记末尾的 `/`。

容错机制暂未完善，一旦出现异常将立即返回错误，如果刷新错误请检查每一行的 URL 书写是否规范。

== Screenshots ==

1. `screenshot-1.png`
2. `screenshot-2.png`

== Changelog ==

== 2.1 ==
增加翻译

= 2.0 =
1. 代码重构
2. 新增预热、刷新目录

== Upgrade Notice ==

= 2.0 =
1. 代码重构
2. 新增预热、刷新目录
