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

class Settings {

	private $client;

	function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

	}

	public function add_admin_menu() {
		add_options_page( __( 'Alibaba Cloud CDN', 'aliyun-cdn' ),
			__( 'Alibaba Cloud CDN', Config::identifier ), 'administrator',
			'aliyun-cdn-helper',
			array( $this, 'settings_page' ) );
	}

	public function settings_page() {
		if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
			$this->handle_submit();
		}

		require ALIYUN_CDN_PATH . '/view/settings.php';
	}

	private function handle_submit() {
		try {

			isset( $_POST['access_key_id'] ) && Config::$options['ak'] = trim( $_POST['access_key_id'] );
			empty( $_POST['access_key_secret'] ) || Config::$options['sk'] = trim( $_POST['access_key_secret'] );
			isset( $_POST['refresh_type'] ) && Config::$options['refresh_type'] = (int) $_POST['refresh_type'];
			isset( $_POST['custom_urls'] ) && Config::$options['custom_urls'] = trim( $_POST['custom_urls'] );

			update_option( Config::option_name, Config::$options );
			Config::update();
			$msg = '<div class="updated"><p><strong>' . __( 'Configuration saved successfully', Config::identifier ) . '</strong></p></div>';
		} catch ( \Exception $e ) {
			$msg = '<div class="error"><p><strong>' . sprintf( __( 'Fail to save configuration, error message: %s', Config::identifier ), $e->getMessage() ) . '</strong></p></div>';
		}

		echo $msg;
	}
}