<?php
/**
 * 24-48 Hrs Prediction Module Main Class
 * 
 * Boots the 24-48 Hrs prediction submenus, database loader, and page view triggers
 * 
 * @package Clairvoyant_Core
 * @subpackage Prediction_24h
 * @since 1.2.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class CV_Prediction_24h {

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
        add_action('admin_menu', array($this, 'register_submenus'), 38);
    }

    /**
     * Load core module dependencies
     */
    private function load_dependencies() {
        require_once CLAIRVOYANT_PLUGIN_DIR . 'modules/prediction-24h/database.php';
        require_once CLAIRVOYANT_PLUGIN_DIR . 'modules/prediction-24h/save.php';
        require_once CLAIRVOYANT_PLUGIN_DIR . 'modules/prediction-24h/delete.php';
    }

    /**
     * Registers menu endpoints under parent Clairvoyant Core menu
     */
    public function register_submenus() {
        add_submenu_page(
            'clairvoyant-dashboard',
            __('Add 24-48 Hrs Prediction', 'clairvoyant-core'),
            __('Add 24-48 Hrs Prediction', 'clairvoyant-core'),
            'manage_options',
            'cv-prediction-24h-add',
            array($this, 'render_add_page')
        );

        add_submenu_page(
            'clairvoyant-dashboard',
            __('Manage 24-48 Hrs Predictions', 'clairvoyant-core'),
            __('Manage 24-48 Hrs Predictions', 'clairvoyant-core'),
            'manage_options',
            'cv-prediction-24h-manage',
            array($this, 'render_manage_page')
        );
    }

    /**
     * Renders add form wrapped in dashboard layout
     */
    public function render_add_page() {
        Clairvoyant_Admin_Menu::render_layout_wrapper(function() {
            require_once CLAIRVOYANT_PLUGIN_DIR . 'modules/prediction-24h/add.php';
        });
    }

    /**
     * Renders manage list table wrapped in dashboard layout
     */
    public function render_manage_page() {
        Clairvoyant_Admin_Menu::render_layout_wrapper(function() {
            require_once CLAIRVOYANT_PLUGIN_DIR . 'modules/prediction-24h/manage.php';
        });
    }
}
