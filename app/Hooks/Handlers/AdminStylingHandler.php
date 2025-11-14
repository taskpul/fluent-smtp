<?php

namespace FluentMail\App\Hooks\Handlers;

use FluentMail\Includes\Core\Application;

class AdminStylingHandler
{
    /**
     * @var Application
     */
    protected $app;

    public function __construct(Application $application)
    {
        $this->app = $application;
    }

    public function register()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueueStyles'], 20);
    }

    public function enqueueStyles($hookSuffix)
    {
        if ($hookSuffix !== 'settings_page_fluent-mail') {
            return;
        }

        $css = <<<'CSS'
.fluent-mail-app .el-button,
.fluent-mail-app .wp-core-ui .button {
    border-radius: 4px;
    transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
}

.fluent-mail-app .el-button--primary,
.fluent-mail-app .el-button--success,
.fluent-mail-app .wp-core-ui .button.button-primary,
.fluent-mail-app .wp-core-ui .button-primary {
    background-color: #000;
    border-color: #000;
    color: #fff;
}

.fluent-mail-app .el-button--primary:hover,
.fluent-mail-app .el-button--primary:focus,
.fluent-mail-app .el-button--primary:active,
.fluent-mail-app .el-button--success:hover,
.fluent-mail-app .el-button--success:focus,
.fluent-mail-app .el-button--success:active,
.fluent-mail-app .wp-core-ui .button.button-primary:hover,
.fluent-mail-app .wp-core-ui .button.button-primary:focus,
.fluent-mail-app .wp-core-ui .button.button-primary:active,
.fluent-mail-app .wp-core-ui .button-primary:hover,
.fluent-mail-app .wp-core-ui .button-primary:focus,
.fluent-mail-app .wp-core-ui .button-primary:active {
    background-color: #1a1a1a;
    border-color: #1a1a1a;
    color: #fff;
    box-shadow: none;
}

.fluent-mail-app .el-button:not(.el-button--primary):not(.el-button--success):not(.el-button--danger):not(.el-button--text):not(.el-button--link),
.fluent-mail-app .el-button.is-plain:not(.el-button--danger):not(.el-button--text),
.fluent-mail-app .wp-core-ui .button:not(.button-primary):not(.button-link) {
    background-color: #fff;
    color: #000;
    border: 1px solid #000;
    box-shadow: none;
}

.fluent-mail-app .el-button:not(.el-button--primary):not(.el-button--success):not(.el-button--danger):not(.el-button--text):not(.el-button--link):hover,
.fluent-mail-app .el-button:not(.el-button--primary):not(.el-button--success):not(.el-button--danger):not(.el-button--text):not(.el-button--link):focus,
.fluent-mail-app .el-button.is-plain:not(.el-button--danger):not(.el-button--text):hover,
.fluent-mail-app .el-button.is-plain:not(.el-button--danger):not(.el-button--text):focus,
.fluent-mail-app .wp-core-ui .button:not(.button-primary):not(.button-link):hover,
.fluent-mail-app .wp-core-ui .button:not(.button-primary):not(.button-link):focus {
    background-color: #f5f5f5;
    color: #000;
    border-color: #1a1a1a;
}

.fluent-mail-app .fss_header,
.fluent-mail-app .fss_content,
.fluent-mail-app .fsm_card,
.fluent-mail-app .fss_config_section,
.fluent-mail-app .fss_content_box,
.fluent-mail-app .el-card {
    border: 1px solid #e5e7eb;
    border-radius: 5px;
    background-color: #fff;
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.05);
}

.fluent-mail-app .fss_header {
    border-bottom: none;
    padding: 18px 20px;
    background: #f9fafb;
    color: #111827;
    font-weight: 600;
    border-radius: 5px 5px 0 0;
}

.fluent-mail-app .fss_content {
    border-top-left-radius: 0;
    border-top-right-radius: 0;
    padding: 24px;
}

.fluent-mail-app .el-input__inner,
.fluent-mail-app .el-textarea__inner,
.fluent-mail-app .el-select .el-input__inner,
.fluent-mail-app .el-date-editor .el-input__inner {
    border: 1px solid #d1d5db;
    border-radius: 4px;
    box-shadow: none;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.fluent-mail-app .el-input__inner:focus,
.fluent-mail-app .el-textarea__inner:focus,
.fluent-mail-app .el-select .el-input.is-focus .el-input__inner,
.fluent-mail-app .el-date-editor .el-input__inner:focus {
    border-color: #000;
    box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.08);
}

.fluent-mail-app .el-table,
.fluent-mail-app .el-table__header-wrapper,
.fluent-mail-app .el-table__body-wrapper {
    border-radius: 5px;
    border: 1px solid #e5e7eb;
    overflow: hidden;
}

.fluent-mail-app .el-table th {
    background-color: #f3f4f6;
    color: #111827;
    font-weight: 600;
    border-bottom: 1px solid #e5e7eb;
}

.fluent-mail-app .el-table th .cell {
    color: #111827;
}

.fluent-mail-app .el-table td {
    border-bottom: 1px solid #f1f5f9;
}

.fluent-mail-app .el-table::before,
.fluent-mail-app .el-table::after {
    background-color: transparent;
}

.fluent-mail-app .el-pagination .el-pager li.active {
    border-color: #000;
    color: #fff;
    background-color: #000;
}
CSS;

        $handle = 'fluent_mail_admin_app';

        if (!wp_style_is($handle, 'enqueued') && wp_style_is($handle, 'registered')) {
            wp_enqueue_style($handle);
        }

        if (wp_style_is($handle, 'registered')) {
            wp_add_inline_style($handle, $css);
            return;
        }

        $fallbackHandle = 'fluentsmtp-admin-customizations';
        wp_register_style($fallbackHandle, false, [], FLUENTMAIL_PLUGIN_VERSION);
        wp_enqueue_style($fallbackHandle);
        wp_add_inline_style($fallbackHandle, $css);
    }
}
