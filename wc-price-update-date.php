<?php
/**
 * Plugin Name: WooCommerce Price Update Date
 * Plugin URI: https://github.com/hypercircletech/WooCommerce-Price-Update-Date
 * Description: Displays the last updated date of a WooCommerce product after the price.
 * Version: 1.5.0
 * Author: Hypercircle Technology
 * Author URI: https://hypercircle.tech
 * License: GPL v3
 * Text Domain: wc-price-update-date
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) exit;

add_action('woocommerce_single_product_summary', 'wc_price_update_date_display', 25);

function wc_price_update_date_display() {
    global $product;
    $updated_date = get_post_modified_time('d/m/Y', false, $product->get_id());
    echo '<p style="color: #FF0000; font-size: 12px;">Updated on ' . $updated_date . '</p>';
}

if (is_admin()) {
    add_filter('woocommerce_get_settings_products', 'wc_price_update_date_settings', 10, 2);
}

function wc_price_update_date_settings($settings, $current_section) {
    if ($current_section === 'price_update_date') {
        $settings = [
            [
                'title' => 'Price Update Date Settings',
                'type' => 'title',
                'desc' => 'Customize settings for the price update date display.',
                'id' => 'price_update_date_settings',
            ],
            [
                'title' => 'Text Color',
                'desc' => 'Choose the color of the update date text.',
                'id' => 'price_update_date_color',
                'type' => 'color',
                'default' => '#FF0000',
                'css' => 'width: 70px;',
            ],
            [
                'type' => 'sectionend',
                'id' => 'price_update_date_settings',
            ],
        ];
    }
    return $settings;
}

function wc_price_update_date_update_color() {
    $color = get_option('price_update_date_color', '#FF0000');
    echo '<style>.woocommerce div.product p { color: ' . esc_attr($color) . '; }</style>';
}
add_action('wp_head', 'wc_price_update_date_update_color');

if (!class_exists('WC_Price_Update_Date_Updater')) {
    class WC_Price_Update_Date_Updater {
        private $api_url = 'https://plugin.hypercircle.tech/wc-price-update-date/metadata.json';
        private $plugin_file;

        public function __construct($plugin_file) {
            $this->plugin_file = $plugin_file;
            add_filter('pre_set_site_transient_update_plugins', [$this, 'check_for_update']);
            add_filter('plugins_api', [$this, 'plugin_info'], 10, 3);
        }

        public function check_for_update($transient) {
            if (empty($transient->checked)) return $transient;
            $plugin_data = get_plugin_data($this->plugin_file);
            $plugin_slug = plugin_basename($this->plugin_file);
            $response = wp_remote_get($this->api_url);
            if (is_wp_error($response)) return $transient;
            $data = json_decode(wp_remote_retrieve_body($response));
            if (empty($data)) return $transient;
            if (version_compare($plugin_data['Version'], $data->version, '<')) {
                $transient->response[$plugin_slug] = (object) [
                    'slug' => $plugin_slug,
                    'new_version' => $data->version,
                    'url' => $data->download_url,
                    'package' => $data->download_url,
                ];
            }
            return $transient;
        }

        public function plugin_info($res, $action, $args) {
            if ($action !== 'plugin_information') return $res;
            if ($args->slug !== plugin_basename($this->plugin_file)) return $res;
            $response = wp_remote_get($this->api_url);
            if (is_wp_error($response)) return $res;
            $data = json_decode(wp_remote_retrieve_body($response));
            if (empty($data)) return $res;
            $res = (object) [
                'name' => 'WooCommerce Price Update Date',
                'slug' => plugin_basename($this->plugin_file),
                'version' => $data->version,
                'author' => '<a href="https://hypercircle.tech">Hypercircle Technology</a>',
                'homepage' => 'https://plugin.hypercircle.tech/wc-price-update-date/',
                'download_link' => $data->download_url,
                'sections' => [
                    'description' => 'Displays the last updated date of a WooCommerce product after the price.',
                ],
            ];
            return $res;
        }
    }
    new WC_Price_Update_Date_Updater(__FILE__);
}
