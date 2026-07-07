<?php
/**
 * Shortcodes Hooks Implementation
 * 
 * Implements, renders, and caches all frontend shortcode widgets
 * 
 * @package Clairvoyant_Core
 * @subpackage Frontend
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shortcode 1: [cv_daily_rashi]
 */
function cv_daily_rashi_shortcode($atts) {
    $atts = shortcode_atts(array(
        'date'   => '',
        'zodiac' => '',
        'limit'  => 12
    ), $atts, 'cv_daily_rashi');

    $transient_key = 'cv_shortcode_rashi_v2_' . md5(json_encode($atts));
    $html = get_transient($transient_key);

    if (false === $html) {
        ob_start();
        require CLAIRVOYANT_PLUGIN_DIR . 'frontend/templates/daily-rashi-template.php';
        $html = ob_get_clean();
        set_transient($transient_key, $html, HOUR_IN_SECONDS);
    }

    return $html;
}
add_shortcode('cv_daily_rashi', 'cv_daily_rashi_shortcode');

/**
 * Shortcode 2: [cv_daily_horoscope]
 */
function cv_daily_horoscope_shortcode($atts) {
    $atts = shortcode_atts(array(
        'date'   => '',
        'zodiac' => ''
    ), $atts, 'cv_daily_horoscope');

    $atts['default_tab'] = 'today';
    $transient_key = 'cv_shortcode_daily_horo_v2_' . md5(json_encode($atts));
    $html = get_transient($transient_key);

    if (false === $html) {
        ob_start();
        require CLAIRVOYANT_PLUGIN_DIR . 'frontend/templates/horoscope-tabs-template.php';
        $html = ob_get_clean();
        set_transient($transient_key, $html, HOUR_IN_SECONDS);
    }

    return $html;
}
add_shortcode('cv_daily_horoscope', 'cv_daily_horoscope_shortcode');

/**
 * Shortcode 3: [cv_weekly_horoscope]
 */
function cv_weekly_horoscope_shortcode($atts) {
    $atts = shortcode_atts(array(
        'week_start' => '',
        'zodiac'     => ''
    ), $atts, 'cv_weekly_horoscope');

    $atts['default_tab'] = 'weekly';
    $transient_key = 'cv_shortcode_weekly_horo_v2_' . md5(json_encode($atts));
    $html = get_transient($transient_key);

    if (false === $html) {
        ob_start();
        require CLAIRVOYANT_PLUGIN_DIR . 'frontend/templates/horoscope-tabs-template.php';
        $html = ob_get_clean();
        set_transient($transient_key, $html, HOUR_IN_SECONDS);
    }

    return $html;
}
add_shortcode('cv_weekly_horoscope', 'cv_weekly_horoscope_shortcode');

/**
 * Shortcode 4: [cv_transit_horoscope]
 */
function cv_transit_horoscope_shortcode($atts) {
    $atts = shortcode_atts(array(
        'limit' => 5
    ), $atts, 'cv_transit_horoscope');

    $atts['default_tab'] = 'transit';
    $transient_key = 'cv_shortcode_transit_v2_' . md5(json_encode($atts));
    $html = get_transient($transient_key);

    if (false === $html) {
        ob_start();
        require CLAIRVOYANT_PLUGIN_DIR . 'frontend/templates/horoscope-tabs-template.php';
        $html = ob_get_clean();
        set_transient($transient_key, $html, HOUR_IN_SECONDS);
    }

    return $html;
}
add_shortcode('cv_transit_horoscope', 'cv_transit_horoscope_shortcode');

/**
 * Shortcode 4b: [cv_horoscope] (Unified tabs widget)
 */
function cv_horoscope_unified_shortcode($atts) {
    $atts = shortcode_atts(array(
        'default_tab' => 'today',
        'limit'       => 5
    ), $atts, 'cv_horoscope');

    $transient_key = 'cv_shortcode_unified_horo_v2_' . md5(json_encode($atts));
    $html = get_transient($transient_key);

    if (false === $html) {
        ob_start();
        require CLAIRVOYANT_PLUGIN_DIR . 'frontend/templates/horoscope-tabs-template.php';
        $html = ob_get_clean();
        set_transient($transient_key, $html, HOUR_IN_SECONDS);
    }

    return $html;
}
add_shortcode('cv_horoscope', 'cv_horoscope_unified_shortcode');

/**
 * Shortcode 5: [cv_testimonials]
 */
function cv_testimonials_shortcode($atts) {
    $atts = shortcode_atts(array(
        'limit' => 6,
        'style' => 'grid' // grid or carousel
    ), $atts, 'cv_testimonials');

    $transient_key = 'cv_shortcode_testimonials_v2_' . md5(json_encode($atts));
    $html = get_transient($transient_key);

    if (false === $html) {
        ob_start();
        require CLAIRVOYANT_PLUGIN_DIR . 'frontend/templates/testimonials-template.php';
        $html = ob_get_clean();
        set_transient($transient_key, $html, HOUR_IN_SECONDS);
    }

    return $html;
}
add_shortcode('cv_testimonials', 'cv_testimonials_shortcode');
