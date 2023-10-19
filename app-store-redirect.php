<?php

/**
 * Plugin Name: App Store Redirect
 * Description: Redirects users to the appropriate app store based on their operating system.
 * Version: 1.1
 * Author: Somar Kesen
 * Author URI: https://github.com/somarkn99
 */

// Add plugin settings in the admin dashboard
function app_store_redirect_admin_menu()
{
    add_menu_page('App Store Redirect Settings', 'App Store Redirect', 'manage_options', 'app-store-redirect-settings', 'app_store_redirect_settings_page', 'dashicons-smartphone');
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
                'response'  => 403,
                'back_link' => 'admin.php?page=' . $_GET['page'],
            ]);
        }

        // Save the Android and iOS app URLs and the custom route
        update_option('android_app_url', sanitize_text_field($_POST['android_app_url']));
        update_option('ios_app_url', sanitize_text_field($_POST['ios_app_url']));
        update_option('custom_route', sanitize_text_field($_POST['custom_route'])); // Added custom route setting

        // Flush old Cached rewrite rules without visiting Settings->Permalinks. (Affictive on next website load)
        flush_rewrite_rules();

        echo '<div class="updated"><p>Settings saved.</p></div>';
    }
    $android_app_url = get_option('android_app_url', '');
    $ios_app_url = get_option('ios_app_url', '');
    $custom_route = get_option('custom_route', ''); // Get the custom route setting
?>
    <div class="wrap">
        <h2>App Store Redirect Settings</h2>
        <form method="post" action="">
            <?php wp_nonce_field('app_store_redirect_update', 'app_store_redirect_nonce'); ?>
            <label for="android_app_url">Google Play URL:</label>
            <input type="text" name="android_app_url" placeholder="You App link on Google Play" id="android_app_url" value="<?php echo esc_attr($android_app_url); ?>" size="60">
            <br>
            <br>
            <label for="ios_app_url">Apple Store URL:</label>
            <input type="text" name="ios_app_url" placeholder="You App link on Apple Store" id="ios_app_url" value="<?php echo esc_attr($ios_app_url); ?>" size="60">
            <br>
            <br>
            <label for="custom_route">Custom Route:</label> <!-- Add input field for custom route -->
            <input type="text" name="custom_route" placeholder="the link that well send to user, for example: appStores" id="custom_route" value="<?php echo esc_attr($custom_route); ?>" size="60">
            <br>
            <br>
            <input type="submit" name="app_store_redirect_submit" class="button button-primary" value="Save Settings">
        </form>
    </div>
<?php
}

// Add a custom route based on the setting
function app_store_redirect_init()
{
    $custom_route = get_option('custom_route', ''); // Get the custom route setting

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

        $android_app_url = get_option('android_app_url', '');
        $ios_app_url = get_option('ios_app_url', '');

        if (strpos($user_agent, 'Android') !== false && !empty($android_app_url)) {
            // Redirect to Android App URL if the user is on an Android device
            header("Location: $android_app_url");
            exit;
        } elseif ((strpos($user_agent, 'iPhone') !== false || strpos($user_agent, 'iPad') !== false) && !empty($ios_app_url)) {
            // Redirect to iOS App URL if the user is on an iPhone or iPad
            header("Location: $ios_app_url");
            exit;
        } else {
            // Redirect to the website's home page if no specific condition is met
            header("Location: " . home_url());
            exit;
        }
    }
}
add_action('template_redirect', 'app_store_redirect_template_redirect');
