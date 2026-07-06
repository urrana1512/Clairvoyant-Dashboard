<?php
/**
 * Daily Horoscope Module - Save Handler
 * 
 * Processes form submissions for creating and updating daily horoscope records
 * 
 * @package Clairvoyant_Core
 * @subpackage Horoscope
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

function cv_save_daily_horoscope_handler() {
    cv_check_admin_referer('manage_options');
    cv_verify_request_nonce('cv_save_daily_horo_action', 'cv_daily_horo_nonce');

    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    
    $scheduled_at = !empty($_POST['scheduled_at']) ? sanitize_text_field($_POST['scheduled_at']) : '';
    $scheduled_val = null;
    if (!empty($scheduled_at)) {
        $ts = strtotime($scheduled_at);
        if ($ts !== false) {
            $scheduled_val = date('Y-m-d H:i:s', $ts);
        }
    }
    
    $fields = array(
        'date'         => sanitize_text_field($_POST['date']),
        'zodiac_sign'  => sanitize_key($_POST['zodiac_sign']),
        'prediction'   => wp_kses_post($_POST['prediction']),
        'career'       => sanitize_text_field($_POST['career']),
        'love'         => sanitize_text_field($_POST['love']),
        'health'       => sanitize_text_field($_POST['health']),
        'money'        => sanitize_text_field($_POST['money']),
        'lucky_number' => sanitize_text_field($_POST['lucky_number']),
        'lucky_color'  => sanitize_hex_color($_POST['lucky_color']),
        'today_rating' => isset($_POST['today_rating']) ? (int) $_POST['today_rating'] : 0,
        'status'       => in_array($_POST['status'], array('publish', 'draft')) ? $_POST['status'] : 'draft',
        'scheduled_at' => $scheduled_val
    );

    $validation = cv_validate_daily_horoscope($fields);
    
    if (!$validation['valid']) {
        $error_key = 'cv_daily_horo_errors_' . get_current_user_id();
        set_transient($error_key, array(
            'errors' => $validation['errors'],
            'inputs' => $_POST
        ), 300);

        $redirect_url = admin_url('admin.php?page=cv-horoscope-daily-add');
        if ($id > 0) {
            $redirect_url = add_query_arg(array('action' => 'edit', 'id' => $id), $redirect_url);
        }
        $redirect_url = add_query_arg('message', 'validation_failed', $redirect_url);
        wp_safe_redirect($redirect_url);
        exit;
    }

    if ($id > 0) {
        global $wpdb;
        $dup = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}cv_daily_horoscope WHERE date = %s AND zodiac_sign = %s AND id != %d",
            $fields['date'], $fields['zodiac_sign'], $id
        ));

        if ($dup) {
            $error_key = 'cv_daily_horo_errors_' . get_current_user_id();
            set_transient($error_key, array(
                'errors' => array('zodiac_sign' => __('A daily horoscope for this Zodiac Sign on this date already exists.', 'clairvoyant-core')),
                'inputs' => $_POST
            ), 300);

            $redirect_url = add_query_arg(array('action' => 'edit', 'id' => $id, 'message' => 'validation_failed'), admin_url('admin.php?page=cv-horoscope-daily-add'));
            wp_safe_redirect($redirect_url);
            exit;
        }

        $saved = cv_update_daily_horoscope($id, $fields);
        $action_label = 'updated';
        cv_log_activity(sprintf('Updated Daily Horoscope for %s on %s', ucfirst($fields['zodiac_sign']), $fields['date']));
    } else {
        global $wpdb;
        $dup = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}cv_daily_horoscope WHERE date = %s AND zodiac_sign = %s",
            $fields['date'], $fields['zodiac_sign']
        ));

        if ($dup) {
            $error_key = 'cv_daily_horo_errors_' . get_current_user_id();
            set_transient($error_key, array(
                'errors' => array('zodiac_sign' => __('A daily horoscope for this Zodiac Sign on this date already exists.', 'clairvoyant-core')),
                'inputs' => $_POST
            ), 300);

            $redirect_url = add_query_arg(array('message' => 'validation_failed'), admin_url('admin.php?page=cv-horoscope-daily-add'));
            wp_safe_redirect($redirect_url);
            exit;
        }

        $saved = cv_insert_daily_horoscope($fields);
        $action_label = 'created';
        cv_log_activity(sprintf('Created Daily Horoscope for %s on %s', ucfirst($fields['zodiac_sign']), $fields['date']));
    }

    if ($saved !== false) {
        cv_clear_shortcode_transients();
        wp_safe_redirect(add_query_arg('message', $action_label, admin_url('admin.php?page=cv-horoscope-daily-manage')));
        exit;
    } else {
        wp_die(__('An error occurred while saving the record.', 'clairvoyant-core'));
    }
}
add_action('admin_post_cv_save_daily_horo', 'cv_save_daily_horoscope_handler');
