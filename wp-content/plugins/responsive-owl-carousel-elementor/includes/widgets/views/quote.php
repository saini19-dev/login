<?php
/**
 * @var array  $settings
 * @var array  $item
 * @var string $field_prefix
 */

defined( 'ABSPATH' ) || exit;

$is_hidden = $settings[ $field_prefix . 'quote_icon_hide' ] ?? true;

if ( $is_hidden ) {
	return;
}

$icon = $settings[ $field_prefix . 'quote_icon' ] ?? array( 'library' => 'solid', 'value' => 'fa fa-quote-left' );
?>
<div class="owl-quote-icon">
    <?php echo owce_get_rendered_icons( $icon ); ?>
</div>