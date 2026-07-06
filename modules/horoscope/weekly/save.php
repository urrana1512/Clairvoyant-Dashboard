<?php
/**
 * Weekly Horoscope Module - Save Handler
 * 
 * @package Clairvoyant_Core
 * @subpackage Horoscope
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

function cv_save_weekly_horoscope_handler() {
    cv_check_admin_referer('manage_options');
    cv_verify_request_nonce('cv_save_weekly_horo_action', 'cv_weekly_horo_nonce');

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
        'week_start'     => sanitize_text_field($_POST['week_start']),
        'week_end'       => sanitize_text_field($_POST['week_end']),
        'zodiac_sign'    => sanitize_key($_POST['zodiac_sign']),
        'prediction'     => wp_kses_post($_POST['prediction']),
        'career'         => sanitize_text_field($_POST['career']),
        'love'           => sanitize_text_field($_POST['love']),
        'health'         => sanitize_text_field($_POST['health']),
        'money'          => sanitize_text_field($_POST['money']),
        'overall_rating' => isset($_POST['overall_rating']) ? (int) $_POST['overall_rating'] : 0,
        'status'         => in_array($_POST['status'], array('publish', 'draft')) ? $_POST['status'] : 'draft',
        'scheduled_at'   => $scheduled_val
    );

    $validation = cv_validate_weekly_horoscope($fields);
    
    if (!$validation['valid']) {
        $error_key = 'cv_weekly_horo_errors_' . get_current_user_id();
        set_transient($error_key, array(
            'errors' => $validation['errors'],
            'inputs' => $_POST
        ), 300);

        $redirect_url = admin_url('admin.php?page=cv-horoscope-weekly-add');
        if ($id > 0) {
            $redirect_url = add_query_arg(array('action' => 'edit', 'id' => $id), $redirect_url);
        }
        $redirect_url = add_query_arg('message', 'validation_failed', $redirect_url);
        wp_safe_redirect($redirect_url);
        exit;
    }

    if ($id > 0) {
        $saved = cv_update_weekly_horoscope($id, $fields);
        $action_label = 'updated';
        cv_log_activity(sprintf('Updated Weekly Horoscope for %s for week starting %s', ucfirst($fields['zodiac_sign']), $fields['week_start']));
    } else {
        $saved = cv_insert_weekly_horoscope($fields);
        $action_label = 'created';
        cv_log_activity(sprintf('Created Weekly Horoscope for %s for week starting %s', ucfirst($fields['zodiac_sign']), $fields['week_start']));
    }

    if ($saved !== false) {
        cv_clear_shortcode_transients();
        wp_safe_redirect(add_query_arg('message', $action_label, admin_url('admin.php?page=cv-horoscope-weekly-manage')));
        exit;
    } else {
        wp_die(__('An error occurred while saving the record.', 'clairvoyant-core'));
    }
}
add_action('admin_post_cv_save_weekly_horo', 'cv_save_weekly_horoscope_handler');
