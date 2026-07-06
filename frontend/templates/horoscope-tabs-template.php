<?php
/**
 * Horoscope Tabs Frontend Shortcode Template
 * 
 * Renders Daily, Weekly, and Transit horoscopes inside a unified tabbed widget
 * 
 * @package Clairvoyant_Core
 * @subpackage Frontend
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$active_tab = isset($atts['default_tab']) ? sanitize_key($atts['default_tab']) : 'today';

$consultation_url = cv_get_setting('consultation_url', 'https://clairvoyantofficial.com/services/#booking');
$consultation_btn_text = cv_get_setting('consultation_btn_text', 'Book Consultation');

// ----------------------------------------------------
// 1. Gather Daily Horoscope Data
// ----------------------------------------------------
$target_date = isset($atts['date']) ? sanitize_text_field($atts['date']) : '';
if (empty($target_date)) {
    global $wpdb;
    $target_date = $wpdb->get_var($wpdb->prepare(
        "SELECT date FROM {$wpdb->prefix}cv_daily_horoscope WHERE status = 'publish' AND (scheduled_at IS NULL OR scheduled_at <= %s) ORDER BY date DESC LIMIT 1",
        current_time('mysql')
    ));
}
if (empty($target_date)) {
    $target_date = current_time('Y-m-d');
}

require_once CLAIRVOYANT_PLUGIN_DIR . 'modules/horoscope/daily/database.php';
$daily_records = cv_get_daily_horoscopes(array('date' => $target_date, 'status' => 'publish', 'limit' => 12));
$daily_predictions = array();
foreach ($daily_records as $r) {
    $daily_predictions[strtolower($r->zodiac_sign)] = $r;
}

// ----------------------------------------------------
// 2. Gather Weekly Horoscope Data
// ----------------------------------------------------
require_once CLAIRVOYANT_PLUGIN_DIR . 'modules/horoscope/weekly/database.php';
global $wpdb;
$latest_week = $wpdb->get_var($wpdb->prepare(
    "SELECT week_start FROM {$wpdb->prefix}cv_weekly_horoscope WHERE status = 'publish' AND (scheduled_at IS NULL OR scheduled_at <= %s) ORDER BY week_start DESC LIMIT 1",
    current_time('mysql')
));
$weekly_args = array('status' => 'publish', 'limit' => 12);
if ($latest_week) {
    $weekly_args['week_start'] = $latest_week;
}
$weekly_records = cv_get_weekly_horoscopes($weekly_args);
$weekly_predictions = array();
$week_start_date = '';
$week_end_date = '';
foreach ($weekly_records as $r) {
    $weekly_predictions[strtolower($r->zodiac_sign)] = $r;
    $week_start_date = cv_format_date($r->week_start);
    $week_end_date = cv_format_date($r->week_end);
}

// ----------------------------------------------------
// 3. Gather Transit Horoscope Data
// ----------------------------------------------------
require_once CLAIRVOYANT_PLUGIN_DIR . 'modules/horoscope/transit/database.php';
$transit_limit = isset($atts['limit']) ? (int) $atts['limit'] : 5;
$transits = cv_get_transit_horoscopes(array('status' => 'publish', 'limit' => $transit_limit));

// Zodiac and Formatted Date
$zodiac_signs = cv_get_zodiac_list();
$formatted_daily_date = cv_format_date($target_date);

// Set Initial Title and Description
$initial_title = 'Daily Horoscope';
$initial_desc = 'Detailed daily forecasts for all zodiac signs - ' . $formatted_daily_date;
if ($active_tab === 'weekly') {
    $initial_title = 'Weekly Horoscope';
    $initial_desc = $week_start_date ? 'Weekly forecasts for all signs: ' . $week_start_date . ' to ' . $week_end_date : 'Stay updated with our weekly stars forecast.';
} elseif ($active_tab === 'transit') {
    $initial_title = 'Planetary Transits';
    $initial_desc = 'Track current planetary movements and their astrological impacts.';
}

?>

<div class="cv-horoscope-tabs-wrapper">
    <!-- Active Tab Header (Positioned at the very top of the wrapper) -->
    <div class="cv-horo-heading" style="margin-bottom: 24px;">
        <h2 id="cv-horo-active-title"><?php echo esc_html($initial_title); ?></h2>
        <p id="cv-horo-active-desc"><?php echo esc_html($initial_desc); ?></p>
    </div>

    <!-- Tabs Navigation Bar (Positioned between Header and Cards) -->
    <div class="cv-horoscope-tabs-nav">
        <button class="cv-tab-button <?php echo $active_tab === 'today' ? 'active' : ''; ?>" 
                data-tab="today"
                data-title="Daily Horoscope"
                data-desc="Detailed daily forecasts for all zodiac signs - <?php echo esc_attr($formatted_daily_date); ?>">
            Today's Horoscope
        </button>
        <button class="cv-tab-button <?php echo $active_tab === 'weekly' ? 'active' : ''; ?>" 
                data-tab="weekly"
                data-title="Weekly Horoscope"
                data-desc="<?php echo $week_start_date ? 'Weekly forecasts for all signs: ' . esc_attr($week_start_date) . ' to ' . esc_attr($week_end_date) : 'Stay updated with our weekly stars forecast.'; ?>">
            Weekly Horoscope
        </button>
        <button class="cv-tab-button <?php echo $active_tab === 'transit' ? 'active' : ''; ?>" 
                data-tab="transit"
                data-title="Planetary Transits"
                data-desc="Track current planetary movements and their astrological impacts.">
            Planetary Transits
        </button>
    </div>

    <!-- Tabs Content Container -->
    <div class="cv-horoscope-tabs-content-container">

        <!-- 1. TODAY'S HOROSCOPE TAB -->
        <div class="cv-tab-content <?php echo $active_tab === 'today' ? 'active' : ''; ?>" data-tab="today" style="<?php echo $active_tab === 'today' ? '' : 'display:none;'; ?>">
            <div class="cv-horo-section type-daily" style="padding-top: 0;">
                <div class="cv-horo-container">
                    
                    <!-- Desktop Grid -->
                    <div class="cv-horo-grid">
                        <?php foreach ($zodiac_signs as $key => $sign) : 
                            $has_data = isset($daily_predictions[$key]);
                            $pred_text = $has_data ? strip_tags($daily_predictions[$key]->prediction) : 'No forecasts available today.';
                            $short_pred = mb_strimwidth($pred_text, 0, 110, '...');
                            $rating = $has_data ? (int) $daily_predictions[$key]->today_rating : 0;
                            
                            $full_prediction = $has_data ? wp_kses_post($daily_predictions[$key]->prediction) : '';
                            $lucky_number = $has_data ? esc_attr($daily_predictions[$key]->lucky_number) : '';
                            $lucky_color = $has_data ? esc_attr($daily_predictions[$key]->lucky_color) : '';
                            $career = $has_data ? esc_attr($daily_predictions[$key]->career) : '';
                            $love = $has_data ? esc_attr($daily_predictions[$key]->love) : '';
                            $health = $has_data ? esc_attr($daily_predictions[$key]->health) : '';
                            $money = $has_data ? esc_attr($daily_predictions[$key]->money) : '';
                            ?>
                            <div class="cv-horo-card <?php echo $has_data ? 'cv-clickable-horo-card' : 'cv-disabled-card'; ?>"
                                 data-name="<?php echo esc_attr($sign['name']); ?>"
                                 data-icon="<?php echo esc_attr($sign['icon']); ?>"
                                 data-date="<?php echo esc_attr($formatted_daily_date); ?>"
                                 data-prediction="<?php echo esc_attr($full_prediction); ?>"
                                 data-lucky-number="<?php echo esc_attr($lucky_number); ?>"
                                 data-lucky-color="<?php echo esc_attr($lucky_color); ?>"
                                 data-career="<?php echo esc_attr($career); ?>"
                                 data-love="<?php echo esc_attr($love); ?>"
                                 data-health="<?php echo esc_attr($health); ?>"
                                 data-money="<?php echo esc_attr($money); ?>"
                                 data-rating="<?php echo esc_attr($rating); ?>">
                                
                                <div class="cv-horo-icon"><?php echo esc_html($sign['icon']); ?></div>
                                <h3 class="cv-horo-name"><?php echo esc_html($sign['name']); ?></h3>
                                <p class="cv-horo-prediction"><?php echo esc_html($short_pred); ?></p>
                                <div class="cv-horo-rating">
                                    <span>Rating</span>
                                    <div class="stars">
                                        <?php for ($i = 1; $i <= 5; $i++) : ?>
                                            <span class="<?php echo $i <= $rating ? 'filled' : ''; ?>">★</span>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <a href="#" class="cv-horo-card-btn">Explore Detailed Horoscope</a>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Mobile Symbol Grid -->
                    <div class="cv-horo-mobile-grid">
                        <?php foreach ($zodiac_signs as $key => $sign) : 
                            $has_data = isset($daily_predictions[$key]);
                            $rating = $has_data ? (int) $daily_predictions[$key]->today_rating : 0;
                            
                            $full_prediction = $has_data ? wp_kses_post($daily_predictions[$key]->prediction) : '';
                            $lucky_number = $has_data ? esc_attr($daily_predictions[$key]->lucky_number) : '';
                            $lucky_color = $has_data ? esc_attr($daily_predictions[$key]->lucky_color) : '';
                            $career = $has_data ? esc_attr($daily_predictions[$key]->career) : '';
                            $love = $has_data ? esc_attr($daily_predictions[$key]->love) : '';
                            $health = $has_data ? esc_attr($daily_predictions[$key]->health) : '';
                            $money = $has_data ? esc_attr($daily_predictions[$key]->money) : '';
                            ?>
                            <div class="cv-horo-mobile-card <?php echo $has_data ? 'cv-clickable-horo-card' : 'cv-disabled-card'; ?>"
                                 data-name="<?php echo esc_attr($sign['name']); ?>"
                                 data-icon="<?php echo esc_attr($sign['icon']); ?>"
                                 data-date="<?php echo esc_attr($formatted_daily_date); ?>"
                                 data-prediction="<?php echo esc_attr($full_prediction); ?>"
                                 data-lucky-number="<?php echo esc_attr($lucky_number); ?>"
                                 data-lucky-color="<?php echo esc_attr($lucky_color); ?>"
                                 data-career="<?php echo esc_attr($career); ?>"
                                 data-love="<?php echo esc_attr($love); ?>"
                                 data-health="<?php echo esc_attr($health); ?>"
                                 data-money="<?php echo esc_attr($money); ?>"
                                 data-rating="<?php echo esc_attr($rating); ?>">
                                
                                <div class="cv-horo-mobile-icon"><?php echo esc_html($sign['icon']); ?></div>
                                <span class="cv-horo-mobile-name"><?php echo esc_html($sign['name']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. WEEKLY HOROSCOPE TAB -->
        <div class="cv-tab-content <?php echo $active_tab === 'weekly' ? 'active' : ''; ?>" data-tab="weekly" style="<?php echo $active_tab === 'weekly' ? '' : 'display:none;'; ?>">
            <div class="cv-horo-section type-weekly" style="padding-top: 0;">
                <div class="cv-horo-container">
                    
                    <!-- Desktop Grid -->
                    <div class="cv-horo-grid">
                        <?php foreach ($zodiac_signs as $key => $sign) : 
                            $has_data = isset($weekly_predictions[$key]);
                            $pred_text = $has_data ? strip_tags($weekly_predictions[$key]->prediction) : 'No weekly forecast available.';
                            $short_pred = mb_strimwidth($pred_text, 0, 110, '...');
                            $rating = $has_data ? (int) $weekly_predictions[$key]->overall_rating : 0;
                            
                            $full_prediction = $has_data ? wp_kses_post($weekly_predictions[$key]->prediction) : '';
                            $career = $has_data ? esc_attr($weekly_predictions[$key]->career) : '';
                            $love = $has_data ? esc_attr($weekly_predictions[$key]->love) : '';
                            $health = $has_data ? esc_attr($weekly_predictions[$key]->health) : '';
                            $money = $has_data ? esc_attr($weekly_predictions[$key]->money) : '';
                            ?>
                            <div class="cv-horo-card <?php echo $has_data ? 'cv-clickable-horo-card' : 'cv-disabled-card'; ?>"
                                 data-name="<?php echo esc_attr($sign['name']); ?>"
                                 data-icon="<?php echo esc_attr($sign['icon']); ?>"
                                 data-date="Weekly Forecast"
                                 data-prediction="<?php echo esc_attr($full_prediction); ?>"
                                 data-career="<?php echo esc_attr($career); ?>"
                                 data-love="<?php echo esc_attr($love); ?>"
                                 data-health="<?php echo esc_attr($health); ?>"
                                 data-money="<?php echo esc_attr($money); ?>"
                                 data-rating="<?php echo esc_attr($rating); ?>">
                                
                                <div class="cv-horo-icon"><?php echo esc_html($sign['icon']); ?></div>
                                <h3 class="cv-horo-name"><?php echo esc_html($sign['name']); ?></h3>
                                <p class="cv-horo-prediction"><?php echo esc_html($short_pred); ?></p>
                                <div class="cv-horo-rating">
                                    <span>Luck Rating</span>
                                    <div class="stars">
                                        <?php for ($i = 1; $i <= 5; $i++) : ?>
                                            <span class="<?php echo $i <= $rating ? 'filled' : ''; ?>">★</span>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <a href="#" class="cv-horo-card-btn">Explore Detailed Horoscope</a>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Mobile Symbol Grid -->
                    <div class="cv-horo-mobile-grid">
                        <?php foreach ($zodiac_signs as $key => $sign) : 
                            $has_data = isset($weekly_predictions[$key]);
                            $rating = $has_data ? (int) $weekly_predictions[$key]->overall_rating : 0;
                            
                            $full_prediction = $has_data ? wp_kses_post($weekly_predictions[$key]->prediction) : '';
                            $career = $has_data ? esc_attr($weekly_predictions[$key]->career) : '';
                            $love = $has_data ? esc_attr($weekly_predictions[$key]->love) : '';
                            $health = $has_data ? esc_attr($weekly_predictions[$key]->health) : '';
                            $money = $has_data ? esc_attr($weekly_predictions[$key]->money) : '';
                            ?>
                            <div class="cv-horo-mobile-card <?php echo $has_data ? 'cv-clickable-horo-card' : 'cv-disabled-card'; ?>"
                                 data-name="<?php echo esc_attr($sign['name']); ?>"
                                 data-icon="<?php echo esc_attr($sign['icon']); ?>"
                                 data-date="Weekly Forecast"
                                 data-prediction="<?php echo esc_attr($full_prediction); ?>"
                                 data-career="<?php echo esc_attr($career); ?>"
                                 data-love="<?php echo esc_attr($love); ?>"
                                 data-health="<?php echo esc_attr($health); ?>"
                                 data-money="<?php echo esc_attr($money); ?>"
                                 data-rating="<?php echo esc_attr($rating); ?>">
                                
                                <div class="cv-horo-mobile-icon"><?php echo esc_html($sign['icon']); ?></div>
                                <span class="cv-horo-mobile-name"><?php echo esc_html($sign['name']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. PLANETARY TRANSITS TAB -->
        <div class="cv-tab-content <?php echo $active_tab === 'transit' ? 'active' : ''; ?>" data-tab="transit" style="<?php echo $active_tab === 'transit' ? '' : 'display:none;'; ?>">
            <div class="cv-transit-section" style="padding-top: 0;">
                <div class="cv-transit-container">
                    
                    <div class="cv-transit-grid">
                        <?php if (!empty($transits)) : ?>
                            <?php foreach ($transits as $t) : ?>
                                <div class="cv-transit-card cv-clickable-transit-card" 
                                     style="background-image: url('<?php echo CLAIRVOYANT_PLUGIN_URL . 'assets/images/transit-bg.png'; ?>');"
                                     data-title="<?php echo esc_attr($t->title); ?>"
                                     data-planet="<?php echo esc_attr(ucfirst($t->planet)); ?>"
                                     data-date="From <?php echo esc_attr(cv_format_date($t->transit_start_date)); ?> <?php echo $t->transit_end_date ? 'to ' . esc_attr(cv_format_date($t->transit_end_date)) : '(Ongoing)'; ?>"
                                     data-prediction="<?php echo esc_attr(wp_kses_post($t->prediction)); ?>"
                                     data-affected-signs="<?php echo esc_attr($t->affected_signs ? $t->affected_signs : 'None specified'); ?>"
                                     data-remedies="<?php echo esc_attr(wp_kses_post($t->remedies ? $t->remedies : 'None specified')); ?>">
                                    
                                    <div class="cv-transit-card-inner">
                                        <div class="cv-transit-card-planet-icon">🪐 <?php echo esc_html(ucfirst($t->planet)); ?></div>
                                        <h3 class="cv-transit-card-title"><?php echo esc_html($t->title); ?></h3>
                                        <span class="cv-transit-card-explore">View Detailed Impact</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <p class="description" style="text-align:center; grid-column: 1/-1; width: 100%;">No active transits listed.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Shared Horoscope Detail Lightbox -->
<div class="cv-modal-lightbox" id="cv-horo-lightbox">
    <div class="cv-lightbox-content">
        <button class="cv-lightbox-close" id="cv-horo-lightbox-close-btn">&times;</button>
        <div class="cv-lightbox-header">
            <span class="cv-lightbox-icon" id="cv-horo-lightbox-icon">🔮</span>
            <div>
                <h3 class="cv-lightbox-title" id="cv-horo-lightbox-title">Leo</h3>
                <span class="cv-lightbox-date" id="cv-horo-lightbox-date">June 30, 2026</span>
            </div>
        </div>
        <div class="cv-lightbox-body">
            <div class="cv-lightbox-prediction" id="cv-horo-lightbox-prediction"></div>
            
            <div class="cv-lightbox-meta-grid" id="cv-horo-meta-row">
                <div class="cv-lightbox-meta-item">
                    <strong>Lucky Number</strong>
                    <span id="cv-horo-lightbox-lucky-number">-</span>
                </div>
                <div class="cv-lightbox-meta-item">
                    <strong>Lucky Color</strong>
                    <span id="cv-horo-lightbox-lucky-color" style="display:inline-flex; align-items:center; gap:6px;">
                        <span class="color-dot" id="cv-horo-lightbox-color-dot"></span>
                        <span id="cv-horo-lightbox-color-name">-</span>
                    </span>
                </div>
                <div class="cv-lightbox-meta-item">
                    <strong>Rating</strong>
                    <div class="stars" id="cv-horo-lightbox-rating-stars">★★★</div>
                </div>
            </div>

            <!-- Life Areas Grid -->
            <div class="cv-lightbox-life-areas">
                <h4 class="cv-lightbox-life-title">Forecast Highlights</h4>
                <div class="cv-lightbox-areas-grid">
                    <div class="cv-lightbox-area-card" id="cv-horo-lightbox-career-card">
                        <h5>💼 Career</h5>
                        <p id="cv-horo-lightbox-career"></p>
                    </div>
                    <div class="cv-lightbox-area-card" id="cv-horo-lightbox-love-card">
                        <h5>❤️ Love</h5>
                        <p id="cv-horo-lightbox-love"></p>
                    </div>
                    <div class="cv-lightbox-area-card" id="cv-horo-lightbox-health-card">
                        <h5>🩺 Health</h5>
                        <p id="cv-horo-lightbox-health"></p>
                    </div>
                    <div class="cv-lightbox-area-card" id="cv-horo-lightbox-money-card">
                        <h5>💰 Money / Wealth</h5>
                        <p id="cv-horo-lightbox-money"></p>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($consultation_url)) : ?>
                <div class="cv-lightbox-actions" style="margin-top: 32px; text-align: center; border-top: 1px solid #E8DFD0; padding-top: 24px;">
                    <a href="<?php echo esc_url($consultation_url); ?>" target="_blank" class="cv-consultation-btn">
                        <?php echo esc_html($consultation_btn_text); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Shared Transit Detail Lightbox -->
<div class="cv-modal-lightbox" id="cv-transit-lightbox">
    <div class="cv-lightbox-content">
        <button class="cv-lightbox-close" id="cv-transit-lightbox-close-btn">&times;</button>
        <div class="cv-lightbox-header">
            <span class="cv-lightbox-icon">🪐</span>
            <div>
                <h3 class="cv-lightbox-title" id="cv-transit-lightbox-title">-</h3>
                <span class="cv-lightbox-date" id="cv-transit-lightbox-date">-</span>
            </div>
        </div>
        <div class="cv-lightbox-body">
            <div class="cv-lightbox-prediction" id="cv-transit-lightbox-prediction"></div>
            
            <div class="cv-lightbox-life-areas">
                <div class="cv-lightbox-areas-grid" style="grid-template-columns: 1fr; gap: 20px;">
                    <div class="cv-lightbox-area-card" id="cv-transit-lightbox-impact-card">
                        <h5 style="color:#C8A96A; font-size: 15px; margin-bottom: 8px;">💫 Impacted Signs</h5>
                        <p id="cv-transit-lightbox-impact" style="font-size:14px; line-height:1.6;"></p>
                    </div>
                    <div class="cv-lightbox-area-card" id="cv-transit-lightbox-remedies-card">
                        <h5 style="color:#C8A96A; font-size: 15px; margin-bottom: 8px;">🛡️ Astrological Remedies</h5>
                        <p id="cv-transit-lightbox-remedies" style="font-size:14px; line-height:1.6;"></p>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($consultation_url)) : ?>
                <div class="cv-lightbox-actions" style="margin-top: 32px; text-align: center; border-top: 1px solid #E8DFD0; padding-top: 24px;">
                    <a href="<?php echo esc_url($consultation_url); ?>" target="_blank" class="cv-consultation-btn">
                        <?php echo esc_html($consultation_btn_text); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
