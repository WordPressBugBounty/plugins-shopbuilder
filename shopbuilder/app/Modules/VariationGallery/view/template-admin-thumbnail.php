<?php
/**
 * Admin Thumbnail js template
 *
 * This template can be overridden by copying it to
 * yourtheme/woo-product-variation-gallery-pro/template-admin-thumbnail.php
 */

defined( 'ABSPATH' ) || exit;
?>
<script type="text/html" id="tmpl-rtsb-vg-image">
	<# hasVideo = ( typeof data.rtsb_vg_video_link !== 'undefined' && data.rtsb_vg_video_link != ''  ) ? 'video' : '';  #>
	<li class="image {{hasVideo}}">
		<input type="hidden" name="rtsb_vg[{{data.product_variation_id}}][]" value="{{data.id}}">
		<img src="{{data.url}}">
		<div class="rtsb-vg-action-button">
			<span data-tip="Add Video" class="rtsb-vg-media-video-popup dashicons dashicons-video-alt3"></span>
			<span data-tip="Edit Image" class="rtsb-vg-gallery-edit dashicons dashicons-edit"></span>
			<a href="#" class="delete rtsb-vg-remove-image"><span class="dashicons dashicons-no"></span></a>
		</div>
	</li>
</script>