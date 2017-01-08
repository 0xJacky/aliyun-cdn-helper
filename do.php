<?php
/*
This is a part of Refresh AliCDN
*/

/*  Copyright 2017  0xJacky  (email : me@jackyu.cm)

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

header('content-type:application/json');
define('BASE_PATH',str_replace( '\\' , '/' , realpath(dirname(__FILE__).'/../../../')));//获取站点根目录
require(BASE_PATH.'/wp-load.php' ); //载入 WordPress 框架函数
if( $_POST['pw']) { //判断是否为带有密钥的请求
  if( $_POST['pw'] == get_option('cdn_post_pw') ) { //判断请求密钥是否正确（防止恶意刷新）
    if (! empty(get_option('cdn_access_key_id')) && ! empty(get_option('cdn_access_key_secret')) ) {

      /* 载入 Aliyun CDN SDK */
      include_once 'aliyun-php-sdk-core/Config.php';
      include_once 'aliyun-php-sdk-cdn/Request/v20141111/RefreshObjectCachesRequest.php';

      //getProfile的三个参数分别是：region, Access Key ID, Access Key Secret
      $iClientProfile = DefaultProfile::getProfile("cn-hangzhou", get_option('cdn_access_key_id'), get_option('cdn_access_key_secret'));
      $client = new DefaultAcsClient($iClientProfile);
      $request = new \Cdn\Request\V20141111\RefreshObjectCachesRequest();
      function refresh($url) {
        global $client;
        global $request;
        if ( is_array($url) ){
          $urls_num = count($url);
          for ($i=0;$i<$urls_num;$i++) {
            $request->setObjectPath($url[$i]);
            $response = $client->doAction($request);
            $result = $response->getBody();
            $oResult = json_decode($result,1);
            if ($oResult && $oResult["RefreshTaskId"]) {
              $status = 1;
              continue;
            } else {
              $status = 0;
              break;
            }
          }
        } else {
          $request->setObjectPath($url);
          $response = $client->doAction($request);
          $result = $response->getBody();
          $oResult = json_decode($result,1);
          if ($oResult && $oResult["RefreshTaskId"]) {
            $status = 1;
          } else {
            $status = 0;
          }
        }
        if ( $status == 1 ) {
          $ret = array();
          $ret['result'] = 1;
          $ret['message'] = "缓存刷新成功";
          echo json_encode($ret);
        } elseif ( $status == 0 ) {
          $ret = array();
          $ret['result'] = 2;
          $ret['message'] = "缓存刷新失败，这通常是由于您的配置错误所引起的，请检查插件配置";
          echo json_encode($ret);
        }
      }

      $type = is_numeric(get_option('cdn_refresh_type')) ? esc_attr(get_option('cdn_refresh_type')) : 1;
      if ( $type == 1 ) {
        $url = get_stylesheet_directory_uri()."/style.css";
        echo refresh($url);
      } elseif ( $type == 2 ) {
        $url = explode("\n", get_option('cdn_refresh_urls'));
        echo refresh($url);
      } elseif ( $type == 3 ) {
        $style_url = array(get_stylesheet_directory_uri()."/style.css");
        $custom_url = explode("\n", get_option('cdn_refresh_urls'));
        $url = array_merge($custom_url, $style_url);
        echo refresh($url);
      } elseif ( $type == 4 ) {
        function getFileType($file) {
          //获取文件拓展名
          return substr($file, strrpos($file, '.') + 1);
        }
        function searchDir($path,&$data){
          //获取文件名
          if(is_dir($path)){
            $dp=dir($path);
            while($file=$dp->read()){
              if($file!='.'&& $file!='..'){
                searchDir($path.'/'.$file, $data);
              }
            }
            $dp->close();
          }
          if(is_file($path)){
            if (getFileType($path) !== "php") { //不刷新 PHP 文件
              $path = str_replace(ABSPATH, site_url()."/", $path); //将绝对路径替换为 URL 形式
              $data[]=$path;
            }
          }
        }
        function getDir($dir){
          $data=array();
          searchDir($dir,$data);
          return   $data;
        }
        $urls = getDir(get_stylesheet_directory());
        $custom_url = explode("\n", get_option('cdn_refresh_urls'));
        $url = array_merge($urls, $custom_url);
        echo refresh($url);
      }
    } else {
      $ret = array();
      $ret['result'] = 3;
      $ret['message'] = "您尚未配置 Access Key ID 或 Access Key Secret";
      echo json_encode($ret);
    }
  } else {
    $ret = array();
    $ret['result'] = 2;
    $ret['message'] = "密钥错误，无法刷新缓存，请重新安装插件";
    echo json_encode($ret);
  }
} else {
  $ret = array();
  $ret['result'] = 3;
  $ret['message'] = "400 Bad Request";
  echo json_encode($ret);
}
?>
