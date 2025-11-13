<?php

namespace FluentMail\App\Hooks\Handlers;

use FluentMail\Includes\Core\Application;

class AdminHeaderNavigationHandler
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
     * Restricted menu labels (lowercase) for the header navigation.
     *
     * @var array<int, string>
     */
    protected $restrictedLabels = [
        'alerts',
        'about',
        'documentation',
    ];

    /**
     * Restricted hash routes within the WebSMTP SPA.
     *
     * @var array<int, string>
     */
    protected $restrictedHashes = [
        '#/notification-settings',
        '#/support',
        '#/documentation',
    ];

    /**
     * Fallback hash route to redirect users toward.
     *
     * @var string
     */
    protected $fallbackHash = '#/';

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
     * Inject inline JavaScript to guard the SPA header navigation.
     *
     * @return void
     */
    protected function injectScripts()
    {
        $labelsJson = wp_json_encode($this->restrictedLabels);
        $hashesJson = wp_json_encode($this->restrictedHashes);
        $fallbackHash = esc_js($this->fallbackHash);

        if (!$labelsJson || !$hashesJson) {
            return;
        }

        $script = <<<JS
(function() {
    var restrictedLabels = {$labelsJson};
    var restrictedHashes = {$hashesJson};
    var fallbackHash = '{$fallbackHash}';

    function normalizeText(text) {
        if (!text) {
            return '';
        }

        return String(text)
            .replace(/\s+/g, ' ')
            .trim()
            .toLowerCase();
    }

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

    function isRestrictedLabel(text) {
        if (!restrictedLabels || !restrictedLabels.length) {
            return false;
        }

        var normalized = normalizeText(text);

        for (var i = 0; i < restrictedLabels.length; i++) {
            if (normalized === normalizeText(restrictedLabels[i])) {
                return true;
            }
        }

        return false;
    }

    function isRestrictedHash(hash) {
        if (!restrictedHashes || !restrictedHashes.length) {
            return false;
        }

        var normalizedTarget = normalizeHash(hash);

        for (var i = 0; i < restrictedHashes.length; i++) {
            if (normalizedTarget === normalizeHash(restrictedHashes[i])) {
                return true;
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

    function findHeaderContainer() {
        var wrapper = document.querySelector('.fm_header_wrapper .fm_top_nav');

        if (wrapper) {
            return wrapper;
        }

        return document.querySelector('.fm_top_nav');
    }

    function resolveMenuRoot(node, container) {
        if (!node || !container) {
            return null;
        }

        var current = node;

        while (current && current !== container) {
            if (current.parentNode === container) {
                return current;
            }

            current = current.parentNode;
        }

        return null;
    }

    function removeNode(node) {
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
    }

    function purgeRestrictedItems(container) {
        if (!container) {
            return;
        }

        var childNodes = container.children ? Array.prototype.slice.call(container.children) : [];

        if (childNodes.length) {
            childNodes.forEach(function(child) {
                if (!child) {
                    return;
                }

                var textValue = normalizeText(child.textContent || '');

                if (isRestrictedLabel(textValue)) {
                    removeNode(child);
                }
            });
        }

        var textualCandidates = container.querySelectorAll('*');

        Array.prototype.forEach.call(textualCandidates, function(candidate) {
            if (!candidate || !candidate.textContent) {
                return;
            }

            if (!isRestrictedLabel(candidate.textContent)) {
                return;
            }

            var menuRoot = resolveMenuRoot(candidate, container);

            if (menuRoot) {
                removeNode(menuRoot);
            }
        });
    }

    function onHeaderMutations() {
        var container = findHeaderContainer();
        purgeRestrictedItems(container);
        redirectIfRestricted();
    }

    function observeHeader(container) {
        if (!container || !window.MutationObserver) {
            return;
        }

        var observer = new MutationObserver(function() {
            onHeaderMutations();
        });

        observer.observe(container, { childList: true, subtree: true });
    }

    function watchForHeader() {
        var container = findHeaderContainer();

        if (container) {
            purgeRestrictedItems(container);
            observeHeader(container);
            return;
        }

        if (!window.MutationObserver) {
            return;
        }

        var bootstrapTarget = document.body || document.documentElement;

        if (!bootstrapTarget) {
            return;
        }

        var bootstrapObserver = new MutationObserver(function() {
            var candidate = findHeaderContainer();

            if (!candidate) {
                return;
            }

            purgeRestrictedItems(candidate);
            observeHeader(candidate);
            bootstrapObserver.disconnect();
        });

        bootstrapObserver.observe(bootstrapTarget, { childList: true, subtree: true });
    }

    function boot() {
        redirectIfRestricted();
        watchForHeader();
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

        if (!wp_script_is('fluentsmtp-admin-header-guard', 'registered')) {
            wp_register_script('fluentsmtp-admin-header-guard', '', [], false, true);
        }

        wp_enqueue_script('fluentsmtp-admin-header-guard');
        wp_add_inline_script('fluentsmtp-admin-header-guard', $script);
    }
}
