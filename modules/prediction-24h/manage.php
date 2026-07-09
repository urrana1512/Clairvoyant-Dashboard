<?php
/**
 * 24-48 Hrs Prediction Module - Manage View Table
 * 
 * Renders records listings, filters, sorting, bulk actions, and pagination
 * 
 * @package Clairvoyant_Core
 * @subpackage Prediction_24h
 * @since 1.2.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// 1. Capture filters, search, sorting and pagination args
$filter_date    = isset($_GET['filter_date']) ? sanitize_text_field($_GET['filter_date']) : '';
$filter_element = isset($_GET['filter_element']) ? sanitize_key($_GET['filter_element']) : '';
$search_query   = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
$paged          = isset($_GET['paged']) ? max(1, (int) $_GET['paged']) : 1;
$limit          = 10;
$offset         = ($paged - 1) * $limit;

$args = array(
    'date'    => $filter_date,
    'element' => $filter_element,
    'search'  => $search_query,
    'limit'   => $limit,
    'offset'  => $offset
);

// 2. Query data
$records = cv_get_predictions_24_48($args);
$total_items = cv_count_predictions_24_48($args);

// 3. Status messages banner
$message_key = isset($_GET['message']) ? sanitize_key($_GET['message']) : '';
$alert_html = '';
if ($message_key) {
    switch ($message_key) {
        case 'created':
            $alert_html = '<div class="cv-alert cv-alert-success">Prediction created successfully!<span class="cv-alert-close">×</span></div>';
            break;
        case 'updated':
            $alert_html = '<div class="cv-alert cv-alert-success">Prediction updated successfully!<span class="cv-alert-close">×</span></div>';
            break;
        case 'deleted':
            $alert_html = '<div class="cv-alert cv-alert-success">Prediction deleted successfully!<span class="cv-alert-close">×</span></div>';
            break;
        case 'bulk_deleted':
            $alert_html = '<div class="cv-alert cv-alert-success">Selected predictions deleted successfully!<span class="cv-alert-close">×</span></div>';
            break;
    }
}

// Base url for sorting & pagination
$base_url = admin_url('admin.php?page=cv-prediction-24h-manage');
if (!empty($filter_date)) $base_url = add_query_arg('filter_date', $filter_date, $base_url);
if (!empty($filter_element)) $base_url = add_query_arg('filter_element', $filter_element, $base_url);
if (!empty($search_query)) $base_url = add_query_arg('s', $search_query, $base_url);

?>

<div class="cv-breadcrumb">
    <a href="<?php echo esc_url(admin_url('admin.php?page=clairvoyant-dashboard')); ?>">Clairvoyant Core</a> &gt; 
    <span>24-48 Hrs Prediction</span>
</div>

<div class="cv-page-title-row">
    <h1 class="cv-page-title"><?php esc_html_e('Manage 24-48 Hrs Predictions', 'clairvoyant-core'); ?></h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=cv-prediction-24h-add')); ?>" class="cv-form-button">Add New Prediction</a>
</div>

<?php echo $alert_html; ?>

<!-- Filters and Search panel -->
<div class="cv-table-container">
    <div class="cv-table-controls">
        <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" class="cv-table-filters">
            <input type="hidden" name="page" value="cv-prediction-24h-manage">
            
            <input type="date" name="filter_date" value="<?php echo esc_attr($filter_date); ?>" class="cv-form-input" style="width: auto;">
            
            <select name="filter_element" class="cv-form-select" style="width: auto;">
                <option value="">-- All Elements --</option>
                <?php foreach (cv_get_element_list() as $key => $el_info) : ?>
                    <option value="<?php echo esc_attr($key); ?>" <?php selected($filter_element, $key); ?>>
                        <?php echo esc_html($el_info['icon'] . ' ' . $el_info['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <button type="submit" class="cv-form-button secondary">Filter</button>
            <?php if (!empty($filter_date) || !empty($filter_element) || !empty($search_query)) : ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=cv-prediction-24h-manage')); ?>" class="cv-form-button secondary">Clear</a>
            <?php endif; ?>
        </form>

        <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" class="cv-table-search">
            <input type="hidden" name="page" value="cv-prediction-24h-manage">
            <?php if (!empty($filter_date)) : ?><input type="hidden" name="filter_date" value="<?php echo esc_attr($filter_date); ?>"><?php endif; ?>
            <?php if (!empty($filter_element)) : ?><input type="hidden" name="filter_element" value="<?php echo esc_attr($filter_element); ?>"><?php endif; ?>
            
            <input type="text" name="s" value="<?php echo esc_attr($search_query); ?>" class="cv-form-input" placeholder="Search predictions...">
            <button type="submit" class="cv-form-button">Search</button>
        </form>
    </div>

    <!-- Bulk Actions Wrapper Form -->
    <form id="cv-bulk-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
        <input type="hidden" name="action" value="cv_bulk_prediction_24h">
        <input type="hidden" name="bulk_action" id="cv-bulk-action-target" value="">
        <?php wp_nonce_field('cv_prediction_24h_bulk_action', 'cv_prediction_24h_bulk_nonce'); ?>

        <table class="cv-table">
            <thead>
                <tr>
                    <th class="cv-checkbox-col"><input type="checkbox" id="cv-select-all"></th>
                    <th>Date</th>
                    <th>Element Group</th>
                    <th>Zodiac Signs Included</th>
                    <th>Created By</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($records)) : ?>
                    <?php foreach ($records as $row) : 
                        $element_info = cv_get_element_info($row->element);
                        $element_label = $element_info ? $element_info['icon'] . ' ' . $element_info['name'] : ucfirst($row->element);
                        $zodiacs_label = $element_info ? implode(', ', array_map(function($z) {
                            $z_info = cv_get_zodiac_info($z);
                            return $z_info ? $z_info['icon'] . ' ' . $z_info['name'] : ucfirst($z);
                        }, $element_info['signs'])) : '';
                        
                        $delete_nonce = wp_create_nonce('cv_delete_prediction_24h_' . $row->id);
                        $delete_url = add_query_arg(array(
                            'action' => 'cv_delete_prediction_24h',
                            'id'     => $row->id,
                            'nonce'  => $delete_nonce
                        ), admin_url('admin-post.php'));
                        ?>
                        <tr>
                            <td class="cv-checkbox-col" data-label="Select">
                                <input type="checkbox" name="ids[]" value="<?php echo esc_attr($row->id); ?>" class="cv-row-select">
                            </td>
                            <td data-label="Date"><strong><?php echo esc_html(cv_format_date($row->date)); ?></strong></td>
                            <td data-label="Element Group"><strong><?php echo esc_html($element_label); ?></strong></td>
                            <td data-label="Zodiac Signs Included" style="font-size:12px; color:#555;"><?php echo esc_html($zodiacs_label); ?></td>
                            <td data-label="Created By"><?php echo esc_html($row->creator_name ? $row->creator_name : 'System'); ?></td>
                            <td data-label="Status">
                                <?php 
                                echo cv_get_status_badge($row->status); 
                                if ($row->status === 'publish' && !empty($row->scheduled_at) && $row->scheduled_at !== '0000-00-00 00:00:00') {
                                    $sched_time = strtotime($row->scheduled_at);
                                    if ($sched_time > current_time('timestamp')) {
                                        echo '<div class="cv-table-scheduled-meta">Scheduled for: ' . esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $sched_time)) . '</div>';
                                    }
                                }
                                ?>
                            </td>
                            <td data-label="Actions">
                                <div class="cv-table-actions-menu-wrapper">
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=cv-prediction-24h-add&action=edit&id=' . $row->id)); ?>" class="cv-table-action-btn edit-btn" title="Edit">📝</a>
                                    <a href="<?php echo esc_url($delete_url); ?>" class="cv-table-action-btn delete-btn cv-delete-record-btn" data-name="<?php echo esc_attr($element_info ? $element_info['name'] : $row->element); ?> Signs Prediction for <?php echo esc_attr(cv_format_date($row->date)); ?>" title="Delete">🗑️</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="7" class="cv-table-empty">No predictions found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </form>

    <!-- Table Footer controls -->
    <div class="cv-table-footer">
        <div></div>
        <?php echo cv_render_pagination($total_items, $limit, $paged, $base_url); ?>
    </div>
</div>

<div class="cv-bulk-actions-bar" id="cv-bulk-bar">
    <span class="cv-bulk-selected-count"><span id="cv-selected-count">0</span> items selected</span>
    <button type="button" class="cv-form-button danger cv-bulk-trigger-btn" data-action="delete">Delete Selected</button>
</div>
