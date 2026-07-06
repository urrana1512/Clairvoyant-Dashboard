<?php
/**
 * Frontend Bootstrap Class
 * 
 * Sets up shortcode handlers, triggers frontend assets, enqueues styles
 * 
 * @package Clairvoyant_Core
 * @subpackage Frontend
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class CV_Frontend {

    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Enqueue shortcodes logic loader
        $this->load_shortcodes();
    }

    /**
     * Includes all shortcodes handlers definitions
     */
    private function load_shortcodes() {
        require_once CLAIRVOYANT_PLUGIN_DIR . 'frontend/shortcodes.php';
    }
}
