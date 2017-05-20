<?php
/*
Plugin Name: Refresh AliCDN
Plugin URI: https://jackyu.cn/projects/refresh_alicdn
Description: A tweak can help you refreshing your aliyun cdn cache without logining to the aliyun console.
Version: 1.3
Author: Jacky
Author URI: https://jackyu.cn/
License: GPL2
*/

/*  Copyright 2017 0xJacky  (email : me@jackyu.cm)

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

register_activation_hook( __FILE__, 'generate_password');

function generate_password() {
    add_option('cdn_post_pw', wp_generate_password(10,false)); //为 do.php post 请求创建随机密码
}

register_deactivation_hook( __FILE__, 'refresh_alicdn_remove' ); //注册卸载时执行的函数

function refresh_alicdn_remove() {
	delete_option('cdn_post_pw');
	delete_option('cdn_access_key_id');
	delete_option('cdn_access_key_secret');
	delete_option('cdn_refresh_type');
	delete_option('cdn_refresh_urls');
}

if ( is_admin() ) {
	add_action( 'admin_bar_menu', 'refresh_button', 90 );
	add_action('admin_footer', 'add_toastr_style');
	add_action('admin_footer', 'show_toastr' );
	add_action('admin_menu', 'aliyun_cdn_menu');
}

function refresh_button( $meta = TRUE ) {
  global $wp_admin_bar;
  if ( is_super_admin() || is_admin_bar_showing() ) {
    $wp_admin_bar->add_menu( array(
      'id' => 'refresh',
      'title' => __( '刷新 CDN 缓存' ),
      'href' => '#',
      'meta'  => array( onclick => 'refresh()' ) )
    );
  }
}

function add_toastr_style() {
  if ( is_super_admin() || is_admin_bar_showing() ) {
    wp_register_style( 'toastrCSS', '//cdn.bootcss.com/toastr.js/latest/css/toastr.min.css' );
    wp_register_script( 'toastrJS', '//cdn.bootcss.com/toastr.js/latest/js/toastr.min.js' );
    wp_register_script( 'jqueryJS', '//cdn.bootcss.com/jquery/3.2.1/jquery.min.js' );
    wp_enqueue_style('toastrCSS');
    wp_enqueue_script('toastrJS');
    wp_enqueue_script('jqueryJS');
  }
}

function show_toastr() {
  echo "<script>function refresh() {
    $.ajax({
			type:\"post\",
      url: \"".plugins_url('do.php', __FILE__)."\",
			data: \"key=".get_option('cdn_post_pw')."\",
      success: function(data) {
        if( data.result==1 ) {
          toastr.success(data.message);
        }
        if( data.result==2 ) {
          toastr.error(data.message);
        }
				if( data.result==3 ) {
					toastr.warning(data.message);
				}
      }
    });}</script>";
  }

function aliyun_cdn_menu() {
		add_action( 'admin_init', 'register_refresh_alicdn_settings' );
    add_options_page('阿里云 CDN 刷新设置', '阿里云 CDN 刷新设置', 'administrator','refresh_alicdn', 'refresh_alicdn_settings_page');

}

function register_refresh_alicdn_settings() {
	register_setting('refresh_alicdn', 'cdn_access_key_id');
	register_setting('refresh_alicdn', 'cdn_access_key_secret');
	register_setting('refresh_alicdn', 'cdn_refresh_type');
	register_setting('refresh_alicdn', 'cdn_refresh_urls');
}
function refresh_alicdn_settings_page() {
	$type = is_numeric(get_option('cdn_refresh_type')) ? esc_attr(get_option('cdn_refresh_type')) : 1;
  /* 载入 Aliyun CDN SDK */
  include_once 'aliyun-php-sdk-core/Config.php';
  include_once 'aliyun-php-sdk-cdn/Request/v20141111/DescribeRefreshQuotaRequest.php';

  //getProfile的三个参数分别是：region, Access Key ID, Access Key Secret
  if (get_option('cdn_access_key_id') && get_option('cdn_access_key_secret')) {
    $iClientProfile = DefaultProfile::getProfile("cn-hangzhou", get_option('cdn_access_key_id'), get_option('cdn_access_key_secret'));
    $client = new DefaultAcsClient($iClientProfile);
    $request = new \Cdn\Request\V20141111\DescribeRefreshQuotaRequest();
    $request->setMethod("GET");
    $response = $client->getAcsResponse($request);
  }
?>
<div class="wrap" style="margin: 10px">
  <h1>阿里云 CDN 刷新设置</h1>
  <form method="post" action="options.php">
    <?php settings_fields( 'refresh_alicdn' ); ?>
    <?php do_settings_sections( 'refresh_alicdn' ); ?>
    <hr>
    <fieldset>
      <h2>Access Key ID</h2>
      <input type="text" name="cdn_access_key_id" value="<?php echo get_option('cdn_access_key_id'); ?>" />
      <p>请在此输入您在阿里云管理控制台中获取到的 Access Key ID</p>
    </fieldset>
    <fieldset>
      <h2>Access key Secret</h2>
      <input type="text" name="cdn_access_key_secret" value="<?php echo get_option('cdn_access_key_secret'); ?>" />
      <p>请在此输入您在阿里云管理控制台中获取到的 Access key Secret</p>
    </fieldset>
		<fieldset>
      <h2>刷新文件类型</h2>
      <ol><input type="radio" name="cdn_refresh_type" value="1" <?php echo checked( 1, $type, false); ?>/>仅刷新 style.css</ol>
      <ol><input type="radio" name="cdn_refresh_type" value="2" <?php echo checked( 2, $type, false); ?>/>仅刷新自定义 URL</ol>
			<ol><input type="radio" name="cdn_refresh_type" value="3" <?php echo checked( 3, $type, false); ?>/>刷新 style.css 和自定义 URL</ol>
			<ol><input type="radio" name="cdn_refresh_type" value="4" <?php echo checked( 4, $type, false); ?>/>刷新主题内所有文件和自定义 URL</ol>
			<p>仅支持当前启用主题内的文件，如有子主题且需要刷新父主题内的文件，请将 URL 添加到刷新自定义 URL 中。一般情况下<font color="#F40">不建议选择</font>刷新主题内所有文件和自定义 URL，仅建议在特殊情况下，如更新主题后使用。</p>
    </fieldset>
		<fieldset>
      <h2>刷新自定义 URL</h2>
      <textarea type="text" cols="60" rows="10" name="cdn_refresh_urls" /><?php echo get_option('cdn_refresh_urls'); ?></textarea>
      <p>多个URL请用回车分隔，每个URL应当以 http:// 或 https:// 开头，一次提交不能超过100个URL</p>
      <?php if (get_option('cdn_access_key_id') && get_option('cdn_access_key_secret')) { ?>
			<p>注意：您的账户每天最多可以刷新(含预热)<?php echo $response->UrlQuota; ?>个文件(URL)和<?php echo $response->DirQuota; ?>个目录。刷新任务生效时间大约为5分钟。</p>
      <p>今日还可以刷新目录 <?php echo $response->DirRemain;?>次，刷新URL <?php echo $response->UrlRemain;?> 个。</p>
      <?php } ?>
    </fieldset>
    <hr>
    <?php submit_button(); ?>
  </form>
</div>
<?php
}
?>
