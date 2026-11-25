<?php
defined('ABSPATH')||exit;

/**
 * Theme-level SEO fallbacks.
 *
 * These run only when a dedicated SEO plugin is not active, so Rank Math or
 * another plugin always remains the source of truth when present.
 */

function bluprint_seo_plugin_active(){
    return defined('RANK_MATH_VERSION')
        || defined('WPSEO_VERSION')
        || defined('AIOSEO_VERSION')
        || defined('SEOPRESS_VERSION');
}

function bluprint_seo_is_indexable(){
    if(is_preview()||is_feed()||is_404()||is_search())return false;

    if(function_exists('is_cart')&&(is_cart()||is_checkout()||is_account_page()))return false;
    if(function_exists('is_wc_endpoint_url')&&is_wc_endpoint_url())return false;

    return true;
}

function bluprint_seo_robots($robots){
    if(!is_array($robots)||!bluprint_seo_is_indexable())return $robots;

    unset($robots['noindex'],$robots['nofollow']);
    $robots['index']=true;
    $robots['follow']=true;
    $robots['max-image-preview']='large';
    $robots['max-snippet']='-1';
    $robots['max-video-preview']='-1';

    return $robots;
}
add_filter('wp_robots','bluprint_seo_robots',99);

function bluprint_seo_rank_math_robots($robots){
    if(!is_array($robots)||!bluprint_seo_is_indexable())return $robots;

    unset($robots['noindex'],$robots['nofollow']);
    $robots['index']='index';
    $robots['follow']='follow';

    return $robots;
}
add_filter('rank_math/frontend/robots','bluprint_seo_rank_math_robots',99);

function bluprint_seo_description(){
    $description='';

    if(is_front_page()){
        $description=get_bloginfo('description');
        if(trim((string)$description)===''){
            $description='گالری بلوپرینت؛ فروشگاه آنلاین ابزار طراحی، معماری و هنر؛ پک‌های آماده، محصولات کاربردی و راهنماهای تخصصی برای شروع سریع‌تر پروژه‌ها.';
        }
    }elseif(is_home()){
        $page_id=(int)get_option('page_for_posts');
        if($page_id)$description=get_the_excerpt($page_id);
        if(trim((string)$description)==='')$description='مقالات، راهنماها و نکته‌های کاربردی گالری بلوپرینت برای طراحی، معماری و خلق ایده‌های بهتر.';
    }elseif(function_exists('is_shop')&&is_shop()){
        if(function_exists('bluprint_shop_archive_settings')){
            $settings=bluprint_shop_archive_settings();
            $description=$settings['description']??'';
        }
    }elseif(function_exists('is_product')&&is_product()){
        global $product;
        if($product instanceof WC_Product){
            $description=$product->get_short_description();
            if(trim((string)$description)==='')$description=$product->get_description();
        }
    }elseif(function_exists('is_product_taxonomy')&&is_product_taxonomy()){
        $term=get_queried_object();
        if($term instanceof WP_Term)$description=term_description($term);
    }elseif(is_singular('bp_pack')){
        $post_id=(int)get_queried_object_id();
        $description=get_the_excerpt($post_id);
        if(trim((string)$description)==='')$description=get_post_field('post_content',$post_id);
    }elseif(is_tax('bp_pack_category')){
        $term=get_queried_object();
        if($term instanceof WP_Term)$description=term_description($term);
    }elseif(is_post_type_archive('bp_pack')){
        $description='پک‌های آماده گالری بلوپرینت؛ مجموعه‌هایی از ابزارها و محصولات هماهنگ برای شروع سریع‌تر پروژه‌های طراحی و معماری.';
    }elseif(is_singular('post')){
        $post_id=(int)get_queried_object_id();
        $description=get_the_excerpt($post_id);
        if(trim((string)$description)==='')$description=get_post_field('post_content',$post_id);
    }elseif(is_page()){
        $post_id=(int)get_queried_object_id();
        $description=get_the_excerpt($post_id);
        if(trim((string)$description)==='')$description=get_post_field('post_content',$post_id);
    }elseif(is_category()||is_tag()||is_tax()){
        $term=get_queried_object();
        if($term instanceof WP_Term){
            $description=term_description($term);
            if(trim(wp_strip_all_tags($description))==='')$description=$term->name.' در گالری بلوپرینت';
        }
    }

    $description=trim(wp_strip_all_tags(strip_shortcodes((string)$description)));

    if($description===''){
        $description=get_bloginfo('description');
    }

    if($description===''){
        $description='گالری بلوپرینت؛ ابزار طراحی، معماری و هنر.';
    }

    return wp_trim_words($description,32,'…');
}

function bluprint_seo_url(){
    if(is_front_page())return home_url('/');
    if(is_home()){
        $page_id=(int)get_option('page_for_posts');
        return $page_id?get_permalink($page_id):home_url('/');
    }
    if(function_exists('is_shop')&&is_shop()){
        $shop_id=function_exists('wc_get_page_id')?wc_get_page_id('shop'):0;
        if($shop_id>0)return get_permalink($shop_id);
    }
    if(is_singular())return get_permalink();
    if(is_category()||is_tag()||is_tax()){
        $term=get_queried_object();
        if($term instanceof WP_Term){
            $url=get_term_link($term);
            if(!is_wp_error($url))return $url;
        }
    }
    if(is_post_type_archive())return get_post_type_archive_link(get_query_var('post_type'));
    if(is_search())return get_search_link();

    return home_url('/');
}

function bluprint_seo_image(){
    $image_id=0;

    if(function_exists('is_product')&&is_product()){
        global $product;
        if($product instanceof WC_Product)$image_id=(int)$product->get_image_id();
    }

    if(!$image_id&&is_singular()){
        $image_id=(int)get_post_thumbnail_id();
    }

    if(!$image_id&&(is_tax()||is_category()||is_tag())){
        $term=get_queried_object();
        if($term instanceof WP_Term)$image_id=(int)get_term_meta($term->term_id,'thumbnail_id',true);
    }

    if($image_id){
        $url=wp_get_attachment_image_url($image_id,'large');
        if($url)return $url;
    }

    $logo_id=(int)get_theme_mod('custom_logo');
    if($logo_id){
        $url=wp_get_attachment_image_url($logo_id,'full');
        if($url)return $url;
    }

    $site_icon=get_site_icon_url(512);
    if($site_icon)return $site_icon;

    return get_theme_file_uri('assets/images/hero/hero-artwork.webp');
}

/**
 * Breadcrumb model shared by the visual fallback and JSON-LD.
 */
function bluprint_seo_breadcrumb_items(){
    $items=[];
    $home=['name'=>'خانه','url'=>home_url('/')];

    if(is_front_page())return [];

    $items[]=$home;

    if(function_exists('is_shop')&&is_shop()){
        $shop_id=wc_get_page_id('shop');
        $items[]=['name'=>get_the_title($shop_id),'url'=>get_permalink($shop_id)];
        return $items;
    }

    if(function_exists('is_product')&&is_product()){
        $shop_id=wc_get_page_id('shop');
        if($shop_id>0)$items[]=['name'=>get_the_title($shop_id),'url'=>get_permalink($shop_id)];
        $terms=get_the_terms(get_queried_object_id(),'product_cat');
        if($terms&&!is_wp_error($terms)){
            $term=$terms[0];
            foreach(array_reverse(get_ancestors($term->term_id,'product_cat')) as $ancestor_id){
                $ancestor=get_term($ancestor_id,'product_cat');
                if($ancestor&&!is_wp_error($ancestor))$items[]=['name'=>$ancestor->name,'url'=>get_term_link($ancestor)];
            }
            $items[]=['name'=>$term->name,'url'=>get_term_link($term)];
        }
        $items[]=['name'=>get_the_title(),'url'=>get_permalink()];
        return $items;
    }

    if(function_exists('is_product_taxonomy')&&is_product_taxonomy()){
        $shop_id=wc_get_page_id('shop');
        if($shop_id>0)$items[]=['name'=>get_the_title($shop_id),'url'=>get_permalink($shop_id)];
        $term=get_queried_object();
        if($term instanceof WP_Term){
            foreach(array_reverse(get_ancestors($term->term_id,$term->taxonomy)) as $ancestor_id){
                $ancestor=get_term($ancestor_id,$term->taxonomy);
                if($ancestor&&!is_wp_error($ancestor))$items[]=['name'=>$ancestor->name,'url'=>get_term_link($ancestor)];
            }
            $items[]=['name'=>$term->name,'url'=>get_term_link($term)];
        }
        return $items;
    }

    if(is_singular('bp_pack')){
        $archive_url=get_post_type_archive_link('bp_pack');
        $items[]=['name'=>'پک‌ها','url'=>$archive_url?:home_url('/')];
        $terms=get_the_terms(get_queried_object_id(),'bp_pack_category');
        if($terms&&!is_wp_error($terms)){
            $term=$terms[0];
            foreach(array_reverse(get_ancestors($term->term_id,'bp_pack_category')) as $ancestor_id){
                $ancestor=get_term($ancestor_id,'bp_pack_category');
                if($ancestor&&!is_wp_error($ancestor))$items[]=['name'=>$ancestor->name,'url'=>get_term_link($ancestor)];
            }
            $items[]=['name'=>$term->name,'url'=>get_term_link($term)];
        }
        $items[]=['name'=>get_the_title(),'url'=>get_permalink()];
        return $items;
    }

    if(is_tax('bp_pack_category')){
        $archive_url=get_post_type_archive_link('bp_pack');
        $items[]=['name'=>'پک‌ها','url'=>$archive_url?:home_url('/')];
        $term=get_queried_object();
        if($term instanceof WP_Term){
            foreach(array_reverse(get_ancestors($term->term_id,$term->taxonomy)) as $ancestor_id){
                $ancestor=get_term($ancestor_id,$term->taxonomy);
                if($ancestor&&!is_wp_error($ancestor))$items[]=['name'=>$ancestor->name,'url'=>get_term_link($ancestor)];
            }
            $items[]=['name'=>$term->name,'url'=>get_term_link($term)];
        }
        return $items;
    }

    if(is_post_type_archive('bp_pack')){
        $items[]=['name'=>'پک‌ها','url'=>get_post_type_archive_link('bp_pack')];
        return $items;
    }

    if(is_singular('post')){
        $page_id=(int)get_option('page_for_posts');
        if($page_id)$items[]=['name'=>get_the_title($page_id),'url'=>get_permalink($page_id)];
        $categories=get_the_category();
        if($categories){
            $category=$categories[0];
            foreach(array_reverse(get_ancestors($category->term_id,'category')) as $ancestor_id){
                $ancestor=get_term($ancestor_id,'category');
                if($ancestor&&!is_wp_error($ancestor))$items[]=['name'=>$ancestor->name,'url'=>get_term_link($ancestor)];
            }
            $items[]=['name'=>$category->name,'url'=>get_category_link($category->term_id)];
        }
        $items[]=['name'=>get_the_title(),'url'=>get_permalink()];
        return $items;
    }

    if(is_page()){
        foreach(array_reverse(get_post_ancestors(get_queried_object_id())) as $ancestor_id){
            $items[]=['name'=>get_the_title($ancestor_id),'url'=>get_permalink($ancestor_id)];
        }
        $items[]=['name'=>get_the_title(),'url'=>get_permalink()];
        return $items;
    }

    if(is_home()){
        $page_id=(int)get_option('page_for_posts');
        $items[]=['name'=>$page_id?get_the_title($page_id):'بلاگ','url'=>$page_id?get_permalink($page_id):home_url('/')];
        return $items;
    }

    if(is_category()||is_tag()||is_tax()){
        $term=get_queried_object();
        if($term instanceof WP_Term){
            $items[]=['name'=>$term->name,'url'=>get_term_link($term)];
        }
    }

    return $items;
}

function bluprint_seo_logo_url(){
    $logo_id=(int)get_theme_mod('custom_logo');
    if($logo_id){
        $url=wp_get_attachment_image_url($logo_id,'full');
        if($url)return $url;
    }

    return get_site_icon_url(512)?:'';
}

function bluprint_seo_output_head(){
    if(bluprint_seo_plugin_active()||!bluprint_seo_is_indexable())return;

    $description=bluprint_seo_description();
    $title=wp_get_document_title();
    $url=bluprint_seo_url();
    $image=bluprint_seo_image();
    $site_name=get_bloginfo('name');

    echo '<meta name="description" content="'.esc_attr($description).'">'."\n";

    echo '<meta property="og:locale" content="fa_IR">'."\n";
    echo '<meta property="og:type" content="'.(is_singular('post')?'article':(function_exists('is_product')&&is_product()?'product':'website')).'">'."\n";
    echo '<meta property="og:title" content="'.esc_attr($title).'">'."\n";
    echo '<meta property="og:description" content="'.esc_attr($description).'">'."\n";
    echo '<meta property="og:url" content="'.esc_url($url).'">'."\n";
    echo '<meta property="og:site_name" content="'.esc_attr($site_name).'">'."\n";

    if($image)echo '<meta property="og:image" content="'.esc_url($image).'">'."\n";

    if(is_singular('post')){
        echo '<meta property="article:published_time" content="'.esc_attr(get_the_date(DATE_W3C)).'">'."\n";
        echo '<meta property="article:modified_time" content="'.esc_attr(get_the_modified_date(DATE_W3C)).'">'."\n";
    }

    echo '<meta name="twitter:card" content="'.($image?'summary_large_image':'summary').'">'."\n";
    echo '<meta name="twitter:title" content="'.esc_attr($title).'">'."\n";
    echo '<meta name="twitter:description" content="'.esc_attr($description).'">'."\n";
    if($image)echo '<meta name="twitter:image" content="'.esc_url($image).'">'."\n";

    $organization_id=home_url('/#organization');
    $website_id=home_url('/#website');
    $graph=[
        [
            '@type'=>'Organization',
            '@id'=>$organization_id,
            'name'=>$site_name,
            'url'=>home_url('/'),
        ],
        [
            '@type'=>'WebSite',
            '@id'=>$website_id,
            'url'=>home_url('/'),
            'name'=>$site_name,
            'publisher'=>['@id'=>$organization_id],
            'potentialAction'=>[
                '@type'=>'SearchAction',
                'target'=>home_url('/?s={search_term_string}'),
                'query-input'=>'required name=search_term_string',
            ],
        ],
        [
            '@type'=>(is_home()?'CollectionPage':'WebPage'),
            '@id'=>$url.'#webpage',
            'url'=>$url,
            'name'=>$title,
            'description'=>$description,
            'isPartOf'=>['@id'=>$website_id],
            'inLanguage'=>'fa-IR',
        ],
    ];

    $logo=bluprint_seo_logo_url();
    if($logo)$graph[0]['logo']=['@type'=>'ImageObject','url'=>$logo];

    if(is_singular('post')){
        $article=[
            '@type'=>'Article',
            '@id'=>$url.'#article',
            'headline'=>get_the_title(),
            'datePublished'=>get_the_date(DATE_W3C),
            'dateModified'=>get_the_modified_date(DATE_W3C),
            'author'=>['@type'=>'Person','name'=>get_the_author_meta('display_name')],
            'publisher'=>['@id'=>$organization_id],
            'mainEntityOfPage'=>['@id'=>$url.'#webpage'],
        ];
        if($image)$article['image']=$image;
        $graph[]=$article;
    }

    $breadcrumbs=bluprint_seo_breadcrumb_items();

    if(count($breadcrumbs)>1&&!(function_exists('is_product')&&is_product())){
        $list=[];
        foreach($breadcrumbs as $index=>$item){
            if(empty($item['url']))continue;
            $list[]=[
                '@type'=>'ListItem',
                'position'=>$index+1,
                'name'=>$item['name'],
                'item'=>$item['url'],
            ];
        }
        if($list){
            $graph[]=[
                '@type'=>'BreadcrumbList',
                '@id'=>$url.'#breadcrumb',
                'itemListElement'=>$list,
            ];
        }
    }

    echo '<script type="application/ld+json">'.wp_json_encode(
        ['@context'=>'https://schema.org','@graph'=>$graph],
        JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE
    ).'</script>'."\n";
}
add_action('wp_head','bluprint_seo_output_head',2);

function bluprint_seo_title_parts($title){
    if(bluprint_seo_plugin_active())return $title;
    if(is_front_page())$title['title']='ابزار طراحی، معماری و هنر';
    return $title;
}
add_filter('document_title_parts','bluprint_seo_title_parts');

function bluprint_seo_attachment_alt($attr,$attachment,$size){
    if(!empty($attr['alt'])||!$attachment instanceof WP_Post)return $attr;

    $alt=get_post_meta($attachment->ID,'_wp_attachment_image_alt',true);
    if(!$alt&&$attachment->post_parent)$alt=get_the_title($attachment->post_parent);
    if($alt)$attr['alt']=$alt;

    return $attr;
}
add_filter('wp_get_attachment_image_attributes','bluprint_seo_attachment_alt',10,3);

function bluprint_seo_sitemaps_enabled(){
    return true;
}
add_filter('wp_sitemaps_enabled','bluprint_seo_sitemaps_enabled',99);

function bluprint_seo_robots_txt($output,$public){
    if(strpos($output,'Sitemap:')===false){
        $output=rtrim($output)."\n\nSitemap: ".home_url('/wp-sitemap.xml')."\n";
    }
    return $output;
}
add_filter('robots_txt','bluprint_seo_robots_txt',10,2);
