<?php
/**
 * @var array  $settings
 * @var array  $item
 * @var string $field_prefix
 * @var string $social_icon_hover_animation_class
 */

defined( 'ABSPATH' ) || exit;

$is_hidden = $settings[ $field_prefix . 'social_icon_hide' ] ?? false;

if ( $is_hidden ) {
	return;
}

$_settings = $item ?? array();
$options = array( 'class' => $social_icon_hover_animation_class ?? '' );
?>
<div class="owl-social-icon">
    <?php echo owce_get_social_icons( $this, $_settings, $options ); ?>
</div>
