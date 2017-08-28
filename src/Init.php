<?php
/**
 * Aliyun CDN Helper
 * Copyright 2017 0xJacky (email : jacky-943572677@qq.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

namespace CDN\WP;
defined( 'ALIYUN_CDN_PATH' ) OR exit();

class Init {
	function __construct() {
		if ( is_admin() ) {
			add_action( 'admin_bar_menu', array( $this, 'refresh_button' ), 90 );
			add_action( 'admin_footer', array( $this, 'button_javascript' ) );
			add_action( 'admin_footer', array( $this, 'load_resources' ) );
		}
		if (! (Config::$accessKeyId && Config::$accessKeySecret || (isset($_GET['page']) && $_GET['page'] == 'aliyun-cdn-helper'))) {
			add_action('admin_notices', array($this, 'warning'));
		}
	}

	/* 后台状态栏添加按钮 */
	public static function refresh_button() {
		global $wp_admin_bar;
		$wp_admin_bar->add_menu( array(
				'id'    => 'alicdn_shortcut',
				'title' => '刷新 CDN',
				'href'  => '#',
				'meta'  => array( 'onclick' => 'refresh_alicdn()' )
			)
		);
	}

	/* Ajax 请求 */
	public static function button_javascript() {
		echo "<script type=\"text/javascript\">
                function refresh_alicdn() {
                    jQuery.ajax({
                        type: 'POST',
                        url: \"" . admin_url( 'admin-ajax.php' ) . "\",
                        data: {
                            action: 'aliyun_cdn_helper',
                            module: 1
                        },
                       	beforeSend: function() {
                       	  	toastr.info( '刷新任务提交中，请稍后...' );
                       	  	jQuery('alicdn_shortcut').attr({ disabled: \"disabled\" });
                       	},
                        success: function(data) {
                        	toastr.clear();
                        	jQuery('alicdn_shortcut').removeAttr(\"disabled\");
                        	if (data == 0) {
                        		toastr.error('失败：请检查 Access Key ID 和 Access key Secret 是否输入正确。');
                        	}
                            switch( data.status ) {
                                case 1:
                                    toastr.success( data.message );
                                    break;
                                case 2:
                                    toastr.error( data.message );
                                    break;
                                case 3:
                                    toastr.warning( data.message );
                                    break;
                            }
                        }
                    });
                }
              </script>";
	}

	/* 加载提示框 css & js */
	public static function load_resources() {
		wp_register_style( 'toastrCSS', plugins_url( '../resources/toastr.min.css', __FILE__ ) );
		wp_register_script( 'toastrJS', plugins_url( '../resources/toastr.min.js', __FILE__ ) );
		wp_enqueue_style( 'toastrCSS' );
		wp_enqueue_script( 'toastrJS' );
	}

	public function warning() {
		echo "<div id='alicdn-warning' class='updated fade'><p>Aliyun CDN Helper 启动成功，您需要 <a href='".Config::$settings_url."'>配置</a> 来让他工作</p></div>";
	}
}