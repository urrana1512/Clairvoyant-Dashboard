<?php
/**
 * Testimonials Frontend Shortcode Template
 * 
 * Renders testimonials as a responsive grid, sliding carousel, or infinity marquee scroller
 * 
 * @package Clairvoyant_Core
 * @subpackage Frontend
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$limit = isset($atts['limit']) ? (int) $atts['limit'] : 12; // Increase default limit to allow more testimonials if added
$style = isset($atts['style']) ? sanitize_key($atts['style']) : 'grid';

require_once CLAIRVOYANT_PLUGIN_DIR . 'modules/testimonials/database.php';
$records = cv_get_testimonials(array(
    'status' => 'publish',
    'limit'  => $limit
));

?>

<div class="cv-testimonials-section style-<?php echo count($records) > 3 ? 'marquee' : esc_attr($style); ?>">
    <div class="cv-testimonials-container">
        
        <div class="cv-testimonials-heading">
            <h2>What Our Clients Say</h2>
            <p>Read inspiring experiences shared by clients who consulted our astrologers.</p>
        </div>

        <?php if (!empty($records)) : ?>
            <?php if (count($records) > 3) : ?>
                <!-- Infinity Marquee Carousel Layout -->
                <div class="cv-testimonials-marquee-wrapper">
                    <div class="cv-testimonials-marquee-track">
                        <div class="cv-testimonials-marquee-group">
                            <?php foreach ($records as $row) : 
                                $avatar = !empty($row->client_image) ? $row->client_image : CLAIRVOYANT_PLUGIN_URL . 'assets/images/default-avatar.png';
                                ?>
                                <div class="cv-testimonial-card">
                                    <div class="cv-testimonial-quote">“</div>
                                    <div class="cv-testimonial-review">
                                        <?php echo wp_kses_post($row->review); ?>
                                    </div>
                                    <div class="cv-testimonial-client">
                                        <img src="<?php echo esc_url($avatar); ?>" class="cv-testimonial-avatar" alt="<?php echo esc_attr($row->client_name); ?>">
                                        <div>
                                            <h4 class="cv-testimonial-name"><?php echo esc_html($row->client_name); ?></h4>
                                            <span class="cv-testimonial-meta">
                                                <?php echo esc_html($row->service); ?> 
                                                <?php echo $row->location ? '• ' . esc_html($row->location) : ''; ?>
                                            </span>
                                            <div class="cv-testimonial-rating">
                                                <?php for ($i = 1; $i <= 5; $i++) : ?>
                                                    <span class="<?php echo $i <= $row->rating ? 'filled' : ''; ?>">★</span>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <!-- Duplicate set for seamless scrolling -->
                        <div class="cv-testimonials-marquee-group" aria-hidden="true">
                            <?php foreach ($records as $row) : 
                                $avatar = !empty($row->client_image) ? $row->client_image : CLAIRVOYANT_PLUGIN_URL . 'assets/images/default-avatar.png';
                                ?>
                                <div class="cv-testimonial-card">
                                    <div class="cv-testimonial-quote">“</div>
                                    <div class="cv-testimonial-review">
                                        <?php echo wp_kses_post($row->review); ?>
                                    </div>
                                    <div class="cv-testimonial-client">
                                        <img src="<?php echo esc_url($avatar); ?>" class="cv-testimonial-avatar" alt="<?php echo esc_attr($row->client_name); ?>">
                                        <div>
                                            <h4 class="cv-testimonial-name"><?php echo esc_html($row->client_name); ?></h4>
                                            <span class="cv-testimonial-meta">
                                                <?php echo esc_html($row->service); ?> 
                                                <?php echo $row->location ? '• ' . esc_html($row->location) : ''; ?>
                                            </span>
                                            <div class="cv-testimonial-rating">
                                                <?php for ($i = 1; $i <= 5; $i++) : ?>
                                                    <span class="<?php echo $i <= $row->rating ? 'filled' : ''; ?>">★</span>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php elseif ($style === 'carousel') : ?>
                <!-- Carousel Layout (for 3 or fewer testimonials) -->
                <div class="cv-testimonials-carousel-wrapper">
                    <div class="cv-testimonials-carousel" id="cv-testimonials-carousel-slider">
                        <?php foreach ($records as $row) : 
                            $avatar = !empty($row->client_image) ? $row->client_image : CLAIRVOYANT_PLUGIN_URL . 'assets/images/default-avatar.png';
                            ?>
                            <div class="cv-testimonial-slide">
                                <div class="cv-testimonial-card">
                                    <div class="cv-testimonial-quote">“</div>
                                    <div class="cv-testimonial-review">
                                        <?php echo wp_kses_post($row->review); ?>
                                    </div>
                                    <div class="cv-testimonial-client">
                                        <img src="<?php echo esc_url($avatar); ?>" class="cv-testimonial-avatar" alt="<?php echo esc_attr($row->client_name); ?>">
                                        <div>
                                            <h4 class="cv-testimonial-name"><?php echo esc_html($row->client_name); ?></h4>
                                            <span class="cv-testimonial-meta">
                                                <?php echo esc_html($row->service); ?> 
                                                <?php echo $row->location ? '• ' . esc_html($row->location) : ''; ?>
                                            </span>
                                            <div class="cv-testimonial-rating">
                                                <?php for ($i = 1; $i <= 5; $i++) : ?>
                                                    <span class="<?php echo $i <= $row->rating ? 'filled' : ''; ?>">★</span>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Carousel Controls -->
                    <div class="cv-carousel-controls">
                        <button class="cv-carousel-arrow prev" id="cv-carousel-prev-arrow">‹</button>
                        <div class="cv-carousel-dots" id="cv-carousel-dots-container"></div>
                        <button class="cv-carousel-arrow next" id="cv-carousel-next-arrow">›</button>
                    </div>
                </div>
            <?php else : ?>
                <!-- Grid Layout (for 3 or fewer testimonials) -->
                <div class="cv-testimonials-grid">
                    <?php foreach ($records as $row) : 
                        $avatar = !empty($row->client_image) ? $row->client_image : CLAIRVOYANT_PLUGIN_URL . 'assets/images/default-avatar.png';
                        ?>
                        <div class="cv-testimonial-card">
                            <div class="cv-testimonial-quote">“</div>
                            <div class="cv-testimonial-review">
                                <?php echo wp_kses_post($row->review); ?>
                            </div>
                            <div class="cv-testimonial-client">
                                <img src="<?php echo esc_url($avatar); ?>" class="cv-testimonial-avatar" alt="<?php echo esc_attr($row->client_name); ?>">
                                <div>
                                    <h4 class="cv-testimonial-name"><?php echo esc_html($row->client_name); ?></h4>
                                    <span class="cv-testimonial-meta">
                                        <?php echo esc_html($row->service); ?> 
                                        <?php echo $row->location ? '• ' . esc_html($row->location) : ''; ?>
                                    </span>
                                    <div class="cv-testimonial-rating">
                                        <?php for ($i = 1; $i <= 5; $i++) : ?>
                                            <span class="<?php echo $i <= $row->rating ? 'filled' : ''; ?>">★</span>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php else : ?>
            <p class="description" style="text-align:center;">No published reviews yet.</p>
        <?php endif; ?>

    </div>
</div>
