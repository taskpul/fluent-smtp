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
#fluent_mail_app,
.fluent-mail-app {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    color: #1f2937;
    background: #f5f7fb;
    line-height: 1.6;
}

.fluent-mail-app a {
    color: #2563eb;
    text-decoration: none;
}

.fluent-mail-app a:hover,
.fluent-mail-app a:focus {
    color: #1d4ed8;
    text-decoration: underline;
}

.fluent-mail-app .fluent-mail-main-menu-items {
    background: linear-gradient(90deg, #0f172a 0%, #1e293b 100%);
    border-radius: 16px;
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.18);
    margin: 24px 24px 12px;
    padding: 6px;
}

.fluent-mail-app .fluent-mail-navigation {
    border-bottom: none;
    background: transparent;
    padding: 0 12px;
}

.fluent-mail-app .fluent-mail-navigation .el-menu-item {
    color: rgba(255, 255, 255, 0.75);
    font-weight: 500;
    letter-spacing: 0.01em;
    border-radius: 12px;
    margin: 6px;
    padding: 18px 22px;
    transition: color 0.2s ease, background-color 0.2s ease, box-shadow 0.2s ease;
}

.fluent-mail-app .fluent-mail-navigation .el-menu-item.is-active,
.fluent-mail-app .fluent-mail-navigation .el-menu-item:hover {
    color: #f8fafc;
    background: rgba(255, 255, 255, 0.14);
    box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.2);
}

.fluent-mail-app .logo img {
    filter: drop-shadow(0 10px 25px rgba(15, 23, 42, 0.25));
}

.fluent-mail-app .fluent-mail-body {
    padding: 12px 24px 36px;
}

.fluent-mail-app .fss_header,
.fluent-mail-app .fss_content,
.fluent-mail-app .fsm_card,
.fluent-mail-app .fss_config_section,
.fluent-mail-app .fss_content_box,
.fluent-mail-app .el-card,
.fluent-mail-app .el-table,
.fluent-mail-app .el-message-box,
.fluent-mail-app .el-dialog {
    border-radius: 18px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    box-shadow: 0 25px 65px rgba(15, 23, 42, 0.08);
}

.fluent-mail-app .fss_header {
    background: linear-gradient(120deg, rgba(37, 99, 235, 0.08), rgba(59, 130, 246, 0));
    border-bottom: 1px solid rgba(226, 232, 240, 0.8);
    padding: 24px;
    border-radius: 18px 18px 0 0;
    color: #0f172a;
    font-weight: 600;
    font-size: 18px;
}

.fluent-mail-app .fss_content {
    padding: 28px;
    border-top-left-radius: 0;
    border-top-right-radius: 0;
}

.fluent-mail-app .fsm_card,
.fluent-mail-app .fss_content_box,
.fluent-mail-app .el-card {
    padding: 24px;
    margin-bottom: 24px;
}

.fluent-mail-app .el-card__header {
    padding: 0 0 18px;
    font-weight: 600;
    color: #0f172a;
    font-size: 16px;
}

.fluent-mail-app .el-card__body {
    padding: 0;
}

.fluent-mail-app .el-table {
    overflow: hidden;
}

.fluent-mail-app .el-table__header-wrapper th {
    background: #f8fafc;
    color: #0f172a;
    text-transform: uppercase;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.08em;
    border-bottom: 1px solid #e2e8f0;
}

.fluent-mail-app .el-table__body-wrapper td {
    border-bottom: 1px solid #f1f5f9;
    padding: 16px 20px;
}

.fluent-mail-app .el-table::before,
.fluent-mail-app .el-table::after {
    height: 0;
}

.fluent-mail-app .el-table tr:hover > td {
    background: #f1f5f9;
}

.fluent-mail-app .el-pagination .el-pager li,
.fluent-mail-app .el-pagination button {
    border-radius: 12px;
    border: 1px solid transparent;
    transition: all 0.2s ease;
    min-width: 36px;
    height: 36px;
    line-height: 36px;
    font-weight: 500;
}

.fluent-mail-app .el-pagination .el-pager li.active {
    background: #2563eb;
    border-color: #2563eb;
    color: #ffffff;
    box-shadow: 0 8px 20px rgba(37, 99, 235, 0.35);
}

.fluent-mail-app .el-pagination .el-pager li:not(.active):hover,
.fluent-mail-app .el-pagination button:hover {
    border-color: rgba(37, 99, 235, 0.4);
    color: #2563eb;
    background: rgba(37, 99, 235, 0.08);
}

.fluent-mail-app .el-input__inner,
.fluent-mail-app .el-textarea__inner,
.fluent-mail-app .el-select .el-input__inner,
.fluent-mail-app .el-date-editor .el-input__inner {
    border: 1px solid #d4dbe5;
    border-radius: 12px;
    padding: 14px 16px;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
    background-color: #f9fafc;
    color: #0f172a;
}

.fluent-mail-app .el-input__inner:focus,
.fluent-mail-app .el-textarea__inner:focus,
.fluent-mail-app .el-select .el-input.is-focus .el-input__inner,
.fluent-mail-app .el-date-editor .el-input__inner:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
    background-color: #ffffff;
}

.fluent-mail-app .el-input__inner::placeholder,
.fluent-mail-app .el-textarea__inner::placeholder {
    color: #94a3b8;
}

.fluent-mail-app .el-form-item__label {
    color: #475569;
    font-weight: 500;
    padding-bottom: 6px;
}

.fluent-mail-app .el-button,
.fluent-mail-app .wp-core-ui .button {
    border-radius: 12px;
    padding: 12px 20px;
    font-weight: 600;
    letter-spacing: 0.02em;
    transition: transform 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease, color 0.18s ease, border-color 0.18s ease;
}

.fluent-mail-app .el-button:focus,
.fluent-mail-app .el-button:hover,
.fluent-mail-app .wp-core-ui .button:focus,
.fluent-mail-app .wp-core-ui .button:hover {
    transform: translateY(-1px);
    box-shadow: 0 16px 30px rgba(15, 23, 42, 0.15);
}

.fluent-mail-app .el-button--primary,
.fluent-mail-app .el-button--success,
.fluent-mail-app .wp-core-ui .button.button-primary,
.fluent-mail-app .wp-core-ui .button-primary {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    border: none;
    color: #ffffff;
}

.fluent-mail-app .el-button--primary:hover,
.fluent-mail-app .el-button--primary:focus,
.fluent-mail-app .el-button--success:hover,
.fluent-mail-app .el-button--success:focus,
.fluent-mail-app .wp-core-ui .button.button-primary:hover,
.fluent-mail-app .wp-core-ui .button.button-primary:focus,
.fluent-mail-app .wp-core-ui .button-primary:hover,
.fluent-mail-app .wp-core-ui .button-primary:focus {
    background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
}

.fluent-mail-app .el-button:not(.el-button--primary):not(.el-button--success):not(.el-button--danger):not(.el-button--text):not(.el-button--link),
.fluent-mail-app .el-button.is-plain:not(.el-button--danger):not(.el-button--text),
.fluent-mail-app .wp-core-ui .button:not(.button-primary):not(.button-link) {
    background: #ffffff;
    border: 1px solid #cbd5f5;
    color: #1f2937;
}

.fluent-mail-app .el-button:not(.el-button--primary):not(.el-button--success):not(.el-button--danger):not(.el-button--text):not(.el-button--link):hover,
.fluent-mail-app .el-button:not(.el-button--primary):not(.el-button--success):not(.el-button--danger):not(.el-button--text):not(.el-button--link):focus,
.fluent-mail-app .el-button.is-plain:not(.el-button--danger):not(.el-button--text):hover,
.fluent-mail-app .el-button.is-plain:not(.el-button--danger):not(.el-button--text):focus,
.fluent-mail-app .wp-core-ui .button:not(.button-primary):not(.button-link):hover,
.fluent-mail-app .wp-core-ui .button:not(.button-primary):not(.button-link):focus {
    background: rgba(37, 99, 235, 0.08);
    border-color: #2563eb;
    color: #1d4ed8;
}

.fluent-mail-app .el-alert,
.fluent-mail-app .notice,
.fluent-mail-app .el-message {
    border-radius: 14px;
    border: 1px solid transparent;
    padding: 18px 20px;
    box-shadow: 0 14px 35px rgba(15, 23, 42, 0.12);
}

.fluent-mail-app .el-alert--success,
.fluent-mail-app .notice-success {
    background: rgba(34, 197, 94, 0.12);
    border-color: rgba(22, 163, 74, 0.35);
    color: #166534;
}

.fluent-mail-app .el-alert--error,
.fluent-mail-app .notice-error {
    background: rgba(248, 113, 113, 0.12);
    border-color: rgba(220, 38, 38, 0.35);
    color: #b91c1c;
}

.fluent-mail-app .el-alert--warning,
.fluent-mail-app .notice-warning {
    background: rgba(251, 191, 36, 0.16);
    border-color: rgba(217, 119, 6, 0.35);
    color: #b45309;
}

.fluent-mail-app .el-tabs__header {
    margin-bottom: 24px;
}

.fluent-mail-app .el-tabs__item {
    border-radius: 10px 10px 0 0;
    padding: 14px 22px;
    font-weight: 600;
    color: #64748b;
    transition: all 0.2s ease;
}

.fluent-mail-app .el-tabs__item:hover,
.fluent-mail-app .el-tabs__item.is-active {
    color: #1e293b;
    background: rgba(37, 99, 235, 0.12);
}

.fluent-mail-app .el-tabs__active-bar {
    background: linear-gradient(90deg, #2563eb 0%, #1d4ed8 100%);
    height: 4px;
    border-radius: 999px;
}

.fluent-mail-app .el-tag {
    border-radius: 999px;
    padding: 6px 14px;
    font-weight: 600;
    letter-spacing: 0.03em;
    background: rgba(37, 99, 235, 0.08);
    border: 1px solid rgba(37, 99, 235, 0.18);
    color: #1d4ed8;
}

.fluent-mail-app .el-breadcrumb__inner,
.fluent-mail-app .el-breadcrumb__inner a {
    font-weight: 500;
    color: #475569;
}

.fluent-mail-app .el-divider--horizontal {
    margin: 28px 0;
    border-top: 1px solid #e2e8f0;
}

.fluent-mail-app .el-message-box__header {
    padding: 24px 24px 12px;
    border-bottom: 1px solid rgba(226, 232, 240, 0.75);
}

.fluent-mail-app .el-message-box__title {
    font-size: 18px;
    font-weight: 600;
    color: #0f172a;
}

.fluent-mail-app .el-message-box__content {
    padding: 0 24px 24px;
    color: #475569;
}

.fluent-mail-app .el-message-box__btns {
    padding: 18px 24px;
    border-top: 1px solid rgba(226, 232, 240, 0.75);
}

.fluent-mail-app .el-dialog__header {
    padding: 26px 28px 12px;
    border-bottom: 1px solid rgba(226, 232, 240, 0.75);
}

.fluent-mail-app .el-dialog__title {
    font-weight: 600;
    font-size: 20px;
    color: #0f172a;
}

.fluent-mail-app .el-dialog__body {
    padding: 0 28px 24px;
    color: #475569;
}

.fluent-mail-app .el-dialog__footer {
    padding: 18px 28px 28px;
    border-top: 1px solid rgba(226, 232, 240, 0.75);
}

.fluent-mail-app .el-tooltip__popper.is-dark {
    background: #0f172a;
    border-radius: 10px;
    padding: 8px 12px;
    box-shadow: 0 14px 35px rgba(15, 23, 42, 0.2);
}

.fluent-mail-app .el-tooltip__popper.is-dark .popper__arrow::after {
    border-top-color: #0f172a;
}

.fluent-mail-app .el-loading-mask {
    background-color: rgba(248, 250, 252, 0.85);
}

.fluent-mail-app .el-loading-spinner .path {
    stroke: #2563eb;
}

.fluent-mail-app .el-loading-spinner i {
    color: #2563eb;
}

@media (max-width: 960px) {
    .fluent-mail-app .fluent-mail-main-menu-items {
        margin: 16px;
        padding: 8px;
        border-radius: 14px;
    }

    .fluent-mail-app .fluent-mail-navigation .el-menu-item {
        margin: 4px;
        padding: 14px 16px;
    }

    .fluent-mail-app .fluent-mail-body {
        padding: 16px;
    }
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
