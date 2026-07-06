<?php
/**
 * Daily Rashi Module - Save Handler
 * 
 * Processes form submissions for creating and updating daily rashi records
 * 
 * @package Clairvoyant_Core
 * @subpackage Daily_Rashi
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handle saving daily rashi record
 */
function cv_save_daily_rashi_handler() {
    // 1. Verify capability and nonce
    cv_check_admin_referer('manage_options');
    cv_verify_request_nonce('cv_save_daily_rashi_action', 'cv_daily_rashi_nonce');

    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    
    $scheduled_at = !empty($_POST['scheduled_at']) ? sanitize_text_field($_POST['scheduled_at']) : '';
    $scheduled_val = null;
    if (!empty($scheduled_at)) {
        $ts = strtotime($scheduled_at);
        if ($ts !== false) {
            $scheduled_val = date('Y-m-d H:i:s', $ts);
        }
    }
    
    // 2. Extract & Sanitize fields
    $fields = array(
        'date'              => sanitize_text_field($_POST['date']),
        'zodiac_sign'       => sanitize_key($_POST['zodiac_sign']),
        'prediction'        => wp_kses_post($_POST['prediction']), // Keep rich text
        'lucky_number'      => sanitize_text_field($_POST['lucky_number']),
        'lucky_color'       => sanitize_hex_color($_POST['lucky_color']),
        'today_luck_rating' => isset($_POST['today_luck_rating']) ? (int) $_POST['today_luck_rating'] : 0,
        'career'            => sanitize_text_field($_POST['career']),
        'love'              => sanitize_text_field($_POST['love']),
        'health'            => sanitize_text_field($_POST['health']),
        'finance'           => sanitize_text_field($_POST['finance']),
        'status'            => in_array($_POST['status'], array('publish', 'draft')) ? $_POST['status'] : 'draft',
        'scheduled_at'      => $scheduled_val
    );

    // 3. Validate
    $validation = cv_validate_daily_rashi($fields);
    
    if (!$validation['valid']) {
        // Validation failed: cache values & errors in transient and redirect back
        $error_key = 'cv_rashi_errors_' . get_current_user_id();
        set_transient($error_key, array(
            'errors' => $validation['errors'],
            'inputs' => $_POST
        ), 300); // 5 mins cache

        $redirect_url = admin_url('admin.php?page=cv-daily-rashi-add');
        if ($id > 0) {
            $redirect_url = add_query_arg(array('action' => 'edit', 'id' => $id), $redirect_url);
        }
        $redirect_url = add_query_arg('message', 'validation_failed', $redirect_url);
        wp_safe_redirect($redirect_url);
        exit;
    }

    $current_user_id = get_current_user_id();

    // 4. Save to Database
    if ($id > 0) {
        // Edit flow
        $fields['updated_by'] = $current_user_id;
        
        // Check if unique key (date, zodiac_sign) isn't violated by another record
        global $wpdb;
        $dup = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}cv_daily_rashi WHERE date = %s AND zodiac_sign = %s AND id != %d",
            $fields['date'], $fields['zodiac_sign'], $id
        ));

        if ($dup) {
            $error_key = 'cv_rashi_errors_' . get_current_user_id();
            set_transient($error_key, array(
                'errors' => array('zodiac_sign' => __('A prediction for this Zodiac Sign on this date already exists.', 'clairvoyant-core')),
                'inputs' => $_POST
            ), 300);

            $redirect_url = add_query_arg(array('action' => 'edit', 'id' => $id, 'message' => 'validation_failed'), admin_url('admin.php?page=cv-daily-rashi-add'));
            wp_safe_redirect($redirect_url);
            exit;
        }

        $saved = cv_update_daily_rashi($id, $fields);
        $action_label = 'updated';
        cv_log_activity(sprintf('Updated Daily Rashi for %s on %s', ucfirst($fields['zodiac_sign']), $fields['date']));
    } else {
        // Add flow
        $fields['created_by'] = $current_user_id;
        $fields['updated_by'] = $current_user_id;

        global $wpdb;
        $dup = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}cv_daily_rashi WHERE date = %s AND zodiac_sign = %s",
            $fields['date'], $fields['zodiac_sign']
        ));

        if ($dup) {
            $error_key = 'cv_rashi_errors_' . get_current_user_id();
            set_transient($error_key, array(
                'errors' => array('zodiac_sign' => __('A prediction for this Zodiac Sign on this date already exists.', 'clairvoyant-core')),
                'inputs' => $_POST
            ), 300);

            $redirect_url = add_query_arg('message', 'validation_failed', admin_url('admin.php?page=cv-daily-rashi-add'));
            wp_safe_redirect($redirect_url);
            exit;
        }

        $saved = cv_insert_daily_rashi($fields);
        $action_label = 'created';
        cv_log_activity(sprintf('Created Daily Rashi for %s on %s', ucfirst($fields['zodiac_sign']), $fields['date']));
    }

    if ($saved !== false) {
        cv_clear_shortcode_transients();
        wp_safe_redirect(add_query_arg('message', $action_label, admin_url('admin.php?page=cv-daily-rashi-manage')));
        exit;
    } else {
        wp_die(__('An error occurred while saving the record. Please try again.', 'clairvoyant-core'));
    }
}
add_action('admin_post_cv_save_daily_rashi', 'cv_save_daily_rashi_handler');
