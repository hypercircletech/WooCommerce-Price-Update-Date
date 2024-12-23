<?php
/**
 * Plugin Name: WooCommerce Price Update Date
 * Plugin URI: https://hypercircle.tech
 * Description: Displays the last updated date after the price on WooCommerce product pages with customizable color from the dashboard.
 * Version: 1.3.0
 * Author: Hypercircle Technology
 * Author URI: https://hypercircle.tech
 * License: GPL v3
 */

if (!defined('ABSPATH')) {
    exit;
}

// Add the color setting to WooCommerce settings.
add_filter('woocommerce_get_sections_products', 'wc_add_price_update_date_section');
function wc_add_price_update_date_section($sections) {
    $sections['price_update_date'] = __('Price Update Date', 'wc-price-update-date');
    return $sections;
}

add_filter('woocommerce_get_settings_products', 'wc_add_price_update_date_settings', 10, 2);
function wc_add_price_update_date_settings($settings, $current_section) {
    if ($current_section === 'price_update_date') {
        $settings = [
            [
                'title' => __('Price Update Date Settings', 'wc-price-update-date'),
                'type'  => 'title',
                'desc'  => __('Customize the display of the price update date.', 'wc-price-update-date'),
                'id'    => 'price_update_date_settings',
            ],
            [
                'title'    => __('Text Color', 'wc-price-update-date'),
                'desc'     => __('Set the color of the "Updated on" text.', 'wc-price-update-date'),
                'id'       => 'wc_price_update_date_color',
                'type'     => 'color',
                'default'  => '#FF0000', 
                'desc_tip' => true,
            ],
            [
                'type' => 'sectionend',
                'id'   => 'price_update_date_settings',
            ],
        ];
    }
    return $settings;
}


add_filter('woocommerce_get_price_html', 'wc_add_price_update_date', 10, 2);
function wc_add_price_update_date($price, $product) {
  
    if (!is_product()) {
        return $price;
    }

  
    $updated_date = get_the_modified_date('d/m/Y', $product->get_id());

   
    $color = get_option('wc_price_update_date_color', '#FF0000'); 

   
    $price .= '<br><small style="display:block; color: ' . esc_attr($color) . '; font-size: 12px;">Updated on ' . esc_html($updated_date) . '</small>';

    return $price;
}
