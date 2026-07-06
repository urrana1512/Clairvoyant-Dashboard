<?php
/**
 * Settings Module Coordinator Class
 * 
 * @package Clairvoyant_Core
 * @subpackage Settings
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class CV_Settings {

    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->load_dependencies();
        add_action('admin_menu', array($this, 'register_submenus'), 45);
        add_action('admin_post_cv_save_settings', array($this, 'save_settings_handler'));
    }

    private function load_dependencies() {
        require_once CLAIRVOYANT_PLUGIN_DIR . 'modules/settings/database.php';
    }

    /**
     * Registers settings submenu page
     */
    public function register_submenus() {
        add_submenu_page(
            'clairvoyant-dashboard',
            __('Settings', 'clairvoyant-core'),
            __('Settings', 'clairvoyant-core'),
            'manage_options',
            'cv-settings',
            array($this, 'render_page')
        );
    }

    public function render_page() {
        Clairvoyant_Admin_Menu::render_layout_wrapper(function() {
            require_once CLAIRVOYANT_PLUGIN_DIR . 'modules/settings/page.php';
        });
    }

    /**
     * Handles Settings post form updates
     */
    public function save_settings_handler() {
        cv_check_admin_referer('manage_options');
        cv_verify_request_nonce('cv_save_settings_action', 'cv_settings_nonce');

        $site_name = sanitize_text_field($_POST['site_name']);
        $logo_url = esc_url_raw($_POST['logo_url']);
        $footer_text = sanitize_textarea_field($_POST['footer_text']);
        $primary_color = sanitize_hex_color($_POST['primary_color']);
        $secondary_color = sanitize_hex_color($_POST['secondary_color']);
        $consultation_btn_text = sanitize_text_field($_POST['consultation_btn_text']);
        $consultation_url = esc_url_raw($_POST['consultation_url']);

        // Sanitize socials list
        $socials = array();
        if (isset($_POST['social']) && is_array($_POST['social'])) {
            foreach ($_POST['social'] as $key => $url) {
                if ($key === 'whatsapp') {
                    $socials[$key] = sanitize_text_field($url);
                } else {
                    $socials[$key] = esc_url_raw($url);
                }
            }
        }

        // Save keys
        cv_save_setting('site_name', $site_name);
        cv_save_setting('logo_url', $logo_url);
        cv_save_setting('footer_text', $footer_text);
        cv_save_setting('primary_color', $primary_color);
        cv_save_setting('secondary_color', $secondary_color);
        cv_save_setting('social_links', $socials);
        cv_save_setting('consultation_btn_text', $consultation_btn_text);
        cv_save_setting('consultation_url', $consultation_url);

        cv_log_activity('Updated global plugin configuration settings');
        cv_clear_shortcode_transients();

        wp_safe_redirect(add_query_arg('message', 'saved', admin_url('admin.php?page=cv-settings')));
        exit;
    }
}
