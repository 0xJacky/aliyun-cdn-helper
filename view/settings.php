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
defined( 'ALIYUN_CDN_PATH' ) OR exit();
$options = array_merge( CDN\WP\Config::$originOptions, get_option( 'alicdn_options', array() ) );
$type    = is_numeric( $options['refresh_type'] ) ? esc_attr( $options['refresh_type'] ) : 1;
$sk      = $options['sk'] ? '我藏起来了:-D' : '';
?>
<div class="wrap" style="margin: 10px">
    <h1>阿里云 CDN</h1>
    <form method="POST" action="<?php echo wp_nonce_url( CDN\WP\Config::$settings_url ); ?>">
        <hr>
        <fieldset>
            <h2>Access Key ID</h2>
            <input type="text" name="access_key_id" value="<?php echo $options['ak'] ?>"/>
            <p>请在此输入您在阿里云管理控制台中获取到的 Access Key ID</p>
        </fieldset>
        <fieldset>
            <h2>Access key Secret</h2>
            <input type="text" name="access_key_secret" value="" placeholder="<?php echo $sk; ?>"/>
            <p>请在此输入您在阿里云管理控制台中获取到的 Access Key Secret</p>
        </fieldset>
        <fieldset>
            <h2>刷新文件类型</h2>
            <ol><input type="radio" name="refresh_type" value="1" <?php echo checked( 1, $type, false ); ?>/>仅刷新当前主题的
                style.css
            </ol>
            <ol><input type="radio" name="refresh_type" value="2" <?php echo checked( 2, $type, false ); ?>/>刷新当前主题目录内的静态文件（目录）
            </ol>
            <ol><input type="radio" name="refresh_type" value="3" <?php echo checked( 3, $type, false ); ?>/>全站刷新（目录）
            </ol>
        </fieldset>
        <fieldset>
            <h2>URL 预热</h2>
            <p>将源站的内容主动预热到 L2 Cache 节点上，用户首次访问可直接命中缓存，缓解源站压力。</p>
            <p>如果您是首次使用 CDN 和这个插件，您可以点击下面的预热按钮，插件将会自动搜索当前主题目录类的静态资源文件，
                并提交到预热接口。</p>
            <a class="button" onclick="task(2)">预热</a>
        </fieldset>
        <fieldset>
            <h2>刷新自定义 URL</h2>
            <textarea type="text" cols="60" rows="10"
                      name="custom_urls"><?php echo $options['custom_urls']; ?></textarea>
            <p>多个 URL 请用回车分隔，每个 URL 应当以 <code>http://</code> 或 <code>https://</code>
                开头，一次提交不能超过100个URL，如果输入的是目录，请不要忘记在结尾添加 <code>/</code></p>
            <a class="button" onclick="task(3)">刷新</a>
			<?php if ( $options['ak'] && $options['sk'] ) { ?>
                <p id="quota"></p>
			<?php } ?>
        </fieldset>
        <hr>
        <button class="button button-primary" type="submit">保存设置</button>
    </form>
</div>
<script type="text/javascript">
    function task(module) {
        jQuery.ajax({
            type: 'POST',
            url: " <?php echo admin_url( 'admin-ajax.php' ) ?> ",
            data: {
                action: 'aliyun_cdn_helper',
                module: parseInt(module)
            },
            beforeSend: function () {
                toastr.info('任务提交中，请稍后...');
                jQuery(this).attr({disabled: "disabled"});
            },
            success: function (data) {
                toastr.clear();
                jQuery(this).removeAttr("disabled");
                switch (data.status) {
                    case 1:
                        toastr.success(data.message);
                        break;
                    case 2:
                        toastr.error(data.message);
                        break;
                    case 3:
                        toastr.warning(data.message);
                        break;
                }
            }
        });
    }

    jQuery(function () {
        if (jQuery('#quota')) {
            jQuery.ajax({
                type: 'POST',
                url: " <?php echo admin_url( 'admin-ajax.php' ) ?> ",
                data: {
                    action: 'aliyun_cdn_helper',
                    module: 5
                },
                beforeSend: function () {
                    jQuery('#quota').html("正在查询预热刷新操作余量...");
                },
                success: function (data) {
                    if (data.status == 1) {
                        $msg = "注意：您的账户每天最多可以刷新（含预热）" + data.message['UrlQuota'] + " 个文件(URL)和 " + data.message['DirQuota'] + " 个目录。刷新任务生效时间大约为5分钟。";
                        $msg += "<br />今日还可以刷新目录 " + data.message['DirRemain'] + " 次，刷新URL " + data.message['UrlRemain'] + " 个。";
                    } else {
                        $msg = "查询失败，错误原因" + data.message;
                    }
                    jQuery('#quota').html($msg);
                }
            });
        }
    });
</script>