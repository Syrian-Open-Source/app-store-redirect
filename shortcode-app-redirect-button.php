<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly 
?>
<!-- App Store button -->
<a href="<?= site_url() . get_option($data['app_link_setting']) ?>" target="_blank" class="as-btn <?= $data['button_class'] ?>" role="button">
    <span class="as-button-subtitle"><?= $data['button_sub_title'] ?></span>
    <span class="as-button-title"><?= $data['button_title'] ?></span>
</a>