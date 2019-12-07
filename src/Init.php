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
		add_action( 'admin_bar_menu', array( $this, 'refresh_button' ), 90 );
		add_action( 'admin_footer', array( $this, 'button_javascript' ) );
		add_action( 'admin_footer', array( $this, 'load_resources' ) );
	}

	/* 后台状态栏添加按钮 */
	public static function refresh_button() {
		global $wp_admin_bar;
		$wp_admin_bar->add_menu( array(
				'id'    => 'aliyun_cdn_shortcut',
				'title' => __( 'Refresh CDN', Config::identifier ),
				'href'  => '#',
				'meta'  => [ 'onclick' => 'do_refresh()' ]
			)
		);
	}

	/* Ajax 请求 */
	public static function button_javascript() {
		require ALIYUN_CDN_PATH . '/view/button_javascript.php';
	}

	/* 加载提示框 css & js */
	public static function load_resources() {
		wp_register_style( 'toastrCSS', plugins_url( '../assets/toastr.min.css', __FILE__ ) );
		wp_register_script( 'toastrJS', plugins_url( '../assets/toastr.min.js', __FILE__ ) );
		wp_enqueue_style( 'toastrCSS' );
		wp_enqueue_script( 'toastrJS' );
	}

	public function warning() {
		$html = "<div id='alicdn-warning' class='updated fade'><p>" .
		        __( 'Aliyun CDN Helper launch success, you need to <a href="%s">configure</a> it to work.', 'aliyun-cdn' ) .
		        "</p></div>";

		echo sprintf( $html, Config::$settings_url );
	}

}
