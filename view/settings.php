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
$d       = 'aliyun-cdn';
$sk      = $options['sk'] ? __( 'You can\'t see me', $d ) : '';
?>
<div class="wrap" style="margin: 10px">
    <h1><?php _e( 'Alibaba Cloud CDN', $d ); ?></h1>
    <form method="POST" action="<?php echo wp_nonce_url( CDN\WP\Config::$settings_url ); ?>">
        <hr>
        <fieldset>
            <h2><?php _e( 'Access Key ID', $d ); ?></h2>
            <input type="text" name="access_key_id" value="<?php echo $options['ak'] ?>"/>
            <h2><?php _e( 'Access key Secret', $d ); ?></h2>
            <input type="text" name="access_key_secret" value="" placeholder="<?php echo $sk; ?>:-D"/>
            <p><?php _e( 'Please enter the Access Key ID and the Access Key Secret, you obtained in the Alibaba Cloud Management Console.', $d ); ?></p>
            <p><?php _e( 'Make sure that your Access Key ID and Access Key Secret are entered correctly and that CDN acceleration has been turned on for the current domain name.', $d ); ?></p>
            <p><?php printf( __( 'If you don\'t know how to use Alibaba Cloud CDN please visit: <a href="%s">https://cdn.aliyun.com</a>', $d ), 'https://cdn.aliyun.com' ) ?></p>
            <p><?php _e( 'Document: <a href="https://www.alibabacloud.com/help/doc-detail/27200.htm">https://www.alibabacloud.com/help/doc-detail/27200.htm</a>', $d ); ?></p>
        </fieldset>
        <fieldset>
            <h2><?php _e( 'Refresh file type', $d ); ?></h2>
            <ol><input type="radio" name="refresh_type"
                       value="1" <?php echo checked( 1, $type, false ); ?>/><?php _e( 'style.css only (Request object type: File)', $d ); ?>
            </ol>
            <ol><input type="radio" name="refresh_type"
                       value="2" <?php echo checked( 2, $type, false ); ?>/><?php _e( 'Refresh static files in the current theme directory (Request object type: Directory)', $d ); ?>
            </ol>
            <ol><input type="radio" name="refresh_type"
                       value="3" <?php echo checked( 3, $type, false ); ?>/><?php _e( 'The whole site (Request object type: Directory)', $d ); ?>
            </ol>
        </fieldset>
        <fieldset>
            <h2><?php _e( 'Push object cache', $d ); ?></h2>
            <p><?php _e( 'Takes content from the origin site and actively preprocess it to the L2 Cache node. Upon first access, users can directly cache hit to relieve pressure on the origin site.', $d ); ?></p>
            <p><?php _e( 'If you are using Alibaba Cloud CDN and this plugin for the first time, you can click on the following button, 
            the plugin will automatically search the current theme directory of the static resource file, and submitted the URLs to the interface.', $d ); ?></p>
            <a class="button" onclick="task(2)"><?php _e( 'Push', $d ) ?></a>
        </fieldset>
        <fieldset>
            <h2><?php _e( 'Custom URL', $d ); ?></h2>
            <textarea type="text" cols="60" rows="10"
                      name="custom_urls" id="c_url"><?php echo $options['custom_urls']; ?></textarea>
            <p><?php _e( 'Multiple URLs should use a carriage return to separate, if you enter the directory, please do not forget to add <code>/</code>', $d ); ?></p>
            <a class="button" onclick="custom_url()"><?php _e( 'Refresh', $d ); ?></a>
			<?php if ( $options['ak'] && $options['sk'] ) { ?>
                <p id="quota"></p>
			<?php } ?>
        </fieldset>
        <hr>
        <button class="button button-primary" type="submit"><?php _e( 'Save', $d ); ?></button>
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
                toastr.info('<?php _e( 'Task is being submitted, please wait ...', $d ); ?>');
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

    function custom_url() {
        jQuery.ajax({
            type: 'POST',
            url: "",
            data: {
                custom_urls: jQuery("#c_url").val()
            },
            beforeSend: function () {
                toastr.info('<?php _e( 'Task is being submitted, please wait ...', $d ); ?>');
                jQuery(this).attr({disabled: "disabled"});
            },
            success: function () {
                task(3)
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
                    jQuery('#quota').html('<?php _e( 'Querying operation quota...', $d ); ?>');
                },
                success: function (data) {
                    jQuery('#quota').html(data.message);
                }
            });
        }
    });
</script>