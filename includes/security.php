<?php
/**
 * Security & Sanitization Helpers
 * 
 * Verifies nonces, capability checks, and data sanitization
 * 
 * @package Clairvoyant_Core
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Verify admin capability and die if unauthorized
 * 
 * @param string $capability The capability required
 */
function cv_check_admin_referer($capability = 'manage_options') {
    if (!current_user_can($capability)) {
        wp_die(
            esc_html__('You do not have sufficient permissions to access this page.', 'clairvoyant-core'),
            esc_html__('Unauthorized Access', 'clairvoyant-core'),
            array('response' => 403)
        );
    }
}

/**
 * Verify nonce and die if invalid
 * 
 * @param string $nonce_action Nonce action name
 * @param string $nonce_field Nonce field key in request
 */
function cv_verify_request_nonce($nonce_action, $nonce_field) {
    if (
        !isset($_REQUEST[$nonce_field]) || 
        !wp_verify_nonce($_REQUEST[$nonce_field], $nonce_action)
    ) {
        wp_die(
            esc_html__('Security check failed. Please refresh the page and try again.', 'clairvoyant-core'),
            esc_html__('Invalid Nonce', 'clairvoyant-core'),
            array('response' => 403)
        );
    }
}

/**
 * Sanitize recursive inputs
 * 
 * @param mixed $data Data to sanitize
 * @param array $allowed_html Optional HTML tag rules for kses
 * @return mixed Sanitized data
 */
function cv_sanitize_input($data, $allowed_html = null) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = cv_sanitize_input($value, $allowed_html);
        }
        return $data;
    }
    
    if (is_string($data)) {
        if ($allowed_html !== null) {
            return wp_kses($data, $allowed_html);
        }
        return sanitize_text_field($data);
    }
    
    return $data;
}
