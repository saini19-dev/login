<?php
/**
 * @var array  $settings
 * @var array  $item
 * @var string $field_prefix
 */

defined( 'ABSPATH' ) || exit;

$is_hidden = $settings[ $field_prefix . 'title_hide' ] ?? false;

if ( $is_hidden ) {
	return;
}

$html_tag = $settings[ $field_prefix . 'title_tag' ] ?? 'h3';
$text     = $item['item_title'] ?? '';
$attrs    = array( 'class' => 'owl-title', 'data-setting' => 'item_title' );

echo wp_kses_post( owce_get_text_with_tag( $this, $html_tag, $text, $attrs ) );
