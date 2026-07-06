<?php
/**
 * Horoscope Module Main Coordinator Class
 * 
 * Boots daily, weekly, and transit horoscope modules
 * 
 * @package Clairvoyant_Core
 * @subpackage Horoscope
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class CV_Horoscope {

    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->load_dependencies();
        add_action('admin_menu', array($this, 'register_submenus'), 35);
    }

    private function load_dependencies() {
        // Daily Submodule
        require_once CLAIRVOYANT_PLUGIN_DIR . 'modules/horoscope/daily/database.php';
        require_once CLAIRVOYANT_PLUGIN_DIR . 'modules/horoscope/daily/save.php';
        require_once CLAIRVOYANT_PLUGIN_DIR . 'modules/horoscope/daily/delete.php';

        // Weekly Submodule
        require_once CLAIRVOYANT_PLUGIN_DIR . 'modules/horoscope/weekly/database.php';
        require_once CLAIRVOYANT_PLUGIN_DIR . 'modules/horoscope/weekly/save.php';
        require_once CLAIRVOYANT_PLUGIN_DIR . 'modules/horoscope/weekly/delete.php';

        // Transit Submodule
        require_once CLAIRVOYANT_PLUGIN_DIR . 'modules/horoscope/transit/database.php';
        require_once CLAIRVOYANT_PLUGIN_DIR . 'modules/horoscope/transit/save.php';
        require_once CLAIRVOYANT_PLUGIN_DIR . 'modules/horoscope/transit/delete.php';
    }

    /**
     * Registers submenus under Clairvoyant Core
     */
    public function register_submenus() {
        // Daily Horoscope Pages
        add_submenu_page(
            'clairvoyant-dashboard',
            __('Add Daily Horoscope', 'clairvoyant-core'),
            __('Add Daily Horoscope', 'clairvoyant-core'),
            'manage_options',
            'cv-horoscope-daily-add',
            array($this, 'render_daily_add_page')
        );
        add_submenu_page(
            'clairvoyant-dashboard',
            __('Manage Daily Horoscope', 'clairvoyant-core'),
            __('Manage Daily Horoscope', 'clairvoyant-core'),
            'manage_options',
            'cv-horoscope-daily-manage',
            array($this, 'render_daily_manage_page')
        );

        // Weekly Horoscope Pages
        add_submenu_page(
            'clairvoyant-dashboard',
            __('Add Weekly Horoscope', 'clairvoyant-core'),
            __('Add Weekly Horoscope', 'clairvoyant-core'),
            'manage_options',
            'cv-horoscope-weekly-add',
            array($this, 'render_weekly_add_page')
        );
        add_submenu_page(
            'clairvoyant-dashboard',
            __('Manage Weekly Horoscope', 'clairvoyant-core'),
            __('Manage Weekly Horoscope', 'clairvoyant-core'),
            'manage_options',
            'cv-horoscope-weekly-manage',
            array($this, 'render_weekly_manage_page')
        );

        // Transit Horoscope Pages
        add_submenu_page(
            'clairvoyant-dashboard',
            __('Add Transit Horoscope', 'clairvoyant-core'),
            __('Add Transit Horoscope', 'clairvoyant-core'),
            'manage_options',
            'cv-horoscope-transit-add',
            array($this, 'render_transit_add_page')
        );
        add_submenu_page(
            'clairvoyant-dashboard',
            __('Manage Transit Horoscope', 'clairvoyant-core'),
            __('Manage Transit Horoscope', 'clairvoyant-core'),
            'manage_options',
            'cv-horoscope-transit-manage',
            array($this, 'render_transit_manage_page')
        );
    }

    /**
     * Renders Daily Add page
     */
    public function render_daily_add_page() {
        Clairvoyant_Admin_Menu::render_layout_wrapper(function() {
            require_once CLAIRVOYANT_PLUGIN_DIR . 'modules/horoscope/daily/add.php';
        });
    }

    /**
     * Renders Daily Manage page
     */
    public function render_daily_manage_page() {
        Clairvoyant_Admin_Menu::render_layout_wrapper(function() {
            require_once CLAIRVOYANT_PLUGIN_DIR . 'modules/horoscope/daily/manage.php';
        });
    }

    /**
     * Renders Weekly Add page
     */
    public function render_weekly_add_page() {
        Clairvoyant_Admin_Menu::render_layout_wrapper(function() {
            require_once CLAIRVOYANT_PLUGIN_DIR . 'modules/horoscope/weekly/add.php';
        });
    }

    /**
     * Renders Weekly Manage page
     */
    public function render_weekly_manage_page() {
        Clairvoyant_Admin_Menu::render_layout_wrapper(function() {
            require_once CLAIRVOYANT_PLUGIN_DIR . 'modules/horoscope/weekly/manage.php';
        });
    }

    /**
     * Renders Transit Add page
     */
    public function render_transit_add_page() {
        Clairvoyant_Admin_Menu::render_layout_wrapper(function() {
            require_once CLAIRVOYANT_PLUGIN_DIR . 'modules/horoscope/transit/add.php';
        });
    }

    /**
     * Renders Transit Manage page
     */
    public function render_transit_manage_page() {
        Clairvoyant_Admin_Menu::render_layout_wrapper(function() {
            require_once CLAIRVOYANT_PLUGIN_DIR . 'modules/horoscope/transit/manage.php';
        });
    }
}
