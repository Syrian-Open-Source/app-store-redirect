<?php

/**
 * Plugin Name: App Redirect
 * Description: Redirects users to the appropriate app store based on their operating system.
 * Version: 1.2
 * Author: Somar Kesen
 * Author URI: https://github.com/somarkn99
 * Text Domain: app-redirect
 * License: GPL-2.0 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */
if (!defined('ABSPATH')) exit; // Exit if accessed directly

define('ANDROID_APP_URL_OPTION', 'android_app_url');
define('IOS_APP_URL_OPTION', 'ios_app_url');
define('CUSTOM_ROUTE_OPTION', 'custom_route');

// ------------------------------------------------------------------------------------------------
// Init & Admin dashboard Settings

// Load Translation Files
function app_store_redirect_load_textdomain()
{
    load_plugin_textdomain('app-redirect', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('init', 'app_store_redirect_load_textdomain');

// Add plugin settings in the admin dashboard
function app_store_redirect_admin_menu()
{
    add_menu_page(
        esc_html__('App Redirect Settings', 'app-redirect'),
        esc_html__('App Redirect', 'app-redirect'),
        'manage_options',
        'app-redirect-settings',
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
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'app-redirect'));
        }
        if (!isset($_POST['app_store_redirect_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['app_store_redirect_nonce'])), 'app_store_redirect_update')) {
            wp_die(esc_html__('Invalid nonce specified', 'app-redirect'), esc_html__('Error', 'app-redirect'), [
                'response' => 403,
                'back_link' => 'admin.php?page=' . sanitize_text_field($_GET['page']),
            ]);
        }

        // Save the Android and iOS app URLs and the custom route
        update_option(ANDROID_APP_URL_OPTION, esc_url_raw(sanitize_text_field($_POST['android_app_url'])));
        update_option(IOS_APP_URL_OPTION, esc_url_raw(sanitize_text_field($_POST['ios_app_url'])));
        update_option(CUSTOM_ROUTE_OPTION, sanitize_text_field($_POST['custom_route']));

        // Flush old Cached rewrite rules without visiting Settings->Permalinks (effective on the next website load)
        flush_rewrite_rules();

        echo '<div class="updated"><p>' . esc_html__('Settings saved', 'app-redirect') . '.</p></div>';
    }

    $android_app_url = esc_url(get_option(ANDROID_APP_URL_OPTION, ''));
    $ios_app_url = esc_url(get_option(IOS_APP_URL_OPTION, ''));
    $custom_route = esc_attr(get_option(CUSTOM_ROUTE_OPTION, ''));
?>
    <div class="wrap">
        <h2><?php esc_html_e('App Redirect Settings', 'app-redirect'); ?></h2>
        <form method="post" action="">
            <?php wp_nonce_field('app_store_redirect_update', 'app_store_redirect_nonce'); ?>
            <label for="android_app_url"><?php esc_html_e('Google Play URL:', 'app-redirect'); ?></label>
            <input type="url" name="android_app_url" placeholder="<?php esc_attr_e('Your App link on Google Play', 'app-redirect'); ?>" id="android_app_url" value="<?php echo esc_attr($android_app_url); ?>" size="60">
            <br><br>
            <label for="ios_app_url"><?php esc_html_e('Apple Store URL:', 'app-redirect'); ?></label>
            <input type="url" name="ios_app_url" placeholder="<?php esc_attr_e('Your App link on Apple Store', 'app-redirect'); ?>" id="ios_app_url" value="<?php echo esc_attr($ios_app_url); ?>" size="60">
            <br><br>
            <label for="custom_route"><?php esc_html_e('Custom Route:', 'app-redirect'); ?></label>
            <input type="text" name="custom_route" placeholder="<?php esc_attr_e('Custom link that will redirect the user, for example: appStores', 'app-redirect'); ?>" id="custom_route" value="<?php echo esc_attr($custom_route); ?>" size="60">
            <br><br>
            <input type="submit" name="app_store_redirect_submit" class="button button-primary" value="<?php esc_attr_e('Save', 'app-redirect'); ?>">
        </form>
    </div>
    <div class="wrap-preview-shortcode">
        <h3><?php esc_html_e('Available Shortcodes', 'app-redirect'); ?></h3>
        <span><?php esc_html_e('(click to copy)', 'app-redirect'); ?></span>

        <h4>[app-store-button buttons="ios"]</h4>
        <?php echo do_shortcode('[app-store-button buttons="ios"]'); ?>

        <h4>[app-store-button buttons="android"]</h4>
        <?php echo do_shortcode('[app-store-button buttons="android"]'); ?>

        <h4>[app-store-button buttons="all"]</h4>
        <?php echo do_shortcode('[app-store-button buttons="all"]'); ?>

        <h4>[app-store-button buttons="auto"]</h4>
        <?php echo do_shortcode('[app-store-button buttons="ios"]'); ?>
        <span style="display: inline;"><?php esc_html_e('or', 'app-redirect'); ?></span>
        <?php echo do_shortcode('[app-store-button buttons="android"]'); ?>
        <span style="display: inline;"><?php esc_html_e('According to the user operating system', 'app-redirect'); ?></span>
    </div>

    <style>
        .wrap-preview-shortcode h4 {
            cursor: pointer;
        }
    </style>
    <!-- Copy on Click -->
    <script>
        document.querySelectorAll('.wrap-preview-shortcode h4').forEach(function(h4) {
            h4.addEventListener('click', function() {
                var text = this.textContent;
                var textarea = document.createElement('textarea');
                textarea.textContent = text;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                alert("<?php echo esc_js(esc_html__('Shortcode Copied !', 'app-redirect')); ?>");
            });
        });
    </script>
<?php
}


// ------------------------------------------------------------------------------------------------
// Shortcode

/*
 * Shortcode: app-store-button 
 * Usability: [app-store-button buttons="auto"]
 * 
 * Attribuites:
 * buttons
 *   - auto : Show button according to the user operatoin-system
 *   - all : (default) Show all buttons
 *   - android : Show only google play
 *   - ios : Show only app store
 * 
 */
add_action('init', 'app_store_redirect_register_button_shortcode');
function app_store_redirect_register_button_shortcode()
{
    add_shortcode('app-store-button', 'app_store_redirect_button_shortcode');
}
function app_store_redirect_button_shortcode($atts)
{

    wp_enqueue_style('app-store-button-style', plugins_url('/style.css', __FILE__), array(), '1.0', 'all');
    extract(shortcode_atts([
        'buttons' => 'all',
    ], $atts));

    // All available buttons
    $ARGS = [
        'ios' => [
            'button_title'     => __('App Store', 'app-redirect'),
            'button_sub_title' => __('Download on the', 'app-redirect'),
            'button_class'     => 'apple-btn',
            'app_link_setting' => IOS_APP_URL_OPTION
        ],
        'android' => [
            'button_title'     => __('Google Play', 'app-redirect'),
            'button_sub_title' => __('Get it on', 'app-redirect'),
            'button_class'     => 'google-btn',
            'app_link_setting' => ANDROID_APP_URL_OPTION
        ]
    ];

    $user_os = app_store_redirect_get_user_os();

    // Echoing html directly causes errors with Gutenberg editor.
    ob_start();

    if ($buttons == 'auto' && array_key_exists($user_os, $ARGS)) { // if desktop, default to all buttons
        $data = $ARGS[$user_os];
        include __DIR__ . '/shortcode-app-redirect-button.php';
    } elseif (array_key_exists($buttons, $ARGS)) {
        $data = $ARGS[$buttons];
        include __DIR__ . '/shortcode-app-redirect-button.php';
    } else { //  all or unknown user agent or settings page preview
        foreach ($ARGS as $data) {
            include __DIR__ . '/shortcode-app-redirect-button.php';
        }
    }
    return ob_get_clean();
}


// ------------------------------------------------------------------------------------------------
// Redirection Handling

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
    if (get_query_var('app_store_redirect')) {
        $user_os = app_store_redirect_get_user_os();
        $android_app_url = esc_url(get_option(ANDROID_APP_URL_OPTION, ''));
        $ios_app_url = esc_url(get_option(IOS_APP_URL_OPTION, ''));

        if ($user_os == 'android' && !empty($android_app_url)) {
            wp_redirect($android_app_url);
            exit;
        } elseif ($user_os == 'ios' && !empty($ios_app_url)) {
            wp_redirect($ios_app_url);
            exit;
        } else {
            wp_safe_redirect(home_url());
            exit;
        }
    }
}
add_action('template_redirect', 'app_store_redirect_template_redirect');

function app_store_redirect_get_user_os()
{
    $user_agent = sanitize_text_field($_SERVER['HTTP_USER_AGENT']);
    if (strpos($user_agent, 'Android') !== false) {
        return 'android';
    } elseif ((strpos($user_agent, 'iPhone') !== false || strpos($user_agent, 'iPad') !== false)) {
        return 'ios';
    } else {
        return 'desktop';
    }
}
