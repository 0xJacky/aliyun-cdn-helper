<?php
/**
 * Plugin Name: Aliyun CDN Helper
 * Plugin URI: https://github.com/0xJacky/aliyun_cdn_helper
 * Description: 阿里云 CDN 辅助工具。Aliyun CDN auxiliary tool for wordpress.
 * Version: 2.1
 * Author: 0xJacky
 * Author URI: https://jackyu.cn/
 * License: GPL2
 */

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

define( 'ALIYUN_CDN_PATH', dirname( __FILE__ ) );
require( ALIYUN_CDN_PATH . '/autoload.php' );


Config::init( ALIYUN_CDN_PATH );
load_plugin_textdomain( Config::identifier, false, Config::$plugin_path . '/languages' );

new Api;

if ( is_admin() ) {
	new Init;
	new Settings;
}
