<?php

namespace FluentMail\App\Hooks\Handlers;

use FluentMail\Includes\Core\Application;

class AdminBrandingReplacementHandler
{
    /**
     * Application instance.
     *
     * @var Application
     */
    protected $app;

    /**
     * Target admin hook suffix for the FluentSMTP settings screen.
     *
     * @var string
     */
    protected $targetHook = 'settings_page_fluent-mail';

    /**
     * Original plugin branding string.
     *
     * @var string
     */
    protected $searchText = 'FluentSMTP';

    /**
     * Replacement branding string.
     *
     * @var string
     */
    protected $replacementText = 'WebSMTP';

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
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets'], 25, 1);
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

        $this->injectReplacementScript();
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
     * Inject inline JavaScript that swaps the visible branding text.
     *
     * @return void
     */
    protected function injectReplacementScript()
    {
        $searchText = esc_js($this->searchText);
        $replacementText = esc_js($this->replacementText);

        $script = <<<JS
(function() {
    var searchText = '{$searchText}';
    var replacementText = '{$replacementText}';

    function replaceInNode(node) {
        if (!node) {
            return;
        }

        if (node.nodeType === Node.TEXT_NODE) {
            var value = node.nodeValue;

            if (value && value.indexOf(searchText) !== -1) {
                node.nodeValue = value.split(searchText).join(replacementText);
            }

            return;
        }

        if (!node.childNodes || !node.childNodes.length) {
            return;
        }

        for (var i = 0; i < node.childNodes.length; i++) {
            replaceInNode(node.childNodes[i]);
        }
    }

    function handleMutations(mutations) {
        for (var i = 0; i < mutations.length; i++) {
            var mutation = mutations[i];

            if (mutation.type === 'childList') {
                if (mutation.addedNodes && mutation.addedNodes.length) {
                    for (var j = 0; j < mutation.addedNodes.length; j++) {
                        var addedNode = mutation.addedNodes[j];

                        if (addedNode.nodeType === Node.TEXT_NODE) {
                            replaceInNode(addedNode);
                        } else if (addedNode.nodeType === Node.ELEMENT_NODE) {
                            replaceInNode(addedNode);
                        }
                    }
                }

                if (mutation.removedNodes && mutation.removedNodes.length) {
                    continue;
                }
            }

            if (mutation.type === 'characterData') {
                replaceInNode(mutation.target);
            }
        }
    }

    function initialize() {
        if (!document.body) {
            return;
        }

        replaceInNode(document.body);

        var observer = new MutationObserver(handleMutations);

        observer.observe(document.body, {
            childList: true,
            subtree: true,
            characterData: true
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize);
    } else {
        initialize();
    }
})();
JS;

        wp_register_script('fluent-smtp-admin-branding', '', [], false, true);
        wp_enqueue_script('fluent-smtp-admin-branding');
        wp_add_inline_script('fluent-smtp-admin-branding', $script);
    }
}
