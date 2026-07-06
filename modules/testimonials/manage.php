<?php
/**
 * Testimonials Module - Manage View Table
 * 
 * @package Clairvoyant_Core
 * @subpackage Testimonials
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$search_query  = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
$paged          = isset($_GET['paged']) ? max(1, (int) $_GET['paged']) : 1;
$limit         = 10;
$offset        = ($paged - 1) * $limit;

$args = array(
    'search' => $search_query,
    'limit'  => $limit,
    'offset' => $offset
);

$records = cv_get_testimonials($args);
$total_items = cv_count_testimonials($args);

$message_key = isset($_GET['message']) ? sanitize_key($_GET['message']) : '';
$alert_html = '';
if ($message_key) {
    switch ($message_key) {
        case 'created':
            $alert_html = '<div class="cv-alert cv-alert-success">Testimonial created successfully!<span class="cv-alert-close">×</span></div>';
            break;
        case 'updated':
            $alert_html = '<div class="cv-alert cv-alert-success">Testimonial updated successfully!<span class="cv-alert-close">×</span></div>';
            break;
        case 'deleted':
            $alert_html = '<div class="cv-alert cv-alert-success">Testimonial deleted successfully!<span class="cv-alert-close">×</span></div>';
            break;
        case 'bulk_deleted':
            $alert_html = '<div class="cv-alert cv-alert-success">Selected testimonials deleted successfully!<span class="cv-alert-close">×</span></div>';
            break;
    }
}

$base_url = admin_url('admin.php?page=cv-testimonials-manage');
if (!empty($search_query)) $base_url = add_query_arg('s', $search_query, $base_url);

?>

<div class="cv-breadcrumb">
    <a href="<?php echo esc_url(admin_url('admin.php?page=clairvoyant-dashboard')); ?>">Clairvoyant Core</a> &gt; 
    <span>Testimonials</span>
</div>

<div class="cv-page-title-row">
    <h1 class="cv-page-title"><?php esc_html_e('Manage Testimonials', 'clairvoyant-core'); ?></h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=cv-testimonials-add')); ?>" class="cv-form-button">Add New Testimonial</a>
</div>

<?php echo $alert_html; ?>

<div class="cv-table-container">
    <div class="cv-table-controls" style="justify-content: flex-end;">
        <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" class="cv-table-search">
            <input type="hidden" name="page" value="cv-testimonials-manage">
            <input type="text" name="s" value="<?php echo esc_attr($search_query); ?>" class="cv-form-input" placeholder="Search client reviews...">
            <button type="submit" class="cv-form-button">Search</button>
        </form>
    </div>

    <form id="cv-bulk-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
        <input type="hidden" name="action" value="cv_bulk_testimonial">
        <input type="hidden" name="bulk_action" id="cv-bulk-action-target" value="">
        <?php wp_nonce_field('cv_testimonial_bulk_action', 'cv_testimonial_bulk_nonce'); ?>

        <table class="cv-table">
            <thead>
                <tr>
                    <th class="cv-checkbox-col"><input type="checkbox" id="cv-select-all"></th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Location</th>
                    <th>Service</th>
                    <th>Rating</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($records)) : ?>
                    <?php foreach ($records as $row) : 
                        $del_nonce = wp_create_nonce('cv_delete_testimonial_' . $row->id);
                        $del_url = add_query_arg(array(
                            'action' => 'cv_delete_testimonial',
                            'id'     => $row->id,
                            'nonce'  => $del_nonce
                        ), admin_url('admin-post.php'));
                        ?>
                        <tr>
                            <td class="cv-checkbox-col"><input type="checkbox" name="ids[]" value="<?php echo esc_attr($row->id); ?>" class="cv-row-select"></td>
                            <td data-label="Image">
                                <img src="<?php echo !empty($row->client_image) ? esc_url($row->client_image) : CLAIRVOYANT_PLUGIN_URL . 'assets/images/default-avatar.png'; ?>" class="cv-table-thumbnail" alt="<?php echo esc_attr($row->client_name); ?>">
                            </td>
                            <td data-label="Name"><strong><?php echo esc_html($row->client_name); ?></strong></td>
                            <td data-label="Location"><?php echo esc_html($row->location ? $row->location : '-'); ?></td>
                            <td data-label="Service"><?php echo esc_html($row->service ? $row->service : '-'); ?></td>
                            <td data-label="Rating"><?php echo cv_render_star_rating($row->rating); ?></td>
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
                            <td data-label="Actions">
                                <div class="cv-table-actions">
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=cv-testimonials-add&action=edit&id=' . $row->id)); ?>" class="cv-table-action-btn edit-btn">📝</a>
                                    <a href="<?php echo esc_url($del_url); ?>" class="cv-table-action-btn delete-btn cv-delete-record-btn" data-name="<?php echo esc_attr($row->client_name); ?>">🗑️</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr><td colspan="8" style="text-align:center; padding:32px;">No testimonials found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </form>
    <?php echo cv_render_pagination($total_items, $limit, $paged, $base_url); ?>
</div>

<div class="cv-bulk-actions-bar" id="cv-bulk-bar">
    <span class="cv-bulk-selected-count"><span id="cv-selected-count">0</span> items selected</span>
    <button type="button" class="cv-form-button danger cv-bulk-trigger-btn" data-action="delete">Delete Selected</button>
</div>
