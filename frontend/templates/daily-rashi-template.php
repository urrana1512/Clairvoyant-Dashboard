<?php
/**
 * Daily Rashi Frontend Shortcode Template
 * 
 * Renders Today's Rashi Fal grid, mobile symbol grid, and modal popups
 * 
 * @package Clairvoyant_Core
 * @subpackage Frontend
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// 1. Resolve date
$target_date = isset($atts['date']) ? sanitize_text_field($atts['date']) : '';
if (empty($target_date)) {
    // Fetch latest date from DB
    global $wpdb;
    $target_date = $wpdb->get_var($wpdb->prepare(
        "SELECT date FROM {$wpdb->prefix}cv_daily_rashi WHERE status = 'publish' AND (scheduled_at IS NULL OR scheduled_at <= %s) ORDER BY date DESC LIMIT 1",
        current_time('mysql')
    ));
}

if (empty($target_date)) {
    $target_date = current_time('Y-m-d');
}

// 2. Fetch predictions
require_once CLAIRVOYANT_PLUGIN_DIR . 'modules/daily-rashi/database.php';
$db_records = cv_get_daily_rashis(array(
    'date'   => $target_date,
    'status' => 'publish',
    'limit'  => 12
));

// Index by sign for easy lookup
$predictions = array();
foreach ($db_records as $r) {
    $predictions[strtolower($r->zodiac_sign)] = $r;
}

$zodiac_signs = cv_get_zodiac_list();
$formatted_date = cv_format_date($target_date);

$consultation_url = cv_get_setting('consultation_url', 'https://clairvoyantofficial.com/services/#booking');
$consultation_btn_text = cv_get_setting('consultation_btn_text', 'Book Consultation');

?>

<div class="cv-rashi-fal-section">
    <div class="cv-rashi-fal-container">
        
        <!-- Heading -->
        <div class="cv-rashi-fal-heading">
            <h2>Today's Rashi Fal</h2>
            <p>Discover what the stars have in store for you today - <?php echo esc_html($formatted_date); ?></p>
        </div>
        
        <!-- Desktop Grid -->
        <div class="cv-rashi-fal-grid">
            <?php foreach ($zodiac_signs as $key => $sign) : 
                $has_data = isset($predictions[$key]);
                $pred_text = $has_data ? strip_tags($predictions[$key]->prediction) : 'No prediction available for today.';
                $short_pred = mb_strimwidth($pred_text, 0, 110, '...');
                $rating = $has_data ? (int) $predictions[$key]->today_luck_rating : 0;
                
                // Details for modal trigger
                $full_prediction = $has_data ? wp_kses_post($predictions[$key]->prediction) : '';
                $lucky_number = $has_data ? esc_attr($predictions[$key]->lucky_number) : '';
                $lucky_color = $has_data ? esc_attr($predictions[$key]->lucky_color) : '';
                $career = $has_data ? esc_attr($predictions[$key]->career) : '';
                $love = $has_data ? esc_attr($predictions[$key]->love) : '';
                $health = $has_data ? esc_attr($predictions[$key]->health) : '';
                $finance = $has_data ? esc_attr($predictions[$key]->finance) : '';
                ?>
                <div class="cv-rashi-fal-card <?php echo $has_data ? 'cv-clickable-card' : 'cv-disabled-card'; ?>"
                     data-sign="<?php echo esc_attr($key); ?>"
                     data-name="<?php echo esc_attr($sign['name']); ?>"
                     data-icon="<?php echo esc_attr($sign['icon']); ?>"
                     data-date="<?php echo esc_attr($formatted_date); ?>"
                     data-prediction="<?php echo esc_attr($full_prediction); ?>"
                     data-lucky-number="<?php echo esc_attr($lucky_number); ?>"
                     data-lucky-color="<?php echo esc_attr($lucky_color); ?>"
                     data-career="<?php echo esc_attr($career); ?>"
                     data-love="<?php echo esc_attr($love); ?>"
                     data-health="<?php echo esc_attr($health); ?>"
                     data-finance="<?php echo esc_attr($finance); ?>"
                     data-rating="<?php echo esc_attr($rating); ?>">
                    
                    <div class="cv-rashi-fal-icon"><?php echo esc_html($sign['icon']); ?></div>
                    <h3 class="cv-rashi-fal-name"><?php echo esc_html($sign['name']); ?></h3>
                    <div class="cv-rashi-fal-today-date"><?php echo esc_html($formatted_date); ?></div>
                    <p class="cv-rashi-fal-prediction"><?php echo esc_html($short_pred); ?></p>
                    
                    <div class="cv-rashi-fal-rating">
                        <span>Luck</span>
                        <div class="stars">
                            <?php for ($i = 1; $i <= 5; $i++) : ?>
                                <span class="<?php echo $i <= $rating ? 'filled' : ''; ?>">★</span>
                            <?php endfor; ?>
                        </div>
                    </div>
                    
                    <a href="#" class="cv-rashi-fal-card-btn">Explore Full Rashi Fal</a>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Mobile Symbol Grid -->
        <div class="cv-rashi-fal-mobile-grid">
            <?php foreach ($zodiac_signs as $key => $sign) : 
                $has_data = isset($predictions[$key]);
                $rating = $has_data ? (int) $predictions[$key]->today_luck_rating : 0;
                $full_prediction = $has_data ? wp_kses_post($predictions[$key]->prediction) : '';
                $lucky_number = $has_data ? esc_attr($predictions[$key]->lucky_number) : '';
                $lucky_color = $has_data ? esc_attr($predictions[$key]->lucky_color) : '';
                $career = $has_data ? esc_attr($predictions[$key]->career) : '';
                $love = $has_data ? esc_attr($predictions[$key]->love) : '';
                $health = $has_data ? esc_attr($predictions[$key]->health) : '';
                $finance = $has_data ? esc_attr($predictions[$key]->finance) : '';
                ?>
                <div class="cv-rashi-fal-mobile-card <?php echo $has_data ? 'cv-clickable-card' : 'cv-disabled-card'; ?>"
                     data-sign="<?php echo esc_attr($key); ?>"
                     data-name="<?php echo esc_attr($sign['name']); ?>"
                     data-icon="<?php echo esc_attr($sign['icon']); ?>"
                     data-date="<?php echo esc_attr($formatted_date); ?>"
                     data-prediction="<?php echo esc_attr($full_prediction); ?>"
                     data-lucky-number="<?php echo esc_attr($lucky_number); ?>"
                     data-lucky-color="<?php echo esc_attr($lucky_color); ?>"
                     data-career="<?php echo esc_attr($career); ?>"
                     data-love="<?php echo esc_attr($love); ?>"
                     data-health="<?php echo esc_attr($health); ?>"
                     data-finance="<?php echo esc_attr($finance); ?>"
                     data-rating="<?php echo esc_attr($rating); ?>">
                    
                    <div class="cv-rashi-fal-mobile-icon"><?php echo esc_html($sign['icon']); ?></div>
                    <span class="cv-rashi-fal-mobile-name"><?php echo esc_html($sign['name']); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Lightbox Modal container -->
<div class="cv-modal-lightbox" id="cv-rashi-lightbox">
    <div class="cv-lightbox-content">
        <button class="cv-lightbox-close" id="cv-lightbox-close-btn">&times;</button>
        <div class="cv-lightbox-header">
            <span class="cv-lightbox-icon" id="cv-lightbox-icon">♈</span>
            <div>
                <h3 class="cv-lightbox-title" id="cv-lightbox-title">Aries</h3>
                <span class="cv-lightbox-date" id="cv-lightbox-date">June 30, 2026</span>
            </div>
        </div>
        <div class="cv-lightbox-body">
            <div class="cv-lightbox-prediction" id="cv-lightbox-prediction"></div>
            
            <div class="cv-lightbox-meta-grid">
                <div class="cv-lightbox-meta-item">
                    <strong>Lucky Number</strong>
                    <span id="cv-lightbox-lucky-number">5</span>
                </div>
                <div class="cv-lightbox-meta-item">
                    <strong>Lucky Color</strong>
                    <span id="cv-lightbox-lucky-color" style="display:inline-flex; align-items:center; gap:6px;">
                        <span class="color-dot" id="cv-lightbox-color-dot"></span>
                        <span id="cv-lightbox-color-name">#C8A96A</span>
                    </span>
                </div>
                <div class="cv-lightbox-meta-item">
                    <strong>Luck Rating</strong>
                    <div class="stars" id="cv-lightbox-rating-stars">★★★★★</div>
                </div>
            </div>

            <!-- Life Areas Tabs -->
            <div class="cv-lightbox-life-areas">
                <h4 class="cv-lightbox-life-title">Life Highlights</h4>
                <div class="cv-lightbox-areas-grid">
                    <div class="cv-lightbox-area-card" id="cv-lightbox-career-card">
                        <h5>💼 Career</h5>
                        <p id="cv-lightbox-career"></p>
                    </div>
                    <div class="cv-lightbox-area-card" id="cv-lightbox-love-card">
                        <h5>❤️ Love</h5>
                        <p id="cv-lightbox-love"></p>
                    </div>
                    <div class="cv-lightbox-area-card" id="cv-lightbox-health-card">
                        <h5>🩺 Health</h5>
                        <p id="cv-lightbox-health"></p>
                    </div>
                    <div class="cv-lightbox-area-card" id="cv-lightbox-finance-card">
                        <h5>💰 Finance</h5>
                        <p id="cv-lightbox-finance"></p>
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
