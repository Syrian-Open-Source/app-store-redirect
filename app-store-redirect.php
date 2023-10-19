<?php

/**
 * Plugin Name: App Store Redirect
 * Description: Redirects users to the appropriate app store based on their operating system.
 * Version: 1.1
 * Author: Somar Kesen
 * Author URI: https://github.com/somarkn99
 * Text Domain: app-store-redirect
 */


define('ANDROID_APP_URL_OPTION', 'android_app_url');
define('IOS_APP_URL_OPTION', 'ios_app_url');
define('CUSTOM_ROUTE_OPTION', 'custom_route');

// Load Translation Files
function app_store_redirect_load_textdomain()
{
    load_plugin_textdomain('app-store-redirect', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('init', 'app_store_redirect_load_textdomain');

// Add plugin settings in the admin dashboard
function app_store_redirect_admin_menu()
{
    add_menu_page(
        __('App Store Redirect Settings', 'app-store-redirect'),
        __('App Store Redirect', 'app-store-redirect'),
        'manage_options',
        'app-store-redirect-settings',
        'app_store_redirect_settings_page',
        'dashicons-smartphone'
    );
}
add_action('admin_menu', 'app_store_redirect_admin_menu');

// Render the settings page
function app_store_redirect_settings_page()
{
    if (isset($_POST['app_store_redirect_submit'])) {
        // Security
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'app-store-redirect'));
        }
        if (!isset($_POST['app_store_redirect_nonce']) || !wp_verify_nonce($_POST['app_store_redirect_nonce'], 'app_store_redirect_update')) {
            wp_die(__('Invalid nonce specified', 'app-store-redirect'), __('Error', 'app-store-redirect'), [
                'response' => 403,
                'back_link' => 'admin.php?page=' . $_GET['page'],
            ]);
        }

        // Save the Android and iOS app URLs and the custom route
        update_option(ANDROID_APP_URL_OPTION, esc_url($_POST['android_app_url']));
        update_option(IOS_APP_URL_OPTION, esc_url($_POST['ios_app_url']));
        update_option(CUSTOM_ROUTE_OPTION, esc_html($_POST['custom_route']));

        // Flush old Cached rewrite rules without visiting Settings->Permalinks (effective on next website load)
        flush_rewrite_rules();

        echo '<div class="updated"><p>' . __('Settings saved', 'app-store-redirect') . '.</p></div>';
    }
    $android_app_url = get_option(ANDROID_APP_URL_OPTION, '');
    $ios_app_url = get_option(IOS_APP_URL_OPTION, '');
    $custom_route = get_option(CUSTOM_ROUTE_OPTION, '');
?>
    <div class="wrap">
        <h2><?= __('App Store Redirect Settings', 'app-store-redirect') ?></h2>
        <form method="post" action="">
            <?php wp_nonce_field('app_store_redirect_update', 'app_store_redirect_nonce'); ?>
            <label for="android_app_url"><?= __('Google Play URL:', 'app-store-redirect') ?></label>
            <input type="text" name="android_app_url" placeholder="<?= __('Your App link on Google Play', 'app-store-redirect') ?>" id="android_app_url" value="<?php echo esc_attr($android_app_url); ?>" size="60">
            <br>
            <br>
            <label for="ios_app_url"><?= __('Apple Store URL:', 'app-store-redirect') ?></label>
            <input type="text" name="ios_app_url" placeholder="<?= __('Your App link on Apple Store', 'app-store-redirect') ?>" id="ios_app_url" value="<?php echo esc_attr($ios_app_url); ?>" size="60">
            <br>
            <br>
            <label for="custom_route"><?= __('Custom Route:', 'app-store-redirect') ?></label>
            <input type="text" name="custom_route" placeholder="<?= __('Custom link that will redirect the user, for example: appStores', 'app-store-redirect') ?>" id="custom_route" value="<?php echo esc_attr($custom_route); ?>" size="60">
            <br>
            <br>
            <input type="submit" name="app_store_redirect_submit" class="button button-primary" value="<?= __('Save') ?>">
        </form>
    </div>
<?php
}

// Add a custom route based on the setting
function app_store_redirect_init()
{
    $custom_route = get_option(CUSTOM_ROUTE_OPTION);

    if (!empty($custom_route)) {
        add_rewrite_rule('^' . $custom_route . '/?$', 'index.php?app_store_redirect=true', 'top');
    }
}
add_action('init', 'app_store_redirect_init');

// Register a query variable for the custom route
function app_store_redirect_query_vars($vars)
{
    $vars[] = 'app_store_redirect';
    return $vars;
}
add_filter('query_vars', 'app_store_redirect_query_vars');

// Redirect users to the appropriate app store based on settings
function app_store_redirect_template_redirect()
{
    global $wp_query;

    if (get_query_var('app_store_redirect')) {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];

        $android_app_url = get_option(ANDROID_APP_URL_OPTION, '');
        $ios_app_url = get_option(IOS_APP_URL_OPTION, '');

        if (strpos($user_agent, 'Android') !== false && !empty($android_app_url)) {
            header("Location: $android_app_url");
            exit;
        } elseif ((strpos($user_agent, 'iPhone') !== false || strpos($user_agent, 'iPad') !== false) && !empty($ios_app_url)) {
            header("Location: $ios_app_url");
            exit;
        } else {
            wp_safe_redirect(home_url());
            exit;
        }
    }
}
add_action('template_redirect', 'app_store_redirect_template_redirect');
