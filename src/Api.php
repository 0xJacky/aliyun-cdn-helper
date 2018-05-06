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

class Api {
	private static $option;
	public static $profile;
	public static $client;
	private static $refresh;
	private static $push;
	private static $Quota;

	function __construct() {
		self::init();
		add_action( 'wp_ajax_aliyun_cdn_helper', array( $this, 'aliyun_cdn_helper' ) );
	}

	private static function init() {
		self::$option  = Config::$options;
		self::$profile = \DefaultProfile::getProfile( 'cn-hangzhou', Config::$accessKeyId, Config::$accessKeySecret );
		self::$client  = new \DefaultAcsClient( self::$profile );
		self::$refresh = new \Cdn\Request\V20141111\RefreshObjectCachesRequest();
		self::$push    = new \Cdn\Request\V20141111\PushObjectCacheRequest();
		self::$Quota   = new \Cdn\Request\V20141111\DescribeRefreshQuotaRequest();
		self::$Quota->setMethod( "GET" );
		self::$Quota = self::$client->getAcsResponse( self::$Quota );
	}

	public function aliyun_cdn_helper() {
		$module      = isset( $_REQUEST['module'] ) ? $_REQUEST['module'] : '';
		$theme_url = get_stylesheet_directory_uri();
		$style       = $theme_url . "/style.css";
		$stylesheet  = $theme_url . "/";
		$object_type = '';
		switch ( $module ) {
			case 1:
				switch ( self::$option['refresh_type'] ) {
					default:
					case 1:
						// 当前主题的样式表
						$urls = $style;
						break;
					case 2:
						// 当前主题
						$urls        = $stylesheet;
						$object_type = 'Directory';
						break;
					case 3:
						// 全站
						$urls        = site_url() . '/';
						$object_type = 'Directory';
						break;
				}
				$custom_url = self::$option['custom_urls'];
				if ( $custom_url ) {
					$urls       = array( $urls );
					$custom_url = explode( "\n", $custom_url );
					$urls       = array_merge( $urls, $custom_url );
				}
				$this->do_refresh( $urls, $object_type );
				exit();
				break;
			case 2:
				$urls = Helper::get_file_list( get_stylesheet_directory() );
				$this->do_push( $urls );
				exit();
				break;
			case 3:
				$custom_url = self::$option['custom_urls'];
				$custom_url = explode( "\n", $custom_url );
				$this->do_refresh( $custom_url );
				exit();
				break;
			case 5:
				$this->get_quota();
				exit();
				break;
		}
	}

	/**
	 * 处理刷新
	 *
	 * @param $urls
	 */
	public function do_refresh( $urls, $object_type = '' ) {
		try {
			$this->handle( $urls, 'refresh', $object_type );
		} catch ( \ServerException $e ) {
			$this->response( 2, sprintf( __( "Fail to refresh!\nError message: %s", "aliyun-cdn" ), $e->getErrorMessage() ) );
		}
	}

	/**
	 * 处理预热
	 *
	 * @param $urls
	 */
	public function do_push( $urls ) {
		try {
			$this->handle( $urls, 'push' );
		} catch ( \ServerException $e ) {
			$this->response( 2, sprintf( __( "Fail to push!\nError message: %s", "aliyun-cdn" ), $e->getErrorMessage() ) );
		}
	}

	private function handle( $urls, $type, $object_type = '' ) {
		$requestId = '';
		$desc      = '';

		if ( $type == 'refresh' ) {
			$requestId = 'RefreshTaskId';
			$qa = 'UrlRemain';
			$desc      = __( 'Refresh', 'aliyun-cdn' );
		} else {
			$requestId = 'RequestId';
			$desc      = __( 'Push', 'aliyun-cdn' );
			$qa = 'PreloadRemain';
		}

		if ( self::$Quota->$qa > 0 ) {

			if ( is_array( $urls ) ) {
				foreach ( $urls as $url)
				{
					self::$$type->setObjectPath( $url );
					$result = self::$client->getAcsResponse( self::$$type );
					$success = $result->$requestId ? 1 : 0;
				}
			} else {
				self::$$type->setObjectPath( $urls );
				$object_type && self::$$type->setObjectType( 'Directory' );
				$result  = self::$client->getAcsResponse( self::$$type );
				$success = $result->$requestId ? 1 : 0;
			}

			if ( $success ) {
				$this->response( 1, $desc . __( ' success!', 'aliyun-cdn' ) );

			}
		} else {
			$this->response( 3, __( 'Today\'s refresh (push) has reached the maximum number of operations!', 'aliyun-cdn' ) );
		}
	}

	public function get_quota() {
		try {
			$Quota = self::$Quota;
			$html  = sprintf( __( 'Notice: You can submit a maximum daily refresh-type request amount of URL: %s, amount of directory: %s, push-type request amount of URL: %s. <br />Today you can refresh URL: %s times, directory: %s times.<br />Today you can push URL: %s times.', 'aliyun-cdn' ), $Quota->UrlQuota, $Quota->DirQuota, $Quota->PreloadQuota, $Quota->UrlRemain, $Quota->DirRemain, $Quota->PreloadRemain );
			$this->response( 1, $html );
		} catch ( \ServerException $e ) {
			$this->response( 2, $e->setErrorMessage() );
			$this->response( 2, $e->setErrorMessage() );
		}

	}

	/**
	 * 响应请求
	 *
	 * @param $status
	 * @param $message
	 */
	private function response( $status, $message ) {
		header( "Content-Type: application/json" );
		$array = array(
			'status'  => $status,
			'message' => $message
		);
		echo json_encode( $array );
	}

}
