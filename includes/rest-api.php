<?php
/**
 * REST API Endpoints Handler
 * 
 * Registers endpoints to expose public astrology and horoscope records as JSON
 * 
 * @package Clairvoyant_Core
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class CV_REST_API {

    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    /**
     * Registers all API routes
     */
    public function register_routes() {
        $namespace = 'clairvoyant/v1';

        // Get daily rashi
        register_rest_route($namespace, '/daily-rashi', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array($this, 'get_daily_rashi_endpoint'),
            'permission_callback' => '__return_true', // public
        ));

        // Get daily horoscope
        register_rest_route($namespace, '/daily-horoscope', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array($this, 'get_daily_horoscope_endpoint'),
            'permission_callback' => '__return_true',
        ));

        // Get weekly horoscope
        register_rest_route($namespace, '/weekly-horoscope', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array($this, 'get_weekly_horoscope_endpoint'),
            'permission_callback' => '__return_true',
        ));

        // Get transit horoscope
        register_rest_route($namespace, '/transit-horoscope', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array($this, 'get_transit_horoscope_endpoint'),
            'permission_callback' => '__return_true',
        ));

        // Get testimonials
        register_rest_route($namespace, '/testimonials', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array($this, 'get_testimonials_endpoint'),
            'permission_callback' => '__return_true',
        ));
    }

    /**
     * Endpoint: GET /clairvoyant/v1/daily-rashi
     */
    public function get_daily_rashi_endpoint($request) {
        $date        = $request->get_param('date');
        $zodiac_sign = $request->get_param('zodiac');
        $limit       = $request->get_param('limit');
        
        $args = array(
            'status'      => 'publish',
            'limit'       => $limit ? (int) $limit : 12,
            'order_by'    => 'date',
            'order'       => 'DESC'
        );

        if (!empty($date)) {
            $args['date'] = sanitize_text_field($date);
        } else {
            // Default to today's date if no date parameter passed
            $args['date'] = current_time('Y-m-d');
        }

        if (!empty($zodiac_sign)) {
            $args['zodiac_sign'] = sanitize_key($zodiac_sign);
        }

        require_once CLAIRVOYANT_PLUGIN_DIR . 'modules/daily-rashi/database.php';
        $results = cv_get_daily_rashis($args);

        // Fallback: If no records for today, query most recent date records
        if (empty($results) && empty($date)) {
            $recent_date = $this->get_latest_record_date('cv_daily_rashi');
            if ($recent_date) {
                $args['date'] = $recent_date;
                $results = cv_get_daily_rashis($args);
            }
        }

        return rest_ensure_response($results);
    }

    /**
     * Endpoint: GET /clairvoyant/v1/daily-horoscope
     */
    public function get_daily_horoscope_endpoint($request) {
        $date        = $request->get_param('date');
        $zodiac_sign = $request->get_param('zodiac');
        $limit       = $request->get_param('limit');

        $args = array(
            'status'      => 'publish',
            'limit'       => $limit ? (int) $limit : 12,
            'order_by'    => 'date',
            'order'       => 'DESC'
        );

        if (!empty($date)) {
            $args['date'] = sanitize_text_field($date);
        } else {
            $args['date'] = current_time('Y-m-d');
        }

        if (!empty($zodiac_sign)) {
            $args['zodiac_sign'] = sanitize_key($zodiac_sign);
        }

        require_once CLAIRVOYANT_PLUGIN_DIR . 'modules/horoscope/daily/database.php';
        $results = cv_get_daily_horoscopes($args);

        if (empty($results) && empty($date)) {
            $recent_date = $this->get_latest_record_date('cv_daily_horoscope');
            if ($recent_date) {
                $args['date'] = $recent_date;
                $results = cv_get_daily_horoscopes($args);
            }
        }

        return rest_ensure_response($results);
    }

    /**
     * Endpoint: GET /clairvoyant/v1/weekly-horoscope
     */
    public function get_weekly_horoscope_endpoint($request) {
        $week_start  = $request->get_param('week_start');
        $zodiac_sign = $request->get_param('zodiac');
        $limit       = $request->get_param('limit');

        $args = array(
            'status'      => 'publish',
            'limit'       => $limit ? (int) $limit : 12,
            'order_by'    => 'week_start',
            'order'       => 'DESC'
        );

        if (!empty($week_start)) {
            $args['week_start'] = sanitize_text_field($week_start);
        }
        if (!empty($zodiac_sign)) {
            $args['zodiac_sign'] = sanitize_key($zodiac_sign);
        }

        require_once CLAIRVOYANT_PLUGIN_DIR . 'modules/horoscope/weekly/database.php';
        $results = cv_get_weekly_horoscopes($args);

        return rest_ensure_response($results);
    }

    /**
     * Endpoint: GET /clairvoyant/v1/transit-horoscope
     */
    public function get_transit_horoscope_endpoint($request) {
        $planet = $request->get_param('planet');
        $limit  = $request->get_param('limit');

        $args = array(
            'status' => 'publish',
            'limit'  => $limit ? (int) $limit : 5
        );

        if (!empty($planet)) {
            $args['planet'] = sanitize_text_field($planet);
        }

        require_once CLAIRVOYANT_PLUGIN_DIR . 'modules/horoscope/transit/database.php';
        $results = cv_get_transit_horoscopes($args);

        return rest_ensure_response($results);
    }

    /**
     * Endpoint: GET /clairvoyant/v1/testimonials
     */
    public function get_testimonials_endpoint($request) {
        $limit = $request->get_param('limit');

        $args = array(
            'status' => 'publish',
            'limit'  => $limit ? (int) $limit : 6
        );

        require_once CLAIRVOYANT_PLUGIN_DIR . 'modules/testimonials/database.php';
        $results = cv_get_testimonials($args);

        return rest_ensure_response($results);
    }

    /**
     * Helper to retrieve latest date from custom table
     */
    private function get_latest_record_date($table_basename) {
        global $wpdb;
        $table_name = $wpdb->prefix . $table_basename;
        
        $sql = $wpdb->prepare(
            "SELECT date FROM $table_name WHERE status = 'publish' AND (scheduled_at IS NULL OR scheduled_at <= %s) ORDER BY date DESC LIMIT 1",
            current_time('mysql')
        );
        return $wpdb->get_var($sql);
    }
}
