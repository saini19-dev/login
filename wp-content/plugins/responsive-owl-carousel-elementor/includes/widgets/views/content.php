<?php
/**
 * @var array  $settings
 * @var array  $item
 * @var string $field_prefix
 */

defined( 'ABSPATH' ) || exit;

$is_hidden = $settings[ $field_prefix . 'content_hide' ] ?? false;

if ( $is_hidden ) {
	return;
}

$html_tag = $settings[ $field_prefix . 'content_tag' ] ?? 'p';
$text     = $item['item_content'] ?? '';
$attrs    = array(
	'class'        => 'owl-content',
	'data-setting' => 'item_content',
);

echo wp_kses_post( owce_get_text_with_tag( $this, $html_tag, $text, $attrs ) );
