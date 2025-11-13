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
     * Target admin hook for the WebSMTP settings screen.
     *
     * @var string
     */
    protected $targetHook = 'settings_page_fluent-mail';

    /**
     * Restricted route configuration for the WebSMTP SPA.
     *
     * @var array<int, array<string, array<int, string>|string>>
     */
    protected $routesToRemove = [
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
     * Conditionally enqueue inline assets for the WebSMTP screen.
     *
     * @param string $hookSuffix
     * @return void
     */
    public function enqueueAssets($hookSuffix)
    {
        if (!$this->shouldHandleRequest($hookSuffix)) {
            return;
        }

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

        if (!current_user_can('manage_options')) {
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
     * Inject inline JavaScript to guard the SPA navigation.
     *
     * @return void
     */
    protected function injectScripts()
    {
        $routesJson = wp_json_encode($this->routesToRemove);

        if (!$routesJson) {
            return;
        }

        $script = <<<JS
(function() {
    var restrictedRoutes = {$routesJson};
    var fallbackHash = '#/connections';

    function normalizeHash(hash) {
        if (!hash) {
            return '';
        }

        var normalized = String(hash).toLowerCase();
        if (normalized.charAt(0) !== '#') {
            normalized = '#' + normalized.replace(/^#+/, '');
        }

        if (normalized.length > 1 && normalized.slice(-1) === '/') {
            normalized = normalized.slice(0, -1);
        }

        return normalized;
    }

    function isRestrictedHash(targetHash) {
        if (!targetHash) {
            return false;
        }

        var normalizedTarget = normalizeHash(targetHash);

        for (var i = 0; i < restrictedRoutes.length; i++) {
            var hashes = restrictedRoutes[i].hashes || [];

            for (var j = 0; j < hashes.length; j++) {
                if (normalizedTarget === normalizeHash(hashes[j])) {
                    return true;
                }
            }
        }

        return false;
    }

    function redirectIfRestricted() {
        var currentHash = normalizeHash(window.location.hash || '');

        if (!isRestrictedHash(currentHash)) {
            return false;
        }

        if (currentHash !== normalizeHash(fallbackHash)) {
            window.location.hash = fallbackHash;
        }

        return true;
    }

    function removeNavigationEntries() {
        var navigationRoots = document.querySelectorAll('.fluent-mail-navigation');

        if (!navigationRoots.length) {
            return;
        }

        restrictedRoutes.forEach(function(config) {
            if (!config || !config.index) {
                return;
            }

            var selector = '.fluent-mail-navigation .el-menu-item[index="' + config.index + '"]';
            var nodes = document.querySelectorAll(selector);

            if (!nodes.length) {
                return;
            }

            Array.prototype.forEach.call(nodes, function(node) {
                if (!node) {
                    return;
                }

                if (typeof node.remove === 'function') {
                    node.remove();
                    return;
                }

                if (node.parentNode) {
                    node.parentNode.removeChild(node);
                }
            });
        });
    }

    function onMutations() {
        removeNavigationEntries();
        redirectIfRestricted();
    }

    function attachObserver(target) {
        if (!target || !window.MutationObserver) {
            return;
        }

        var observer = new MutationObserver(onMutations);
        observer.observe(target, { childList: true, subtree: true });
    }

    function watchForAppRoot() {
        if (!window.MutationObserver) {
            return;
        }

        var existingRoot = document.querySelector('.fluent-mail-app');

        if (existingRoot) {
            attachObserver(existingRoot);
            return;
        }

        var fallbackTarget = document.body || document.documentElement;

        if (!fallbackTarget) {
            return;
        }

        var bootstrapObserver = new MutationObserver(function() {
            var candidate = document.querySelector('.fluent-mail-app');

            if (!candidate) {
                return;
            }

            attachObserver(candidate);
            bootstrapObserver.disconnect();
        });

        bootstrapObserver.observe(fallbackTarget, { childList: true, subtree: true });
    }

    function boot() {
        removeNavigationEntries();
        redirectIfRestricted();

        watchForAppRoot();

        window.addEventListener('hashchange', redirectIfRestricted, false);
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

