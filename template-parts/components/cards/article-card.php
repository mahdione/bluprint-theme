<?php
defined('ABSPATH')||exit;

$args=wp_parse_args($args??[],[
    'post_id'=>0,
    'heading_level'=>3,
]);

$post_id=absint($args['post_id']);
if(!$post_id)return;

$heading_level=in_array((int)$args['heading_level'],[2,3],true)?(int)$args['heading_level']:3;
$title=get_the_title($post_id);
$url=get_permalink($post_id);
$excerpt=get_the_excerpt($post_id);

if(!$excerpt){
    $excerpt=wp_strip_all_tags(strip_shortcodes(get_post_field('post_content',$post_id)));
}

$excerpt=wp_trim_words(wp_strip_all_tags($excerpt),30,'...');
$categories=get_the_category($post_id);
$category=null;
$depth=-1;

foreach($categories as $term){
    $term_depth=count(get_ancestors($term->term_id,'category'));
    if($term_depth>$depth){
        $depth=$term_depth;
        $category=$term;
    }
}

$image_id=get_post_thumbnail_id($post_id);
$image_alt=$image_id?get_post_meta($image_id,'_wp_attachment_image_alt',true):'';
$image_alt=$image_alt?:$title;
?>
<article class="bp-article-card">
    <a class="bp-article-card__link" href="<?php echo esc_url($url); ?>" aria-label="<?php echo esc_attr($title); ?>"></a>

    <div class="bp-article-card__media">
        <?php if($image_id): ?>
            <?php echo wp_get_attachment_image($image_id,'large',false,[
                'class'=>'bp-article-card__image',
                'alt'=>$image_alt,
                'loading'=>'lazy',
                'decoding'=>'async',
            ]); ?>
        <?php else: ?>
            <div class="bp-article-card__placeholder" aria-hidden="true"></div>
        <?php endif; ?>
    </div>

    <div class="bp-article-card__body">
        <?php if($category): ?>
            <a class="bp-article-card__category" href="<?php echo esc_url(get_category_link($category->term_id)); ?>">
                <?php echo esc_html($category->name); ?>
            </a>
        <?php endif; ?>

        <h<?php echo esc_attr($heading_level); ?> class="bp-article-card__title"><?php echo esc_html($title); ?></h<?php echo esc_attr($heading_level); ?>>

        <?php if($excerpt): ?>
            <p class="bp-article-card__excerpt"><?php echo esc_html($excerpt); ?></p>
        <?php endif; ?>

        <a class="bp-button bp-button--primary bp-article-card__button" href="<?php echo esc_url($url); ?>">
            <img class="bp-button__icon" src="<?php echo esc_url(get_theme_file_uri('assets/icons/arrow-left.svg')); ?>" alt="" width="16" height="16" decoding="async">
            <span class="bp-button__label">خواندن مطلب</span>
        </a>
    </div>
</article>