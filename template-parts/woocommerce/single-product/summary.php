<?php
defined('ABSPATH')||exit;
global $product;
if(!$product instanceof WC_Product)return;
do_action('woocommerce_single_product_summary');