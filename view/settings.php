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
defined( 'ALIYUN_CDN_PATH' ) OR exit();

use CDN\WP\Config;

$type     = is_numeric( Config::$refresh_type ) ? Config::$refresh_type : 1;
$_d       = Config::identifier;
$nonce    = wp_create_nonce( 'wp_rest' );
$api_root = site_url() . '/wp-json/aliyun-cdn-helper/v1';
?>
<div class="wrap" style="margin: 10px">
    <h1><?php _e( 'Alibaba Cloud CDN Helper', $_d ); ?></h1>
    <form method="POST" action="<?php echo wp_nonce_url( Config::$settings_url ); ?>">
        <hr>
        <fieldset>
            <h2><?php _e( 'Access Key ID', $_d ); ?></h2>
            <input type="text" name="access_key_id" value="<?php echo Config::$accessKeyId ?>"/>
            <h2><?php _e( 'Access key Secret', $_d ); ?></h2>
            <input type="text" name="access_key_secret" value=""
                   placeholder="<?php Config::$accessKeySecret ? _e( 'You cannot see me:-D', $_d ) : ''; ?>"/>
            <p><?php _e( 'Please enter your Access Key ID and Access Key Secret, which can be obtained in the Alibaba Cloud Management Console.', $_d ); ?></p>
            <p><?php _e( 'Make sure that the Access Key ID and the Access Key Secret you entered are correct and the CDN acceleration has been turned on for the current domain name.', $_d ); ?></p>
            <p><?php _e( 'Last but not least, we suggest that you should add <code>ver</code> to the <code>Ignore Parameters</code>, which will increase the cache hit ratio.', $_d ) ?></p>
        </fieldset>
        <fieldset>
            <h2><?php _e( 'Refresh file type', $_d ); ?></h2>
            <ol><input type="radio" name="refresh_type"
                       value="1" <?php echo checked( 1, $type, false ); ?>/><?php _e( 'style.css only (Request object type: File)', $_d ); ?>
            </ol>
            <ol><input type="radio" name="refresh_type"
                       value="2" <?php echo checked( 2, $type, false ); ?>/><?php _e( 'Static files in the current theme directory (Request object type: Directory)', $_d ); ?>
            </ol>
            <ol><input type="radio" name="refresh_type"
                       value="3" <?php echo checked( 3, $type, false ); ?>/><?php _e( 'The whole site (Request object type: Directory)', $_d ); ?>
            </ol>
        </fieldset>
        <fieldset>
            <h2><?php _e( 'Custom URLs', $_d ); ?></h2>
            <textarea type="text" cols="60" rows="10"
                      name="custom_urls" id="c_url"><?php echo Config::$custom_urls ?></textarea>
            <p><?php _e( 'Multiple URLs should use a carriage return to separate.', $_d ); ?></p>
            <p><?php _e( 'If you enter a directory url, do not forget to add <code>/</code> at the end.', $_d ); ?></p>
			<?php if ( Config::$accessKeyId && Config::$accessKeySecret ) { ?>
                <p id="quota"></p>
			<?php } ?>
        </fieldset>
        <hr>
        <button class="button button-primary" type="submit"><?php _e( 'Save', $_d ); ?></button>
        <a class="button" onclick="do_refresh()"><?php _e( 'Refresh CDN', $_d ) ?></a>
        <a class="button" onclick="custom_url()"><?php _e( 'Refresh Custom urls', $_d ); ?></a>
        <p><?php _e( 'After changed the settings, make sure you save the settings before you click the refresh button.', $_d ); ?></p>
    </form>
</div>
<script type="text/javascript">
    function do_refresh() {
        jQuery.ajax({
            type: 'POST',
            url: "<?php echo $api_root . '/refresh'; ?>",
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', '<?php echo $nonce; ?>');
                toastr.info('<?php _e( 'Submitting task, please wait ...', $_d ); ?>');
                jQuery(this).attr({disabled: "disabled"});
            },
            success: function (data) {
                toastr.clear();
                jQuery(this).removeAttr("disabled");
                switch (data.status) {
                    case 200:
                        get_quota()
                        toastr.success(data.message);
                        break;
                    case 500:
                        toastr.error(data.message);
                        break;
                    case 406:
                        toastr.warning(data.message);
                        break;
                }
            }
        });
    }

    function custom_url() {
        jQuery.ajax({
            type: 'POST',
            url: " <?php echo $api_root . '/refresh?type=5' ?> ",
            dataType: 'json',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', '<?php echo $nonce; ?>');
                toastr.info('<?php _e( 'Submitting task, please wait ...', $_d ); ?>');
                jQuery(this).attr({disabled: "disabled"});
            },
            success: function (data) {
                toastr.clear();
                jQuery(this).removeAttr("disabled");
                switch (data.status) {
                    case 200:
                        get_quota()
                        toastr.success(data.message);
                        break;
                    case 500:
                        toastr.error(data.message);
                        break;
                    case 406:
                        toastr.warning(data.message);
                        break;
                }
            }
        });
    }

    function get_quota() {
        jQuery.ajax({
            type: 'GET',
            dataType: 'json',
            url: " <?php echo $api_root . '/quota' ?> ",
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', '<?php echo $nonce; ?>');
                jQuery('#quota').html('<?php _e( 'Querying operation quota...', $_d ); ?>');
            },
            success: function (data) {
                jQuery('#quota').html(data.message);
            }
        });
    }

    jQuery(function () {
        if (jQuery('#quota')) {
            get_quota()
        }
    });
</script>
