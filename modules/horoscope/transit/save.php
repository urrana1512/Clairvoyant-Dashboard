<?php
/**
 * Transit Horoscope Module - Save Handler
 * 
 * @package Clairvoyant_Core
 * @subpackage Horoscope
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

function cv_save_transit_horoscope_handler() {
    cv_check_admin_referer('manage_options');
    cv_verify_request_nonce('cv_save_transit_horo_action', 'cv_transit_horo_nonce');

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
        'planet'             => sanitize_text_field($_POST['planet']),
        'transit_start_date' => sanitize_text_field($_POST['transit_start_date']),
        'transit_end_date'   => !empty($_POST['transit_end_date']) ? sanitize_text_field($_POST['transit_end_date']) : null,
        'title'              => sanitize_text_field($_POST['title']),
        'prediction'         => wp_kses_post($_POST['prediction']),
        'affected_signs'     => sanitize_text_field($_POST['affected_signs']),
        'remedies'           => wp_kses_post($_POST['remedies']),
        'status'             => in_array($_POST['status'], array('publish', 'draft')) ? $_POST['status'] : 'draft',
        'scheduled_at'       => $scheduled_val
    );

    $validation = cv_validate_transit_horoscope($fields);
    
    if (!$validation['valid']) {
        $error_key = 'cv_transit_horo_errors_' . get_current_user_id();
        set_transient($error_key, array(
            'errors' => $validation['errors'],
            'inputs' => $_POST
        ), 300);

        $redirect_url = admin_url('admin.php?page=cv-horoscope-transit-add');
        if ($id > 0) {
            $redirect_url = add_query_arg(array('action' => 'edit', 'id' => $id), $redirect_url);
        }
        $redirect_url = add_query_arg('message', 'validation_failed', $redirect_url);
        wp_safe_redirect($redirect_url);
        exit;
    }

    if ($id > 0) {
        $saved = cv_update_transit_horoscope($id, $fields);
        $action_label = 'updated';
        cv_log_activity(sprintf('Updated Transit prediction for %s starting on %s', ucfirst($fields['planet']), $fields['transit_start_date']));
    } else {
        $saved = cv_insert_transit_horoscope($fields);
        $action_label = 'created';
        cv_log_activity(sprintf('Created Transit prediction for %s starting on %s', ucfirst($fields['planet']), $fields['transit_start_date']));
    }

    if ($saved !== false) {
        cv_clear_shortcode_transients();
        wp_safe_redirect(add_query_arg('message', $action_label, admin_url('admin.php?page=cv-horoscope-transit-manage')));
        exit;
    } else {
        wp_die(__('An error occurred while saving the record.', 'clairvoyant-core'));
    }
}
add_action('admin_post_cv_save_transit_horo', 'cv_save_transit_horoscope_handler');
