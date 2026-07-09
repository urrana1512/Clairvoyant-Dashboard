<?php
/**
 * Dashboard Subpanel View
 * 
 * Displays quick stats, activity log, action buttons, and recent updates
 * 
 * @package Clairvoyant_Core
 * @subpackage Admin
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

// Table names
$table_rashi = $wpdb->prefix . 'cv_daily_rashi';
$table_daily_horo = $wpdb->prefix . 'cv_daily_horoscope';
$table_weekly_horo = $wpdb->prefix . 'cv_weekly_horoscope';
$table_transit_horo = $wpdb->prefix . 'cv_transit_horoscope';
$table_testimonials = $wpdb->prefix . 'cv_testimonials';
$table_prediction_24_48 = $wpdb->prefix . 'cv_prediction_24_48';

// Fetch Statistics counts
$total_rashi_pub = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_rashi WHERE status = 'publish'");
$today_horo_count = (int) $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM $table_daily_horo WHERE date = %s",
    current_time('Y-m-d')
));
$total_testimonials = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_testimonials");
$pending_testimonials = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_testimonials WHERE status = 'draft'");
$total_prediction_24_48 = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_prediction_24_48 WHERE status = 'publish'");

// Gather recent updates from all modules
$recent_updates = array();

// 24-48 Hrs Predictions
$predictions_24_48 = $wpdb->get_results("SELECT id, element as name, date, status, updated_at FROM $table_prediction_24_48 ORDER BY updated_at DESC LIMIT 10", ARRAY_A);
if ($predictions_24_48) {
    foreach ($predictions_24_48 as $p) {
        $recent_updates[] = array(
            'type'       => '24-48 Hrs',
            'title'      => sprintf('%s - %s', ucfirst($p['name']), cv_format_date($p['date'])),
            'date'       => $p['updated_at'],
            'status'     => $p['status'],
            'edit_url'   => admin_url('admin.php?page=cv-prediction-24h-add&action=edit&id=' . $p['id'])
        );
    }
}

// Rashi
$rashis = $wpdb->get_results("SELECT id, zodiac_sign as name, date, status, updated_at FROM $table_rashi ORDER BY updated_at DESC LIMIT 10", ARRAY_A);
if ($rashis) {
    foreach ($rashis as $r) {
        $recent_updates[] = array(
            'type'       => 'Daily Rashi',
            'title'      => sprintf('%s - %s', ucfirst($r['name']), cv_format_date($r['date'])),
            'date'       => $r['updated_at'],
            'status'     => $r['status'],
            'edit_url'   => admin_url('admin.php?page=cv-daily-rashi-add&action=edit&id=' . $r['id'])
        );
    }
}

// Daily Horoscope
$daily_horos = $wpdb->get_results("SELECT id, zodiac_sign as name, date, status, updated_at FROM $table_daily_horo ORDER BY updated_at DESC LIMIT 10", ARRAY_A);
if ($daily_horos) {
    foreach ($daily_horos as $h) {
        $recent_updates[] = array(
            'type'       => 'Daily Horoscope',
            'title'      => sprintf('%s - %s', ucfirst($h['name']), cv_format_date($h['date'])),
            'date'       => $h['updated_at'],
            'status'     => $h['status'],
            'edit_url'   => admin_url('admin.php?page=cv-horoscope-daily-add&action=edit&id=' . $h['id'])
        );
    }
}

// Weekly Horoscope
$weekly_horos = $wpdb->get_results("SELECT id, zodiac_sign as name, week_start, status, updated_at FROM $table_weekly_horo ORDER BY updated_at DESC LIMIT 10", ARRAY_A);
if ($weekly_horos) {
    foreach ($weekly_horos as $w) {
        $recent_updates[] = array(
            'type'       => 'Weekly Horoscope',
            'title'      => sprintf('%s (Week %s)', ucfirst($w['name']), cv_format_date($w['week_start'])),
            'date'       => $w['updated_at'],
            'status'     => $w['status'],
            'edit_url'   => admin_url('admin.php?page=cv-horoscope-weekly-add&action=edit&id=' . $w['id'])
        );
    }
}

// Transit Horoscope
$transits = $wpdb->get_results("SELECT id, title, planet, status, updated_at FROM $table_transit_horo ORDER BY updated_at DESC LIMIT 10", ARRAY_A);
if ($transits) {
    foreach ($transits as $t) {
        $recent_updates[] = array(
            'type'       => 'Transit',
            'title'      => sprintf('%s: %s', ucfirst($t['planet']), $t['title']),
            'date'       => $t['updated_at'],
            'status'     => $t['status'],
            'edit_url'   => admin_url('admin.php?page=cv-horoscope-transit-add&action=edit&id=' . $t['id'])
        );
    }
}

// Testimonials
$reviews = $wpdb->get_results("SELECT id, client_name, service, status, updated_at FROM $table_testimonials ORDER BY updated_at DESC LIMIT 10", ARRAY_A);
if ($reviews) {
    foreach ($reviews as $rev) {
        $recent_updates[] = array(
            'type'       => 'Testimonial',
            'title'      => sprintf('%s (%s)', $rev['client_name'], $rev['service']),
            'date'       => $rev['updated_at'],
            'status'     => $rev['status'],
            'edit_url'   => admin_url('admin.php?page=cv-testimonials-add&action=edit&id=' . $rev['id'])
        );
    }
}

// Sort the combined array by updated date descending
usort($recent_updates, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

// Slice top 10 elements
$recent_updates = array_slice($recent_updates, 0, 10);

// Load Activity Log Option
$activities = get_option('cv_activity_log', array());

?>

<div class="cv-breadcrumb">
    <span>Clairvoyant Core</span> &gt; <span>Dashboard</span>
</div>

<div class="cv-page-title-row">
    <h1 class="cv-page-title"><?php esc_html_e('Astrology Dashboard', 'clairvoyant-core'); ?></h1>
</div>

<!-- Stats Row -->
<div class="cv-stats-grid">
    <!-- Stat 1 -->
    <div class="cv-stats-card">
        <div class="cv-stats-header">
            <span class="cv-stats-title">Active Rashi Predictions</span>
            <span class="cv-stats-icon">♈</span>
        </div>
        <div class="cv-stats-value"><?php echo esc_html($total_rashi_pub); ?></div>
        <div class="cv-stats-trend up">Published predictions</div>
    </div>
    
    <!-- Stat 2 -->
    <div class="cv-stats-card">
        <div class="cv-stats-header">
            <span class="cv-stats-title">Today's Horoscopes</span>
            <span class="cv-stats-icon">🔮</span>
        </div>
        <div class="cv-stats-value"><?php echo esc_html($today_horo_count); ?></div>
        <div class="cv-stats-trend info">For <?php echo esc_html(cv_format_date(current_time('Y-m-d'))); ?></div>
    </div>

    <!-- Stat 2.5 (24-48h predictions) -->
    <div class="cv-stats-card">
        <div class="cv-stats-header">
            <span class="cv-stats-title">Active 24-48h Predictions</span>
            <span class="cv-stats-icon">⏳</span>
        </div>
        <div class="cv-stats-value"><?php echo esc_html($total_prediction_24_48); ?></div>
        <div class="cv-stats-trend up">Published elements forecast</div>
    </div>

    <!-- Stat 3 -->
    <div class="cv-stats-card">
        <div class="cv-stats-header">
            <span class="cv-stats-title">Total Testimonials</span>
            <span class="cv-stats-icon">💬</span>
        </div>
        <div class="cv-stats-value"><?php echo esc_html($total_testimonials); ?></div>
        <div class="cv-stats-trend up">All customer reviews</div>
    </div>

    <!-- Stat 4 -->
    <div class="cv-stats-card">
        <div class="cv-stats-header">
            <span class="cv-stats-title">Pending Reviews</span>
            <span class="cv-stats-icon">⏳</span>
        </div>
        <div class="cv-stats-value"><?php echo esc_html($pending_testimonials); ?></div>
        <div class="cv-stats-trend <?php echo $pending_testimonials > 0 ? 'down' : 'up'; ?>">
            <?php echo $pending_testimonials > 0 ? 'Needs moderation' : 'No drafts'; ?>
        </div>
    </div>
</div>

<!-- Main Section Grid -->
<div class="cv-dashboard-cols">
    <!-- Left Column: Recent Updates Table -->
    <div class="cv-card">
        <h2 class="cv-card-title"><?php esc_html_e('Recent Content Changes', 'clairvoyant-core'); ?></h2>
        
        <?php if (!empty($recent_updates)) : ?>
            <div style="overflow-x: auto;">
                <table class="cv-table" style="margin-top:0;">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Title</th>
                            <th>Last Modified</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_updates as $update) : ?>
                            <tr>
                                <td data-label="Type"><strong><?php echo esc_html($update['type']); ?></strong></td>
                                <td data-label="Title"><?php echo esc_html($update['title']); ?></td>
                                <td data-label="Last Modified"><?php echo esc_html(mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $update['date'])); ?></td>
                                <td data-label="Status"><?php echo cv_get_status_badge($update['status']); ?></td>
                                <td data-label="Action">
                                    <a href="<?php echo esc_url($update['edit_url']); ?>" class="cv-table-action-btn edit-btn" title="Edit">📝</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else : ?>
            <p class="description" style="padding: 24px 0; text-align: center;">
                <?php esc_html_e('No recent modifications found. Start by creating astrology listings!', 'clairvoyant-core'); ?>
            </p>
        <?php endif; ?>
    </div>

    <!-- Right Column: Quick Actions & Activity Logs -->
    <div>
        <!-- Quick Actions Panel -->
        <div class="cv-card">
            <h2 class="cv-card-title"><?php esc_html_e('Quick Actions', 'clairvoyant-core'); ?></h2>
            <div class="cv-quick-actions">
                <a href="<?php echo esc_url(admin_url('admin.php?page=cv-daily-rashi-add')); ?>" class="cv-action-btn">
                    <span>➕</span>
                    <span>Add Rashi</span>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=cv-horoscope-daily-add')); ?>" class="cv-action-btn">
                    <span>🔮</span>
                    <span>Add Horoscope</span>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=cv-prediction-24h-add')); ?>" class="cv-action-btn">
                    <span>⏳</span>
                    <span>Add 24-48 Hrs</span>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=cv-testimonials-add')); ?>" class="cv-action-btn">
                    <span>💬</span>
                    <span>Add Testimonial</span>
                </a>
                <a href="<?php echo esc_url(home_url()); ?>" target="_blank" class="cv-action-btn">
                    <span>🌐</span>
                    <span>View Site</span>
                </a>
            </div>
        </div>

        <!-- Activity Log Panel -->
        <div class="cv-card">
            <h2 class="cv-card-title"><?php esc_html_e('Activity Log', 'clairvoyant-core'); ?></h2>
            <?php if (!empty($activities)) : ?>
                <ul class="cv-activity-list">
                    <?php foreach ($activities as $log) : ?>
                        <li class="cv-activity-item">
                            <span class="cv-activity-desc"><?php echo esc_html($log['action']); ?></span>
                            <div class="cv-activity-meta">
                                <span>by <?php echo esc_html($log['user']); ?></span>
                                <span><?php echo esc_html(human_time_diff(strtotime($log['timestamp']), current_time('timestamp')) . ' ago'); ?></span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else : ?>
                <p class="description" style="text-align: center; padding: 12px 0;">
                    <?php esc_html_e('No recent dashboard activity logs.', 'clairvoyant-core'); ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>
