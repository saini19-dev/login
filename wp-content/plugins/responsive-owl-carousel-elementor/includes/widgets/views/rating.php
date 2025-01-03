<?php
/**
 * @var array  $settings
 * @var array  $item
 * @var string $field_prefix
 */

defined( 'ABSPATH' ) || exit;

$is_hidden = $settings[ $field_prefix . 'rating_icon_hide' ] ?? false;

if ( $is_hidden ) {
	return;
}

$icon      = $settings[ $field_prefix . 'rating_icon' ] ?? array( 'library' => 'solid', 'value' => 'fas fa-star' );
$icon_size = $item['item_rating']['size'] ?? 5;
?>
<div class="owl-rating-icon">
    <?php echo owce_get_rendered_icons( $icon, $icon_size ); ?>
</div>
