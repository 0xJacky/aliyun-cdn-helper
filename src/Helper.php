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

class Helper {
	/* 获取文件扩展名 */
	private static function get_extension( $file ) {
		return substr( $file, strrpos( $file, '.' ) + 1 );
	}

	/* 获取文件列表 */
	private static function search_dir( $path, &$list ) {
		if ( is_dir( $path ) ) {
			$dp = dir( $path );
			while ( $file = $dp->read() ) {
				if ( $file != '.' && $file != '..' ) {
					self::search_dir( $path . '/' . $file, $list );
				}
			}
			$dp->close();
		}
		if ( is_file( $path ) ) {
			$ext = self::get_extension( $path );
			$include = array('css', 'js');
			if ( in_array( $ext, $include ) ) {
				/* 将绝对路径替换为 URL 形式 */
				$path   = str_replace( ABSPATH, site_url() . '/', $path );
				$list[] = $path;
			}
		}
	}

	public static function get_file_list( $dir ) {
		$list = array();
		self::search_dir( $dir, $list );

		return $list;
	}

}
