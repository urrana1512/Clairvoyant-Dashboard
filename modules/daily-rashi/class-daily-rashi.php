<?php
/**
 * Daily Rashi Module Main Class
 * 
 * Boots the daily rashi submenus, database loader, and page view triggers
 * 
 * @package Clairvoyant_Core
 * @subpackage Daily_Rashi
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class CV_Daily_Rashi {

    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Load database functions and save/delete handlers
        $this->load_dependencies();

        // Register submenus
        add_action('admin_menu', array($this, 'register_submenus'), 30);
    }

    /**
     * Load core module dependencies
     */
    private function load_dependencies() {
        require_once CLAIRVOYANT_PLUGIN_DIR . 'modules/daily-rashi/database.php';
        require_once CLAIRVOYANT_PLUGIN_DIR . 'modules/daily-rashi/save.php';
        require_once CLAIRVOYANT_PLUGIN_DIR . 'modules/daily-rashi/delete.php';
    }

    /**
     * Registers menu endpoints
     */
    public function register_submenus() {
        // Hidden / visible submenus registered under the parent slug
        add_submenu_page(
            'clairvoyant-dashboard',
            __('Add Daily Rashi', 'clairvoyant-core'),
            __('Add Daily Rashi', 'clairvoyant-core'),
            'manage_options',
            'cv-daily-rashi-add',
            array($this, 'render_add_page')
        );

        add_submenu_page(
            'clairvoyant-dashboard',
            __('Manage Daily Rashi', 'clairvoyant-core'),
            __('Manage Daily Rashi', 'clairvoyant-core'),
            'manage_options',
            'cv-daily-rashi-manage',
            array($this, 'render_manage_page')
        );
    }

    /**
     * Renders add form wrapped in dashboard layout
     */
    public function render_add_page() {
        Clairvoyant_Admin_Menu::render_layout_wrapper(function() {
            require_once CLAIRVOYANT_PLUGIN_DIR . 'modules/daily-rashi/add.php';
        });
    }

    /**
     * Renders manage list table wrapped in dashboard layout
     */
    public function render_manage_page() {
        Clairvoyant_Admin_Menu::render_layout_wrapper(function() {
            require_once CLAIRVOYANT_PLUGIN_DIR . 'modules/daily-rashi/manage.php';
        });
    }
}
