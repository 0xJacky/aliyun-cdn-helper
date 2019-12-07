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

use CDN\WP\Config;

$_d       = Config::identifier;
$api_root = site_url() . '/wp-json/aliyun-cdn-helper/v1';
$nonce    = wp_create_nonce( 'wp_rest' );
?>
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
</script>
