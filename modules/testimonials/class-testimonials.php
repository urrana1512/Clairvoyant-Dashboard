<?php
/**
 * Testimonials Module Main Class
 * 
 * @package Clairvoyant_Core
 * @subpackage Testimonials
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class CV_Testimonials {

    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->load_dependencies();
        add_action('admin_menu', array($this, 'register_submenus'), 40);
    }

    private function load_dependencies() {
        require_once CLAIRVOYANT_PLUGIN_DIR . 'modules/testimonials/database.php';
        require_once CLAIRVOYANT_PLUGIN_DIR . 'modules/testimonials/save.php';
        require_once CLAIRVOYANT_PLUGIN_DIR . 'modules/testimonials/delete.php';
    }

    /**
     * Registers submenus under Clairvoyant Core
     */
    public function register_submenus() {
        add_submenu_page(
            'clairvoyant-dashboard',
            __('Add Testimonial', 'clairvoyant-core'),
            __('Add Testimonial', 'clairvoyant-core'),
            'manage_options',
            'cv-testimonials-add',
            array($this, 'render_add_page')
        );

        add_submenu_page(
            'clairvoyant-dashboard',
            __('Manage Testimonials', 'clairvoyant-core'),
            __('Manage Testimonials', 'clairvoyant-core'),
            'manage_options',
            'cv-testimonials-manage',
            array($this, 'render_manage_page')
        );
    }

    public function render_add_page() {
        Clairvoyant_Admin_Menu::render_layout_wrapper(function() {
            require_once CLAIRVOYANT_PLUGIN_DIR . 'modules/testimonials/add.php';
        });
    }

    public function render_manage_page() {
        Clairvoyant_Admin_Menu::render_layout_wrapper(function() {
            require_once CLAIRVOYANT_PLUGIN_DIR . 'modules/testimonials/manage.php';
        });
    }
}
