<?php
/**
 * Plugin Name: Clairvoyant Core
 * Plugin URI: https://yoursite.com
 * Description: Complete astrology dashboard for WordPress
 * Version: 1.1.2
 * Author: Your Name
 * Author URI: https://yoursite.com
 * License: GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages
 * Text Domain: clairvoyant-core
 * Requires at least: 5.9
 * Requires PHP: 8.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main Clairvoyant Core Class
 * 
 * Boots the plugin components, enqueues resources, and mounts routes
 * 
 * @since 1.0.0
 */
class Clairvoyant_Core {

    /**
     * Singleton instance of the class
     * 
     * @var Clairvoyant_Core
     */
    private static $instance = null;

    /**
     * Main class instance retriever
     * 
     * @return Clairvoyant_Core
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor enqueuing hooks
     */
    private function __construct() {
        // Load dependencies
        $this->load_dependencies();

        // Register Activation / Deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        // Hooks for setup
        add_action('plugins_loaded', array($this, 'init_plugin'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
    }

    /**
     * Include core configuration and helpers
     */
    private function load_dependencies() {
        require_once plugin_dir_path(__FILE__) . 'includes/constants.php';
        require_once plugin_dir_path(__FILE__) . 'includes/helpers.php';
        require_once plugin_dir_path(__FILE__) . 'includes/security.php';
        require_once plugin_dir_path(__FILE__) . 'includes/validation.php';
        require_once plugin_dir_path(__FILE__) . 'includes/rest-api.php';

        // Load modules classes
        require_once plugin_dir_path(__FILE__) . 'admin/menu.php';
        require_once plugin_dir_path(__FILE__) . 'modules/daily-rashi/class-daily-rashi.php';
        require_once plugin_dir_path(__FILE__) . 'modules/horoscope/class-horoscope.php';
        require_once plugin_dir_path(__FILE__) . 'modules/testimonials/class-testimonials.php';
        require_once plugin_dir_path(__FILE__) . 'modules/settings/class-settings.php';
        require_once plugin_dir_path(__FILE__) . 'modules/prediction-24h/class-prediction-24h.php';

        // Frontend Loader
        require_once plugin_dir_path(__FILE__) . 'frontend/class-frontend.php';
        require_once plugin_dir_path(__FILE__) . 'frontend/shortcodes.php';
    }

    /**
     * Activates the plugin: installs databases and logs metadata
     */
    public function activate() {
        require_once plugin_dir_path(__FILE__) . 'database/install.php';
        cv_install_database();

        add_option('clairvoyant_installed', current_time('mysql'));
        add_option('clairvoyant_version', CLAIRVOYANT_VERSION);
    }

    /**
     * Deactivates the plugin
     */
    public function deactivate() {
        // Deactivation cleanup if any
    }

    /**
     * Initializes controllers and modules
     */
    public function init_plugin() {
        // Run database upgrade if version changed
        $installed_version = get_option('clairvoyant_version');
        if ($installed_version !== CLAIRVOYANT_VERSION) {
            require_once plugin_dir_path(__FILE__) . 'database/install.php';
            cv_install_database();
            update_option('clairvoyant_version', CLAIRVOYANT_VERSION);
        }

        // Trigger settings, horoscopes, daily rashi, testimonials, REST endpoints, and shortcodes initialization
        if (is_admin()) {
            Clairvoyant_Admin_Menu::get_instance();
            CV_Daily_Rashi::get_instance();
            CV_Horoscope::get_instance();
            CV_Testimonials::get_instance();
            CV_Settings::get_instance();
            CV_Prediction_24h::get_instance();
        }

        // Initialize Frontend
        CV_Frontend::get_instance();
        
        // Initialize REST API Endpoints
        CV_REST_API::get_instance();
    }

    /**
     * Enqueue Admin Stylesheets and Scripts
     * 
     * @param string $hook Current admin screen hook
     */
    public function enqueue_admin_assets($hook) {
        // Enqueue only on clairvoyant admin pages
        if (strpos($hook, 'clairvoyant') === false && strpos($hook, 'cv-') === false) {
            return;
        }

        // Enqueue WordPress Built-in media upload tools and color picker
        wp_enqueue_media();
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');

        // Fonts
        wp_enqueue_style('cv-google-fonts', 'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;0,700;1,400&family=Poppins:wght@300;400;500;600;700&display=swap', array(), null);

        // Core Stylesheets
        wp_enqueue_style('cv-admin-dashboard', CLAIRVOYANT_PLUGIN_URL . 'assets/css/dashboard.css', array(), CLAIRVOYANT_VERSION);
        wp_enqueue_style('cv-admin-forms', CLAIRVOYANT_PLUGIN_URL . 'assets/css/forms.css', array(), CLAIRVOYANT_VERSION);
        wp_enqueue_style('cv-admin-tables', CLAIRVOYANT_PLUGIN_URL . 'assets/css/tables.css', array(), CLAIRVOYANT_VERSION);
        wp_enqueue_style('cv-admin-responsive', CLAIRVOYANT_PLUGIN_URL . 'assets/css/responsive.css', array(), CLAIRVOYANT_VERSION);

        // Core Scripts
        wp_enqueue_script('cv-admin-modals', CLAIRVOYANT_PLUGIN_URL . 'assets/js/modals.js', array(), CLAIRVOYANT_VERSION, true);
        wp_enqueue_script('cv-admin-forms', CLAIRVOYANT_PLUGIN_URL . 'assets/js/forms.js', array('wp-color-picker', 'cv-admin-modals'), CLAIRVOYANT_VERSION, true);
        wp_enqueue_script('cv-admin-tables', CLAIRVOYANT_PLUGIN_URL . 'assets/js/tables.js', array('cv-admin-modals'), CLAIRVOYANT_VERSION, true);
        wp_enqueue_script('cv-admin-dashboard', CLAIRVOYANT_PLUGIN_URL . 'assets/js/dashboard.js', array('cv-admin-modals'), CLAIRVOYANT_VERSION, true);
        
        // Pass translation/nonce details to Javascript
        wp_localize_script('cv-admin-dashboard', 'cvAdmin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('cv_admin_ajax_nonce'),
        ));
    }

    /**
     * Enqueue Frontend Stylesheets and Scripts
     */
    public function enqueue_frontend_assets() {
        wp_enqueue_style('cv-google-fonts', 'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;0,700;1,400&family=Poppins:wght@300;400;500;600;700&display=swap', array(), null);
        
        // Load CSS and JS on frontend
        wp_enqueue_style('cv-frontend-style', CLAIRVOYANT_PLUGIN_URL . 'assets/css/frontend.css', array(), CLAIRVOYANT_VERSION);
        wp_enqueue_script('cv-frontend-script', CLAIRVOYANT_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), CLAIRVOYANT_VERSION, true);
        
        wp_localize_script('cv-frontend-script', 'cvFront', array(
            'rest_url' => esc_url_raw(rest_url('clairvoyant/v1')),
            'nonce'    => wp_create_nonce('wp_rest')
        ));
    }
}

// Bootstrap the plugin
Clairvoyant_Core::get_instance();
