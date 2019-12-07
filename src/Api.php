<?php
/**
 * Aliyun CDN Helper
 * Copyright 2017 - 2020 0xJacky (email : jacky-943572677@qq.com)
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

use WP_REST_Server;
use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;

class Api {
	const REST_NAMESPACE = 'aliyun-cdn-helper/v1';
	const routes = [
		'refresh' => [
			'methods'  => WP_REST_Server::CREATABLE,
			'callback' => 'do_refresh'
		],
		'quota'   => [
			'methods'  => WP_REST_Server::READABLE,
			'callback' => 'get_quota'
		]
	];

	function __construct() {
		add_filter( 'rest_api_init', [ $this, 'init' ] );
	}

	public function init() {
		foreach ( self::routes as $url => $route ) {
			register_rest_route( self::REST_NAMESPACE, $url, [
				'methods'             => $route['methods'],
				'permission_callback' => [ $this, 'privileged_permission_callback' ],
				'callback'            => [ $this, $route['callback'] ]
			] );
		}
	}

	public static function privileged_permission_callback() {
		return current_user_can( 'level_10' );
	}

	public static function build_request( $action, $query = [] ) {
		AlibabaCloud::accessKeyClient( Config::$accessKeyId, Config::$accessKeySecret )
		            ->regionId( 'cn-hangzhou' )
		            ->asDefaultClient();

		$query = array_merge( [ 'RegionId' => "cn-hangzhou" ], $query );

		return AlibabaCloud::rpc()
		                   ->product( 'Cdn' )
		                   ->scheme( 'https' ) // https | http
		                   ->version( '2018-05-10' )
		                   ->action( $action )
		                   ->method( 'POST' )
		                   ->host( 'cdn.aliyuncs.com' )
		                   ->options( [
			                   'query' => $query,
		                   ] )
		                   ->request()->toArray();
	}

	public static function do_refresh() {
		if ( ! Config::is_configured() ) {
			return [
				'status'  => 406,
				'message' => __( 'Please check whether the Access Key ID and Access key Secret are entered correctly.', Config::identifier )
			];
		}
		$type      = $_REQUEST['type'] ?? Config::$refresh_type ?? 1;
		$theme_url = get_stylesheet_directory_uri();
		$obj_type  = 'Directory';
		switch ( $type ) {
			default:
			case 1:
				$url      = $theme_url . '/style.css';
				$obj_type = 'File';
				$msg      = __( 'Refresh the style.css and custom urls success!', Config::identifier );
				break;
			case 2:
				$url = $theme_url . '/';
				$msg = __( 'Refresh the theme url and custom urls success!', Config::identifier );
				break;
			case 3:
				$url = site_url() . '/';
				$msg = __( 'Refresh the site url and custom urls success!', Config::identifier );
				break;
			case 5:
				$url = Config::$custom_urls;
				$msg = __( 'Refresh custom urls success!', Config::identifier );
				break;
		}
		// test
		$url = str_replace( "blog.app", "jackyu.cn", $url );
		try {
			$result = self::build_request(
				'RefreshObjectCaches',
				[
					'ObjectPath' => $url,
					'ObjectType' => $obj_type
				] );

			return [
				'status'  => 200,
				'message' => $msg
			];
		} catch ( ClientException $e ) {
			return [
				'status'  => 500,
				'message' => sprintf( __( 'Fail to refresh, error message: %s', Config::identifier ), $e->getErrorMessage() )
			];
		} catch ( ServerException $e ) {
			return [
				'status'  => 500,
				'message' => sprintf( __( 'Fail to refresh, error message: %s', Config::identifier ), $e->getErrorMessage() )
			];
		}

	}

	public static function get_quota() {
		try {
			$result = self::build_request( 'DescribeRefreshQuota' );

			$data = [
				'status'  => 200,
				'message' => sprintf( __( 'You can refresh %s files and %s directories today.', Config::identifier ),
					$result['UrlRemain'], $result['DirRemain'] )
			];
		} catch ( ClientException $e ) {
			return [
				'status'  => 500,
				'message' => sprintf( __( 'Fail to get quota, error message: %s', Config::identifier ), $e->getErrorMessage() )
			];
		} catch ( ServerException $e ) {
			return [
				'status'  => 500,
				'message' => sprintf( __( 'Fail to get quota, error message: %s', Config::identifier ), $e->getErrorMessage() )
			];
		}

		return $data;
	}
}