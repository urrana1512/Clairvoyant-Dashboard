<?php
/**
 * Admin Menu Registration
 * 
 * Registers the Clairvoyant Core menu and handles main layout layouts routing
 * 
 * @package Clairvoyant_Core
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Clairvoyant_Admin_Menu {

    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', array($this, 'register_menu'));
    }

    /**
     * Registers all main and submenus
     */
    public function register_menu() {
        // Parent Main Menu
        add_menu_page(
            __('Clairvoyant Core', 'clairvoyant-core'),
            __('Clairvoyant Core', 'clairvoyant-core'),
            'manage_options',
            'clairvoyant-dashboard',
            array($this, 'render_dashboard'),
            'dashicons-translation',
            25
        );

        // Dashboard Submenu
        add_submenu_page(
            'clairvoyant-dashboard',
            __('Dashboard', 'clairvoyant-core'),
            __('Dashboard', 'clairvoyant-core'),
            'manage_options',
            'clairvoyant-dashboard',
            array($this, 'render_dashboard')
        );

        // Daily Rashi Fal submenus are registered dynamically by the Daily Rashi Class, 
        // but we can register placeholders here or let each module class handle its own menu hooks.
        // Letting modules register their submenus under 'clairvoyant-dashboard' is extremely clean!
    }

    /**
     * Renders the base wrapper container for all dashboard screens
     * 
     * @param string $content_callback Function callback that renders the actual page view content
     */
    public static function render_layout_wrapper($content_callback) {
        $current_page = isset($_GET['page']) ? sanitize_key($_GET['page']) : 'clairvoyant-dashboard';
        $user = wp_get_current_user();
        $logo_url = cv_get_setting('logo_url', '');
        ?>
        <div class="cv-dashboard-wrapper">
            <!-- Header Bar -->
            <header class="cv-header-bar">
                <div class="cv-logo-area">
                    <button class="cv-menu-toggle" id="cv-menu-toggle">☰</button>
                    <?php if (!empty($logo_url)) : ?>
                        <img src="<?php echo esc_url($logo_url); ?>" class="cv-header-logo" alt="Logo">
                    <?php else : ?>
                        <span class="cv-logo-icon">✨</span>
                    <?php endif; ?>
                    <span class="cv-logo-text">Clairvoyant Core</span>
                </div>
                <div class="cv-header-right">
                    <div class="cv-user-badge">
                        Logged in as: <strong><?php echo esc_html($user->display_name); ?></strong>
                    </div>
                    <a href="<?php echo esc_url(wp_logout_url(admin_url('index.php'))); ?>" class="cv-form-button secondary" style="padding: 6px 12px; font-size: 12px; height: auto;">Logout</a>
                </div>
            </header>

            <div class="cv-layout">
                <!-- Sidebar Menu Navigation -->
                <aside class="cv-sidebar">
                    <ul class="cv-sidebar-menu">
                        <!-- Dashboard -->
                        <li class="cv-menu-item <?php echo $current_page === 'clairvoyant-dashboard' ? 'active' : ''; ?>">
                            <a href="<?php echo esc_url(admin_url('admin.php?page=clairvoyant-dashboard')); ?>" class="cv-menu-link">
                                <span class="cv-menu-icon">📊</span>
                                <span>Dashboard</span>
                            </a>
                        </li>

                        <!-- Daily Rashi Fal -->
                        <li class="cv-menu-item">
                            <span class="cv-menu-link <?php echo strpos($current_page, 'cv-daily-rashi') !== false ? 'parent-active' : ''; ?>">
                                <span class="cv-menu-icon">♈</span>
                                <span>Daily Rashi Fal</span>
                            </span>
                            <ul class="cv-submenu">
                                <li class="cv-submenu-item <?php echo $current_page === 'cv-daily-rashi-add' ? 'active' : ''; ?>">
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=cv-daily-rashi-add')); ?>" class="cv-submenu-link">Add New</a>
                                </li>
                                <li class="cv-submenu-item <?php echo $current_page === 'cv-daily-rashi-manage' ? 'active' : ''; ?>">
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=cv-daily-rashi-manage')); ?>" class="cv-submenu-link">Manage</a>
                                </li>
                            </ul>
                        </li>

                        <!-- Horoscope -->
                        <li class="cv-menu-item">
                            <span class="cv-menu-link <?php echo strpos($current_page, 'cv-horoscope') !== false ? 'parent-active' : ''; ?>">
                                <span class="cv-menu-icon">🔮</span>
                                <span>Horoscope</span>
                            </span>
                            <ul class="cv-submenu">
                                <!-- Daily Horoscope -->
                                <li class="cv-submenu-item-group-title">Daily Horoscope</li>
                                <li class="cv-submenu-item <?php echo $current_page === 'cv-horoscope-daily-add' ? 'active' : ''; ?>">
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=cv-horoscope-daily-add')); ?>" class="cv-submenu-link" style="padding-left: 28px;">Add New</a>
                                </li>
                                <li class="cv-submenu-item <?php echo $current_page === 'cv-horoscope-daily-manage' ? 'active' : ''; ?>">
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=cv-horoscope-daily-manage')); ?>" class="cv-submenu-link" style="padding-left: 28px;">Manage</a>
                                </li>
                                
                                <!-- Weekly Horoscope -->
                                <li class="cv-submenu-item-group-title" style="margin-top: 10px;">Weekly Horoscope</li>
                                <li class="cv-submenu-item <?php echo $current_page === 'cv-horoscope-weekly-add' ? 'active' : ''; ?>">
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=cv-horoscope-weekly-add')); ?>" class="cv-submenu-link" style="padding-left: 28px;">Add New</a>
                                </li>
                                <li class="cv-submenu-item <?php echo $current_page === 'cv-horoscope-weekly-manage' ? 'active' : ''; ?>">
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=cv-horoscope-weekly-manage')); ?>" class="cv-submenu-link" style="padding-left: 28px;">Manage</a>
                                </li>

                                <!-- Transit Horoscope -->
                                <li class="cv-submenu-item-group-title" style="margin-top: 10px;">Transit Horoscope</li>
                                <li class="cv-submenu-item <?php echo $current_page === 'cv-horoscope-transit-add' ? 'active' : ''; ?>">
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=cv-horoscope-transit-add')); ?>" class="cv-submenu-link" style="padding-left: 28px;">Add New</a>
                                </li>
                                <li class="cv-submenu-item <?php echo $current_page === 'cv-horoscope-transit-manage' ? 'active' : ''; ?>">
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=cv-horoscope-transit-manage')); ?>" class="cv-submenu-link" style="padding-left: 28px;">Manage</a>
                                </li>
                        <!-- 24-48 Hrs Prediction -->
                        <li class="cv-menu-item">
                            <span class="cv-menu-link <?php echo strpos($current_page, 'cv-prediction-24h') !== false ? 'parent-active' : ''; ?>">
                                <span class="cv-menu-icon">⏳</span>
                                <span>24-48 Hrs Prediction</span>
                            </span>
                            <ul class="cv-submenu">
                                <li class="cv-submenu-item <?php echo $current_page === 'cv-prediction-24h-add' ? 'active' : ''; ?>">
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=cv-prediction-24h-add')); ?>" class="cv-submenu-link">Add New</a>
                                </li>
                                <li class="cv-submenu-item <?php echo $current_page === 'cv-prediction-24h-manage' ? 'active' : ''; ?>">
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=cv-prediction-24h-manage')); ?>" class="cv-submenu-link">Manage</a>
                                </li>
                            </ul>
                        </li>

                        <!-- Testimonials -->
                        <li class="cv-menu-item">
                            <span class="cv-menu-link <?php echo strpos($current_page, 'cv-testimonials') !== false ? 'parent-active' : ''; ?>">
                                <span class="cv-menu-icon">💬</span>
                                <span>Testimonials</span>
                            </span>
                            <ul class="cv-submenu">
                                <li class="cv-submenu-item <?php echo $current_page === 'cv-testimonials-add' ? 'active' : ''; ?>">
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=cv-testimonials-add')); ?>" class="cv-submenu-link">Add New</a>
                                </li>
                                <li class="cv-submenu-item <?php echo $current_page === 'cv-testimonials-manage' ? 'active' : ''; ?>">
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=cv-testimonials-manage')); ?>" class="cv-submenu-link">Manage</a>
                                </li>
                            </ul>
                        </li>

                        <!-- Settings -->
                        <li class="cv-menu-item <?php echo $current_page === 'cv-settings' ? 'active' : ''; ?>">
                            <a href="<?php echo esc_url(admin_url('admin.php?page=cv-settings')); ?>" class="cv-menu-link">
                                <span class="cv-menu-icon">⚙️</span>
                                <span>Settings</span>
                            </a>
                        </li>
                    </ul>
                </aside>

                <!-- Page content container -->
                <main class="cv-main-content">
                    <?php 
                    if (is_callable($content_callback)) {
                        call_user_func($content_callback);
                    }
                    ?>
                </main>
            </div>
        </div>
        <?php
    }

    /**
     * Route handler for rendering dashboard main screen
     */
    public function render_dashboard() {
        self::render_layout_wrapper(function() {
            require_once CLAIRVOYANT_PLUGIN_DIR . 'admin/dashboard.php';
        });
    }
}
