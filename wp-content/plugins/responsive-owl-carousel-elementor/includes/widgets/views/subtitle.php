<?php
/**
 * @var array  $settings
 * @var array  $item
 * @var string $field_prefix
 */

defined( 'ABSPATH' ) || exit;

$is_hidden = $settings[ $field_prefix . 'subtitle_hide' ] ?? false;

if ( $is_hidden ) {
	return;
}

$html_tag = $settings[ $field_prefix . 'subtitle_tag' ] ?? 'h5';
$text     = $item['item_subtitle'] ?? '';
$attrs    = array( 'class' => 'owl-subtitle', 'data-setting' => 'item_subtitle' );

echo wp_kses_post( owce_get_text_with_tag( $this, $html_tag, $text, $attrs ) );
