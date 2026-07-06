# Clairvoyant Core 🌟

[![WordPress Version](https://img.shields.io/badge/WordPress-5.9+-blue.svg)](https://wordpress.org)
[![PHP Version](https://img.shields.io/badge/PHP-8.0+-purple.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-GPL--2.0%2B-green.svg)](https://www.gnu.org/licenses/gpl-2.0.txt)

**Clairvoyant Core** is a robust, features-packed astrology dashboard plugin for WordPress. It allows administrators to manage, schedule, and showcase daily rashi predictions, daily and weekly horoscopes, planetary transit forecasts, and client testimonials. It provides a complete management system in the backend alongside responsive frontend templates and REST API endpoints.

---

## 🚀 Key Features

* **Daily Rashi (Moon Sign Predictions):** Publish daily moon sign forecasts complete with lucky numbers, lucky colors, luck ratings, and specific breakdowns for Career, Love, Health, and Finance.
* **Daily & Weekly Horoscopes:** Easily manage and display daily and weekly predictions categorized by zodiac signs.
* **Transit Horoscopes:** Keep users updated with planetary transits, including start/end dates, affected signs, predictions, and suggested remedies.
* **Testimonials Manager:** Curate client feedback and render them cleanly on your site.
* **WordPress REST API Integration:** Provides custom API endpoints for seamless frontend rendering or headless integration.
* **Transient Caching:** Fast loading times via automatic caching of frontend widgets.

---

## 🛠 Installation & Requirements

* **WordPress Version:** 5.9 or higher
* **PHP Version:** 8.0 or higher

1. Download the plugin folder and upload it to the `/wp-content/plugins/` directory of your WordPress installation, or upload the ZIP file directly via the WordPress admin panel.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Upon activation, the plugin automatically creates the necessary database tables.

---

## 🗃 Custom Database Tables

Clairvoyant Core is built to scale by using custom, indexed database tables rather than bloating the default `wp_options` or `wp_postmeta` tables:

* `{wp_prefix}_cv_daily_rashi` — Stores daily rashi predictions.
* `{wp_prefix}_cv_daily_horoscope` — Stores daily horoscope details.
* `{wp_prefix}_cv_weekly_horoscope` — Stores weekly horoscope intervals.
* `{wp_prefix}_cv_transit_horoscope` — Stores planetary transits and remedies.
* `{wp_prefix}_cv_testimonials` — Stores client reviews and ratings.
* `{wp_prefix}_cv_settings` — Stores administrative configuration options.

---

## 🔌 Shortcodes Guide

You can render astrology widgets anywhere on your site using the following shortcodes:

### 1. Daily Rashi Widget
Displays daily predictions for moon signs.
```text
[cv_daily_rashi zodiac="" date="" limit="12"]
```
* **Parameters:**
  * `zodiac` (string): Filter by a specific sign (e.g. `Aries`). Default: Empty (shows all).
  * `date` (string): Specific date (`YYYY-MM-DD`). Default: Today.
  * `limit` (int): Number of signs to display. Default: `12`.

### 2. Daily Horoscope Widget
```text
[cv_daily_horoscope zodiac="" date=""]
```

### 3. Weekly Horoscope Widget
```text
[cv_weekly_horoscope zodiac="" week_start=""]
```

### 4. Transit Horoscope Widget
Displays planetary transits.
```text
[cv_transit_horoscope limit="5"]
```

### 5. Unified Horoscope (Tabs Widget)
Renders a clean tabbed container matching the active theme's styling where users can toggle between Today, Weekly, and Transit views.
```text
[cv_horoscope default_tab="today" limit="5"]
```
* **Parameters:**
  * `default_tab` (string): The tab active on load (`today`, `weekly`, or `transit`). Default: `today`.

### 6. Testimonials Widget
Displays client testimonials in a modern format.
```text
[cv_testimonials style="grid" limit="6"]
```
* **Parameters:**
  * `style` (string): Display layout. Choose `grid` or `carousel`. Default: `grid`.
  * `limit` (int): Maximum testimonials to show. Default: `6`.

---

## 🌐 REST API Endpoints

The plugin registers custom REST API routes under the `clairvoyant/v1` namespace. All frontend templates use these endpoints to perform fast actions or load dynamic content:

* `GET /wp-json/clairvoyant/v1/rashi` — Fetch daily rashi data.
* `GET /wp-json/clairvoyant/v1/horoscope` — Fetch daily/weekly horoscope data.
* `GET /wp-json/clairvoyant/v1/transit` — Fetch planetary transits.
* `GET /wp-json/clairvoyant/v1/testimonials` — Fetch client testimonials.
