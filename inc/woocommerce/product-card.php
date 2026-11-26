<?php
defined('ABSPATH')||exit;

/**
 * Compact price data for product cards.
 *
 * WooCommerce's default price HTML prints both the regular and sale range for
 * variable products ("65 – 90" struck through next to "55 – 80"), which is long
 * and gets clipped inside a card. Cards only need the entry price and the
 * saving, so this helper returns:
 *
 * - the lowest current price, prefixed with "از" for variable products;
 * - the strongest discount percentage (marked as "تا" when variations differ).
 */
function bluprint_product_card_price($product){
    $result=[
        'html'=>'',
        'discount'=>0,
        'up_to'=>false,
    ];

    if(!$product instanceof WC_Product)return $result;

    if($product->is_type('variable')){
        $prices=$product->get_variation_prices(true);

        if(empty($prices['price'])||!is_array($prices['price']))return $result;

        $min=(float)current($prices['price']);
        $max=(float)end($prices['price']);
        $discount=0;

        foreach($prices['price'] as $variation_id=>$price){
            $price=(float)$price;
            $regular=(float)($prices['regular_price'][$variation_id]??0);

            if($regular<=0||$price<=0||$price>=$regular)continue;

            $percent=(int)round((1-($price/$regular))*100);
            if($percent>$discount)$discount=$percent;
        }

        $result['discount']=min(99,$discount);
        $result['up_to']=$discount>0;
        $result['html']=($min!==$max?'از ':'').wc_price($min);

        return $result;
    }

    $display_price=(float)wc_get_price_to_display($product);
    $price=(float)$product->get_price('edit');
    $regular=(float)$product->get_regular_price('edit');

    if($product->is_on_sale()&&$regular>0&&$price>0&&$price<$regular){
        $result['discount']=min(99,(int)round((1-($price/$regular))*100));
    }

    if($product->get_price()!==''){
        $result['html']=wc_price($display_price);
    }else{
        // Grouped, external or otherwise unpriced products keep their own HTML.
        $fallback=$product->get_price_html();
        if($fallback!=='')$result['html']=$fallback;
    }

    return $result;
}
