<?php
/*
Plugin Name:  WebSMTP
Plugin URI:   https://fluentsmtp.com
Description:  The Ultimate SMTP Connection Plugin for WordPress.
Version:      2.2.92
Author:       WebSMTP & WPManageNinja Team
Author URI:   https://fluentsmtp.com
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  fluent-smtp
Domain Path:  /language
*/

!defined('WPINC') && die;

define('FLUENTMAIL_PLUGIN_FILE', __FILE__);

require_once plugin_dir_path(__FILE__) . 'boot.php';

register_activation_hook(
    __FILE__, array('\FluentMail\Includes\Activator', 'handle')
);

register_deactivation_hook(
    __FILE__, array('\FluentMail\Includes\Deactivator', 'handle')
);

/**
 * Initializes the Fluent SMTP plugin.
 *
 * This function creates a new instance of the FluentMail\Includes\Core\Application class and registers
 * an action hook to be executed when the plugins are loaded. Inside the action hook, the 'fluentMail_loaded'
 * action is triggered with the application instance as a parameter.
 *
 * @since 1.0.0
 */
function fluentSmtpInit() {
    $application = new FluentMail\Includes\Core\Application;
    add_action('plugins_loaded', function () use ($application) {
        do_action('fluentMail_loaded', $application);
    });
}

fluentSmtpInit();

add_action('init', function () {
    add_filter('http_request_args', function ($args, $url) {
        if (strpos($url, 'api.wordpress.org/plugins/update-check') === false) {
            return $args;
        }

        if (empty($args['body']['plugins'])) {
            return $args;
        }

        $payload = json_decode($args['body']['plugins'], true);

        if (!$payload) {
            $payload = maybe_unserialize($args['body']['plugins']);
        }

        if (!is_array($payload) || empty($payload['plugins'])) {
            return $args;
        }

        $basename = plugin_basename(__FILE__);

        if (isset($payload['plugins'][$basename])) {
            unset($payload['plugins'][$basename]);
        }

        if (!empty($payload['active'])) {
            $payload['active'] = array_values(array_filter(
                $payload['active'],
                function ($plugin) use ($basename) {
                    return $plugin !== $basename;
                }
            ));
        }

        $args['body']['plugins'] = wp_json_encode($payload);

        return $args;
    }, 10, 2);

    if (!is_admin()) {
        return;
    }

    if (!class_exists('\\FluentMail\\Updater\\FluentLicensing')) {
        $updaterFile = plugin_dir_path(__FILE__) . 'updater/FluentLicensing.php';

        if (file_exists($updaterFile)) {
            require_once $updaterFile;
        }
    }

    try {
        $licenseInstance = (new \FluentMail\Updater\FluentLicensing())->register([
            'version'  => FLUENTMAIL_PLUGIN_VERSION,
            'item_id'  => '1643',
            'basename' => plugin_basename(__FILE__),
            'api_url'  => 'https://east.webmakerr.com'
        ]);

        if (!class_exists('\\FluentMail\\Updater\\LicenseSettings')) {
            $licenseSettings = plugin_dir_path(__FILE__) . 'updater/LicenseSettings.php';

            if (file_exists($licenseSettings)) {
                require_once $licenseSettings;
            }
        }

        if (class_exists('\\FluentMail\\Updater\\LicenseSettings')) {
            (new \FluentMail\Updater\LicenseSettings())
                ->register($licenseInstance, [
                    'menu_title'   => __('WebSMTP License', 'fluent-smtp'),
                    'page_title'   => __('WebSMTP License', 'fluent-smtp'),
                    'title'        => __('WebSMTP License', 'fluent-smtp'),
                    'license_key'  => __('License Key', 'fluent-smtp'),
                    'purchase_url' => 'https://fluentsmtp.com/pricing/?utm_source=plugin&utm_medium=settings&utm_campaign=license',
                    'account_url'  => 'https://fluentsmtp.com/account/',
                    'plugin_name'  => 'WebSMTP'
                ])
                ->addPage([
                    'type'        => 'submenu',
                    'parent_slug' => 'options-general.php',
                    'menu_slug'   => 'fluent-smtp-license',
                    'page_title'  => __('WebSMTP License', 'fluent-smtp'),
                    'menu_title'  => __('WebSMTP License', 'fluent-smtp')
                ]);
        }
    } catch (\Exception $exception) {
        error_log('FluentSMTP updater initialization failed: ' . $exception->getMessage());
    }
});

if (!function_exists('wp_mail')):
    function wp_mail($to, $subject, $message, $headers = '', $attachments = array()) {
        return fluentMailSend($to, $subject, $message, $headers, $attachments);
    }
 else :
    if (!(defined('DOING_AJAX') && DOING_AJAX)):
        add_action('init', 'fluentMailFuncCouldNotBeLoadedRecheckPluginsLoad');
    endif;
endif;

/*
 * Thanks for checking the source code
 * Please check the full source here: https://github.com/WPManageNinja/fluent-smtp
 * Would love to welcome your pull request
*/
