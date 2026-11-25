<?php
defined('ABSPATH')||exit;

/**
 * Display numbers with English digits across the site.
 *
 * The installation uses Persian localisation, which can convert numbers in
 * prices, dates, counts and post content. The theme consistently uses Latin
 * digits, so normalise those values without changing stored data.
 */
function bluprint_english_digits($value){
    if(!is_string($value)||$value==='')return $value;

    static $map=null;

    if($map===null){
        $map=[
            '۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4',
            '۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9',
            '٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4',
            '٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9',
        ];
    }

    return strtr($value,$map);
}

/**
 * Convert digits in visible HTML text nodes only, leaving attributes (URLs,
 * IDs and input values) untouched.
 */
function bluprint_english_digits_in_html($html){
    if(!is_string($html)||$html==='')return $html;

    $converted=preg_replace_callback(
        '/>([^<>]+)</u',
        static function($matches){
            return '>'.bluprint_english_digits($matches[1]).'<';
        },
        $html
    );

    return $converted??$html;
}

/**
 * Normalise the final front-end document. Buffering is skipped for AJAX,
 * REST, feeds and previews; prices there are covered by the i18n filter.
 */
function bluprint_start_english_digit_buffer(){
    if(is_admin()||wp_doing_ajax()||is_feed()||is_preview())return;
    if(defined('REST_REQUEST')&&REST_REQUEST)return;

    ob_start('bluprint_english_digits_in_html');
}
add_action('template_redirect','bluprint_start_english_digit_buffer',100);

/**
 * Keep localized numbers produced by WordPress and plugins in Latin digits,
 * including AJAX-rendered cart fragments.
 */
add_filter('number_format_i18n','bluprint_english_digits',1001);

/**
 * WooCommerce uses this string to compose prices. In an RTL document, placing
 * the amount first puts the currency symbol visually on the left.
 */
function bluprint_currency_sign_left($format,$position){
    return '%2$s %1$s';
}
add_filter('woocommerce_price_format','bluprint_currency_sign_left',20,2);