<?php
/**
 * Daily Rashi Module - Manage View Table
 * 
 * Renders records listings, filters, sorting, bulk actions, and pagination
 * 
 * @package Clairvoyant_Core
 * @subpackage Daily_Rashi
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// 1. Capture filters, search, sorting and pagination args
$filter_date   = isset($_GET['filter_date']) ? sanitize_text_field($_GET['filter_date']) : '';
$filter_zodiac = isset($_GET['filter_zodiac']) ? sanitize_key($_GET['filter_zodiac']) : '';
$search_query  = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
$paged          = isset($_GET['paged']) ? max(1, (int) $_GET['paged']) : 1;
$limit         = 10;
$offset        = ($paged - 1) * $limit;

$args = array(
    'date'        => $filter_date,
    'zodiac_sign' => $filter_zodiac,
    'search'      => $search_query,
    'limit'       => $limit,
    'offset'      => $offset
);

// 2. Query data
$records = cv_get_daily_rashis($args);
$total_items = cv_count_daily_rashis($args);

// 3. Status messages banner
$message_key = isset($_GET['message']) ? sanitize_key($_GET['message']) : '';
$alert_html = '';
if ($message_key) {
    switch ($message_key) {
        case 'created':
            $alert_html = '<div class="cv-alert cv-alert-success">Daily Rashi prediction created successfully!<span class="cv-alert-close">×</span></div>';
            break;
        case 'updated':
            $alert_html = '<div class="cv-alert cv-alert-success">Daily Rashi prediction updated successfully!<span class="cv-alert-close">×</span></div>';
            break;
        case 'deleted':
            $alert_html = '<div class="cv-alert cv-alert-success">Daily Rashi prediction deleted successfully!<span class="cv-alert-close">×</span></div>';
            break;
        case 'bulk_deleted':
            $alert_html = '<div class="cv-alert cv-alert-success">Selected predictions deleted successfully!<span class="cv-alert-close">×</span></div>';
            break;
    }
}

// Base url for sorting & pagination
$base_url = admin_url('admin.php?page=cv-daily-rashi-manage');
if (!empty($filter_date)) $base_url = add_query_arg('filter_date', $filter_date, $base_url);
if (!empty($filter_zodiac)) $base_url = add_query_arg('filter_zodiac', $filter_zodiac, $base_url);
if (!empty($search_query)) $base_url = add_query_arg('s', $search_query, $base_url);

?>

<div class="cv-breadcrumb">
    <a href="<?php echo esc_url(admin_url('admin.php?page=clairvoyant-dashboard')); ?>">Clairvoyant Core</a> &gt; 
    <span>Daily Rashi Fal</span>
</div>

<div class="cv-page-title-row">
    <h1 class="cv-page-title"><?php esc_html_e('Manage Daily Rashi Fal', 'clairvoyant-core'); ?></h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=cv-daily-rashi-add')); ?>" class="cv-form-button">Add New Prediction</a>
</div>

<?php echo $alert_html; ?>

<!-- Filters and Search panel -->
<div class="cv-table-container">
    <div class="cv-table-controls">
        <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" class="cv-table-filters">
            <input type="hidden" name="page" value="cv-daily-rashi-manage">
            
            <input type="date" name="filter_date" value="<?php echo esc_attr($filter_date); ?>" class="cv-form-input" style="width: auto;">
            
            <select name="filter_zodiac" class="cv-form-select" style="width: auto;">
                <option value="">-- All Zodiacs --</option>
                <?php foreach (cv_get_zodiac_list() as $key => $sign) : ?>
                    <option value="<?php echo esc_attr($key); ?>" <?php selected($filter_zodiac, $key); ?>>
                        <?php echo esc_html($sign['icon'] . ' ' . $sign['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <button type="submit" class="cv-form-button secondary">Filter</button>
            <?php if (!empty($filter_date) || !empty($filter_zodiac) || !empty($search_query)) : ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=cv-daily-rashi-manage')); ?>" class="cv-form-button secondary">Clear</a>
            <?php endif; ?>
        </form>

        <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" class="cv-table-search">
            <input type="hidden" name="page" value="cv-daily-rashi-manage">
            <?php if (!empty($filter_date)) : ?><input type="hidden" name="filter_date" value="<?php echo esc_attr($filter_date); ?>"><?php endif; ?>
            <?php if (!empty($filter_zodiac)) : ?><input type="hidden" name="filter_zodiac" value="<?php echo esc_attr($filter_zodiac); ?>"><?php endif; ?>
            
            <input type="text" name="s" value="<?php echo esc_attr($search_query); ?>" class="cv-form-input" placeholder="Search predictions...">
            <button type="submit" class="cv-form-button">Search</button>
        </form>
    </div>

    <!-- Bulk Actions Wrapper Form -->
    <form id="cv-bulk-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
        <input type="hidden" name="action" value="cv_bulk_daily_rashi">
        <input type="hidden" name="bulk_action" id="cv-bulk-action-target" value="">
        <?php wp_nonce_field('cv_rashi_bulk_action', 'cv_rashi_bulk_nonce'); ?>

        <table class="cv-table">
            <thead>
                <tr>
                    <th class="cv-checkbox-col"><input type="checkbox" id="cv-select-all"></th>
                    <th>Date</th>
                    <th>Zodiac Sign</th>
                    <th>Created By</th>
                    <th>Status</th>
                    <th>Rating</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($records)) : ?>
                    <?php foreach ($records as $row) : 
                        $zodiac_info = cv_get_zodiac_info($row->zodiac_sign);
                        $zodiac_label = $zodiac_info ? $zodiac_info['icon'] . ' ' . $zodiac_info['name'] : ucfirst($row->zodiac_sign);
                        $delete_nonce = wp_create_nonce('cv_delete_rashi_' . $row->id);
                        $delete_url = add_query_arg(array(
                            'action' => 'cv_delete_daily_rashi',
                            'id'     => $row->id,
                            'nonce'  => $delete_nonce
                        ), admin_url('admin-post.php'));
                        ?>
                        <tr>
                            <td class="cv-checkbox-col" data-label="Select">
                                <input type="checkbox" name="ids[]" value="<?php echo esc_attr($row->id); ?>" class="cv-row-select">
                            </td>
                            <td data-label="Date"><strong><?php echo esc_html(cv_format_date($row->date)); ?></strong></td>
                            <td data-label="Zodiac Sign"><?php echo esc_html($zodiac_label); ?></td>
                            <td data-label="Created By"><?php echo esc_html($row->creator_name ? $row->creator_name : 'System'); ?></td>
                            <td data-label="Status">
                                <?php 
                                echo cv_get_status_badge($row->status); 
                                if ($row->status === 'publish' && !empty($row->scheduled_at) && $row->scheduled_at !== '0000-00-00 00:00:00') {
                                    $sched_time = strtotime($row->scheduled_at);
                                    if ($sched_time > current_time('timestamp')) {
                                        echo '<br><span style="font-size:10px; color:#C8A96A; font-weight:500;">Scheduled for ' . esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $sched_time)) . '</span>';
                                    } else {
                                        echo '<br><span style="font-size:10px; color:#888; font-weight:500;">Went live ' . esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $sched_time)) . '</span>';
                                    }
                                }
                                ?>
                            </td>
                            <td data-label="Rating"><?php echo cv_render_star_rating($row->today_luck_rating); ?></td>
                            <td data-label="Actions">
                                <div class="cv-table-actions">
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=cv-daily-rashi-add&action=edit&id=' . $row->id)); ?>" class="cv-table-action-btn edit-btn" title="Edit">📝</a>
                                    <a href="<?php echo esc_url($delete_url); ?>" class="cv-table-action-btn delete-btn cv-delete-record-btn" data-name="<?php echo esc_attr(ucfirst($row->zodiac_sign) . ' for ' . cv_format_date($row->date)); ?>" title="Delete">🗑️</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 32px;" class="description">
                            No records found.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </form>

    <!-- Pagination Links -->
    <?php echo cv_render_pagination($total_items, $limit, $paged, $base_url); ?>
</div>

<!-- Floating Bulk Actions bar (Slid in/out by tables.js) -->
<div class="cv-bulk-actions-bar" id="cv-bulk-bar">
    <span class="cv-bulk-selected-count"><span id="cv-selected-count">0</span> items selected</span>
    <button type="button" class="cv-form-button danger cv-bulk-trigger-btn" data-action="delete">Delete Selected</button>
</div>
