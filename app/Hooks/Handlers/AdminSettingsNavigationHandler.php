<?php

namespace FluentMail\App\Hooks\Handlers;

use FluentMail\Includes\Core\Application;

class AdminSettingsNavigationHandler
{
    /**
     * Application instance.
     *
     * @var Application
     */
    protected $app;

    /**
     * Target admin hook for the FluentSMTP settings screen.
     *
     * @var string
     */
    protected $targetHook = 'settings_page_fluent-mail';

    /**
     * Hidden route configuration for the FluentSMTP SPA.
     *
     * @var array<int, array<string, array<int, string>|string>>
     */
    protected $routesToHide = [
        [
            'index'  => 'notification_settings',
            'hashes' => ['#/notification-settings', '#/notification-settings/'],
        ],
        [
            'index'  => 'support',
            'hashes' => ['#/support', '#/support/'],
        ],
        [
            'index'  => 'docs',
            'hashes' => ['#/documentation', '#/documentation/'],
        ],
    ];

    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Register the handler with WordPress.
     *
     * @return void
     */
    public function register()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets'], 20, 1);
    }

    /**
     * Conditionally enqueue inline assets for the FluentSMTP screen.
     *
     * @param string $hookSuffix
     * @return void
     */
    public function enqueueAssets($hookSuffix)
    {
        if (!$this->shouldHandleRequest($hookSuffix)) {
            return;
        }

        $this->injectStyles();
        $this->injectScripts();
    }

    /**
     * Determine whether the current admin request should be handled.
     *
     * @param string $hookSuffix
     * @return bool
     */
    protected function shouldHandleRequest($hookSuffix)
    {
        if (!is_admin()) {
            return false;
        }

        if ($hookSuffix !== $this->targetHook) {
            return false;
        }

        $page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';

        if ($page !== 'fluent-mail') {
            return false;
        }

        return true;
    }

    /**
     * Inject inline CSS to visually hide disallowed menu entries.
     *
     * @return void
     */
    protected function injectStyles()
    {
        $selectors = [];

        foreach ($this->routesToHide as $config) {
            $selectors[] = '.fluent-mail-navigation [index="' . sanitize_key($config['index']) . '"]';
        }

        $css  = implode(",\n", $selectors) . " {\n    display: none !important;\n}";
        $css .= "\n.fluent-mail-navigation .fluentsmtp-menu-guard-hidden {\n    display: none !important;\n}\n";

        if (wp_style_is('fluent_mail_admin_app', 'enqueued')) {
            wp_add_inline_style('fluent_mail_admin_app', $css);
            return;
        }

        if (!wp_style_is('fluentsmtp-admin-menu-guard', 'registered')) {
            wp_register_style('fluentsmtp-admin-menu-guard', false);
        }

        wp_enqueue_style('fluentsmtp-admin-menu-guard');
        wp_add_inline_style('fluentsmtp-admin-menu-guard', $css);
    }

    /**
     * Inject inline JavaScript to guard the SPA navigation.
     *
     * @return void
     */
    protected function injectScripts()
    {
        $routesJson = wp_json_encode($this->routesToHide);

        if (!$routesJson) {
            return;
        }

        $script = <<<JS
(function() {
    var restrictedRoutes = {$routesJson};
    var fallbackHash = '#/connections';

    function hideMenuItems() {
        var navigation = document.querySelector('.fluent-mail-navigation');
        if (!navigation) {
            return;
        }

        restrictedRoutes.forEach(function(config) {
            var selector = '[index="' + config.index + '"]';
            Array.prototype.forEach.call(navigation.querySelectorAll(selector), function(item) {
                if (item.getAttribute('data-fluentsmtp-guarded') === '1') {
                    return;
                }

                item.classList.add('fluentsmtp-menu-guard-hidden');
                item.setAttribute('aria-hidden', 'true');
                item.setAttribute('tabindex', '-1');
                item.setAttribute('data-fluentsmtp-guarded', '1');
                item.addEventListener('click', preventNavigation, true);

                var link = item.querySelector('a');
                if (link) {
                    link.setAttribute('tabindex', '-1');
                    link.setAttribute('aria-hidden', 'true');
                    link.addEventListener('click', preventNavigation, true);
                }
            });
        });
    }

    function preventNavigation(event) {
        if (event) {
            if (typeof event.preventDefault === 'function') {
                event.preventDefault();
            }
            if (typeof event.stopImmediatePropagation === 'function') {
                event.stopImmediatePropagation();
            }
        }

        guardNavigation();
    }

    function guardNavigation() {
        var currentHash = (window.location.hash || '').toLowerCase();

        for (var i = 0; i < restrictedRoutes.length; i++) {
            var hashes = restrictedRoutes[i].hashes || [];

            for (var j = 0; j < hashes.length; j++) {
                if (currentHash === hashes[j]) {
                    if (currentHash !== fallbackHash) {
                        window.location.hash = fallbackHash;
                    }
                    return true;
                }
            }
        }

        return false;
    }

    function boot() {
        hideMenuItems();
        guardNavigation();

        var navigationWrapper = document.querySelector('.fluent-mail-main-menu-items');
        if (navigationWrapper && window.MutationObserver) {
            var observer = new MutationObserver(function() {
                hideMenuItems();
            });
            observer.observe(navigationWrapper, { childList: true, subtree: true });
        }

        window.addEventListener('hashchange', guardNavigation, false);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
JS;

        if (wp_script_is('fluent_mail_admin_app_boot', 'enqueued')) {
            wp_add_inline_script('fluent_mail_admin_app_boot', $script, 'after');
            return;
        }

        if (!wp_script_is('fluentsmtp-admin-menu-guard', 'registered')) {
            wp_register_script('fluentsmtp-admin-menu-guard', '', [], false, true);
        }

        wp_enqueue_script('fluentsmtp-admin-menu-guard');
        wp_add_inline_script('fluentsmtp-admin-menu-guard', $script);
    }
}

