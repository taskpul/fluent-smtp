<?php

namespace FluentMail\App\Hooks\Handlers;

use FluentMail\Includes\Core\Application;

class AdminSettingsNavigationHandler
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @var string
     */
    protected $targetHook = 'settings_page_fluent-mail';

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function register()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets'], 20, 1);
    }

    public function enqueueAssets($hookSuffix)
    {
        if (!$this->shouldHandleRequest($hookSuffix)) {
            return;
        }

        $this->injectStyles();
        $this->injectScripts();
    }

    protected function shouldHandleRequest($hookSuffix)
    {
        if (!is_admin()) {
            return false;
        }

        if ($hookSuffix !== $this->targetHook) {
            return false;
        }

        if (!isset($_GET['page']) || $_GET['page'] !== 'fluent-mail') {
            return false;
        }

        return true;
    }

    protected function injectStyles()
    {
        $css = <<<'CSS'
.fluent-mail-navigation [index="notification_settings"],
.fluent-mail-navigation [index="support"],
.fluent-mail-navigation [index="docs"] {
    display: none !important;
}
.fluent-mail-navigation .fluentsmtp-menu-guard-hidden {
    display: none !important;
}
CSS;

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

    protected function injectScripts()
    {
        $script = <<<'JS'
(function(){
    var restrictedRoutes = [
        { route: 'notification_settings', hashes: ['#/notification-settings', '#/notification-settings/'] },
        { route: 'support', hashes: ['#/support', '#/support/'] },
        { route: 'docs', hashes: ['#/documentation', '#/documentation/'] }
    ];
    function hideMenuItems(){
        var navigation = document.querySelector('.fluent-mail-navigation');
        if(!navigation){
            return;
        }
        restrictedRoutes.forEach(function(config){
            var selector = "[index=\"" + config.route + "\"]";
            navigation.querySelectorAll(selector).forEach(function(item){
                if(item.classList.contains('fluentsmtp-menu-guard-hidden')){
                    return;
                }
                item.classList.add('fluentsmtp-menu-guard-hidden');
                item.setAttribute('aria-hidden', 'true');
                var link = item.querySelector('a');
                if(link){
                    link.setAttribute('tabindex', '-1');
                    link.setAttribute('aria-hidden', 'true');
                }
            });
        });
    }
    function guardNavigation(){
        var currentHash = window.location.hash.toLowerCase();
        for(var i = 0; i < restrictedRoutes.length; i++){
            var config = restrictedRoutes[i];
            for(var j = 0; j < config.hashes.length; j++){
                if(currentHash === config.hashes[j]){
                    window.location.hash = '#/';
                    return true;
                }
            }
        }
        return false;
    }
    function boot(){
        hideMenuItems();
        guardNavigation();
        var navigationWrapper = document.querySelector('.fluent-mail-main-menu-items');
        if(navigationWrapper && window.MutationObserver){
            var observer = new MutationObserver(function(){
                hideMenuItems();
            });
            observer.observe(navigationWrapper, { childList: true, subtree: true });
        }
        window.addEventListener('hashchange', guardNavigation);
    }
    if(document.readyState === 'loading'){
        document.addEventListener('DOMContentLoaded', boot);
    }else{
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
