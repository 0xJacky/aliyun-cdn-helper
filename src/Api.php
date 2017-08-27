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
		$style       = get_stylesheet_directory_uri() . "/style.css";
		$stylesheet  = str_replace( ABSPATH, site_url() . '/', get_stylesheet_directory() );
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
			$this->response( 2, "刷新失败！\n错误详情: " . $e->getErrorMessage() );
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
			$this->response( 2, "预热失败！\n错误详情: " . $e->getErrorMessage() );
		}
	}

	private function handle( $urls, $type, $object_type = '' ) {
		if ( self::$Quota->UrlRemain >= 0 ) {
			$requestId = '';
			$desc      = '';

			if ( $type == 'refresh' ) {
				$requestId = 'RefreshTaskId';
				$desc      = '缓存刷新';
			} else {
				$requestId = 'RequestId';
				$desc      = '预热';
			}

			if ( is_array( $urls ) ) {
				foreach ( $urls as $url ) {
					self::$$type->setObjectPath( $url );
					$result = self::$client->getAcsResponse( self::$$type );
					if ( $result->$requestId ) {
						$success = 1;
						continue;
					} else {
						$success = 0;
						break;
					}
				}
			} else {
				self::$$type->setObjectPath( $urls );
				$object_type && self::$$type->setObjectType( 'Directory' );
				$result  = self::$client->getAcsResponse( self::$$type );
				$success = $result->$requestId ? 1 : 0;
			}

			if ( $success ) {
				$this->response( 1, $desc . "成功!" );

			} else {
				$this->response( 2, $desc . "失败！\n错误详情: " . $result["Message"] );
			}
		} else {
			$this->response( 3, "今日刷新（预热）次数以达上限!" );
		}
	}

	public function get_quota() {
		try {
			$Quota = self::$Quota;
			$quota = array(
				'DirQuota'  => $Quota->DirQuota,
				'DirRemain' => $Quota->DirRemain,
				'UrlQuota'  => $Quota->UrlQuota,
				'UrlRemain' => $Quota->UrlRemain
			);
			$this->response( 1, $quota );
		} catch ( \ServerException $e ) {
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