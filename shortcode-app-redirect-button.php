<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly 
?>

<!-- App Store button -->
<a href="<?php echo esc_url(site_url() . get_option($data['app_link_setting'])); ?>" target="_blank" class="as-btn <?php echo esc_attr($data['button_class']); ?>" role="button">
    <span class="as-button-subtitle"><?php echo esc_html($data['button_sub_title']); ?></span>
    <span class="as-button-title"><?php echo esc_html($data['button_title']); ?></span>
</a>