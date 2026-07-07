<?php
/**
 * Horoscope Frontend Shortcode Template
 * 
 * Handles Daily, Weekly, and Transit horoscope templates
 * 
 * @package Clairvoyant_Core
 * @subpackage Frontend
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$type = isset($atts['type']) ? sanitize_key($atts['type']) : 'daily';

$consultation_url = cv_get_setting('consultation_url', 'https://clairvoyantofficial.com/services/#booking');
$consultation_btn_text = cv_get_setting('consultation_btn_text', 'Book Consultation');

// ----------------------------------------------------
// DAILY HOROSCOPE
// ----------------------------------------------------
if ($type === 'daily') {
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
    $db_records = cv_get_daily_horoscopes(array('date' => $target_date, 'status' => 'publish', 'limit' => 12));
    $predictions = array();
    foreach ($db_records as $r) {
        $predictions[strtolower($r->zodiac_sign)] = $r;
    }
    
    $zodiac_signs = cv_get_zodiac_list();
    $formatted_date = cv_format_date($target_date);
    ?>
    <div class="cv-horo-section type-daily">
        <div class="cv-horo-container">
            <div class="cv-horo-heading">
                <h2>Daily Horoscope</h2>
                <p>Detailed daily forecasts for all zodiac signs - <?php echo esc_html($formatted_date); ?></p>
            </div>
            
            <div class="cv-horo-grid">
                <?php foreach ($zodiac_signs as $key => $sign) : 
                    $has_data = isset($predictions[$key]);
                    $pred_text = $has_data ? strip_tags($predictions[$key]->prediction) : 'No forecasts available today.';
                    $short_pred = mb_strimwidth($pred_text, 0, 110, '...');
                    $rating = $has_data ? (int) $predictions[$key]->today_rating : 0;
                    
                    // Modal items
                    $full_prediction = $has_data ? wp_kses_post($predictions[$key]->prediction) : '';
                    $lucky_number = $has_data ? esc_attr($predictions[$key]->lucky_number) : '';
                    $lucky_color = $has_data ? esc_attr($predictions[$key]->lucky_color) : '';
                    $career = $has_data ? esc_attr($predictions[$key]->career) : '';
                    $love = $has_data ? esc_attr($predictions[$key]->love) : '';
                    $health = $has_data ? esc_attr($predictions[$key]->health) : '';
                    $money = $has_data ? esc_attr($predictions[$key]->money) : '';
                    ?>
                    <div class="cv-horo-card <?php echo $has_data ? 'cv-clickable-horo-card' : 'cv-disabled-card'; ?>"
                         data-name="<?php echo esc_attr($sign['name']); ?>"
                         data-icon="<?php echo esc_attr($sign['icon']); ?>"
                         data-date="<?php echo esc_attr($formatted_date); ?>"
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
        </div>
    </div>
    <?php
}

// ----------------------------------------------------
// WEEKLY HOROSCOPE
// ----------------------------------------------------
if ($type === 'weekly') {
    require_once CLAIRVOYANT_PLUGIN_DIR . 'modules/horoscope/weekly/database.php';
    
    // Fetch latest weekly start date
    global $wpdb;
    $latest_week = $wpdb->get_var($wpdb->prepare(
        "SELECT week_start FROM {$wpdb->prefix}cv_weekly_horoscope WHERE status = 'publish' AND (scheduled_at IS NULL OR scheduled_at <= %s) ORDER BY week_start DESC LIMIT 1",
        current_time('mysql')
    ));
    
    $args = array('status' => 'publish', 'limit' => 12);
    if ($latest_week) {
        $args['week_start'] = $latest_week;
    }
    
    $db_records = cv_get_weekly_horoscopes($args);
    $predictions = array();
    $week_start_date = '';
    $week_end_date = '';
    
    foreach ($db_records as $r) {
        $predictions[strtolower($r->zodiac_sign)] = $r;
        $week_start_date = cv_format_date($r->week_start);
        $week_end_date = cv_format_date($r->week_end);
    }
    
    $zodiac_signs = cv_get_zodiac_list();
    ?>
    <div class="cv-horo-section type-weekly">
        <div class="cv-horo-container">
            <div class="cv-horo-heading">
                <h2>Weekly Horoscope</h2>
                <?php if ($week_start_date) : ?>
                    <p>Weekly forecasts for all signs: <?php echo esc_html($week_start_date); ?> to <?php echo esc_html($week_end_date); ?></p>
                <?php else : ?>
                    <p>Stay updated with our weekly stars forecast.</p>
                <?php endif; ?>
            </div>
            
            <div class="cv-horo-grid">
                <?php foreach ($zodiac_signs as $key => $sign) : 
                    $has_data = isset($predictions[$key]);
                    $pred_text = $has_data ? strip_tags($predictions[$key]->prediction) : 'No weekly forecast available.';
                    $short_pred = mb_strimwidth($pred_text, 0, 110, '...');
                    $rating = $has_data ? (int) $predictions[$key]->overall_rating : 0;
                    
                    $full_prediction = $has_data ? wp_kses_post($predictions[$key]->prediction) : '';
                    $career = $has_data ? esc_attr($predictions[$key]->career) : '';
                    $love = $has_data ? esc_attr($predictions[$key]->love) : '';
                    $health = $has_data ? esc_attr($predictions[$key]->health) : '';
                    $money = $has_data ? esc_attr($predictions[$key]->money) : '';
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
        </div>
    </div>
    <?php
}

// ----------------------------------------------------
// TRANSIT HOROSCOPE
// ----------------------------------------------------
if ($type === 'transit') {
    require_once CLAIRVOYANT_PLUGIN_DIR . 'modules/horoscope/transit/database.php';
    $limit = isset($atts['limit']) ? (int) $atts['limit'] : 5;
    $transits = cv_get_transit_horoscopes(array('status' => 'publish', 'limit' => $limit));
    ?>
    <div class="cv-transit-section">
        <div class="cv-transit-container">
            <div class="cv-horo-heading">
                <h2>Planetary Transits</h2>
                <p>Track current planetary movements and their astrological impacts.</p>
            </div>
            
            <div class="cv-transit-list">
                <?php if (!empty($transits)) : ?>
                    <?php foreach ($transits as $t) : ?>
                        <div class="cv-transit-card">
                            <div class="cv-transit-meta">
                                <span class="cv-transit-planet">🪐 <?php echo esc_html($t->planet); ?></span>
                                <span class="cv-transit-date">From: <?php echo esc_html(cv_format_date($t->transit_start_date)); ?> <?php echo $t->transit_end_date ? 'to ' . esc_html(cv_format_date($t->transit_end_date)) : '(Ongoing)'; ?></span>
                            </div>
                            <h3 class="cv-transit-title"><?php echo esc_html($t->title); ?></h3>
                            
                            <div class="cv-transit-prediction">
                                <?php echo wp_kses_post($t->prediction); ?>
                            </div>
                            
                            <?php if (!empty($t->affected_signs)) : ?>
                                <div class="cv-transit-impact">
                                    <strong>Impacted Signs:</strong>
                                    <span><?php echo esc_html($t->affected_signs); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($t->remedies)) : ?>
                                <div class="cv-transit-remedies">
                                    <strong>Astrological Remedies:</strong>
                                    <div class="remedies-content"><?php echo wp_kses_post($t->remedies); ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <p class="description" style="text-align:center;">No active transits listed.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
}
?>

<!-- Shared Horoscope Detail Lightbox -->
<?php if ($type === 'daily' || $type === 'weekly') : ?>
<div class="cv-modal-lightbox" id="cv-horo-lightbox">
    <button class="cv-lightbox-close" id="cv-horo-lightbox-close-btn">&times;</button>
    <div class="cv-lightbox-content">
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
<?php endif; ?>
