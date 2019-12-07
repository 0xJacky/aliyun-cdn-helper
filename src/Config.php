<?php
/**
 * Jacky AliCDN Helper
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

class Config {
	/* 配置 */
	public static $accessKeyId = '';
	public static $accessKeySecret = '';

	public static $originOptions = array(
		'ak'           => '',
		'sk'           => '',
		'refresh_type' => 1,
		'custom_urls'  => ''
	);

	public static $options = '';
	public static $plugin_path = 'aliyun-cdn';
	public static $settings_url = "options-general.php?page=jacky-alicdn-helper";

	public static function init( $plugin_path = "" ) {
		$plugin_path && self::$plugin_path = plugin_basename( $plugin_path );
		self::$options         = array_merge( self::$originOptions, get_option( 'alicdn_options', array() ) );
		self::$accessKeyId     = self::$options['ak'];
		self::$accessKeySecret = self::$options['sk'];
	}

}
