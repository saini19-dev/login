<?php
/**
 * @var array  $settings
 * @var array  $item
 * @var string $field_prefix
 */

defined( 'ABSPATH' ) || exit;

$is_hidden = $settings[ $field_prefix . 'image_hide' ] ?? false;

if ( $is_hidden ) {
	return;
}

$img_size          = ( $field_prefix . 'thumbnail' ) ?? 'owl_elementor_thumbnail';
$light_box_options = array(
	'show_lightbox'                => $settings[ $field_prefix . 'lightbox' ] ?? '',
	'show_lightbox_title'          => $settings[ $field_prefix . 'lightbox_title' ] ?? 'yes',
	'show_lightbox_description'    => $settings[ $field_prefix . 'lightbox_description' ] ?? '',
	'disable_lightbox_editor_mode' => $settings[ $field_prefix . 'lightbox_editor_mode' ] ?? 'yes',
);

// Add option(image) key in the settings array as owce_get_img_with_size function will look for that key in it.
$settings['item_image_temp'] = $item['item_image'];
?>
<div class="owl-thumb">
    <?php echo wp_kses_post( owce_get_img_with_size( $settings, $img_size, 'item_image_temp', $this, $light_box_options ) ); ?>
</div>
