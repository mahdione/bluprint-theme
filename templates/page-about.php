<?php
/**
 * Template Name: درباره ما
 * Template Post Type: page
 *
 * The structured content (hero, stats, values, CTA) is edited in the page
 * editor under "محتوای صفحه درباره ما". The story body is the normal page
 * content and the story image is the featured image.
 */
defined('ABSPATH')||exit;

get_header();

while(have_posts()):
    the_post();

    $page_id=get_the_ID();
    $data=bluprint_about_get_data($page_id);
    $title=trim($data['title'])!==''?$data['title']:get_the_title();
    $intro=trim($data['intro'])!==''?$data['intro']:wp_trim_words(get_the_excerpt(),40,'…');
    $shop_url=function_exists('wc_get_page_permalink')?wc_get_page_permalink('shop'):home_url('/shop/');
    $cta_url=trim($data['cta_button_url'])!==''?$data['cta_button_url']:$shop_url;
    $story_caption=trim($data['story_caption'])!==''?$data['story_caption']:'BLUEPRINT / SHEET A—01';

    $image_id=get_post_thumbnail_id($page_id);
    $image='';
    if($image_id){
        $alt=get_post_meta($image_id,'_wp_attachment_image_alt',true);
        $image=wp_get_attachment_image($image_id,'large',false,[
            'class'=>'bp-about-story__image',
            'loading'=>'lazy',
            'decoding'=>'async',
            'alt'=>$alt?:$title,
        ]);
    }

    $story=apply_filters('the_content',get_the_content());
    if(trim(wp_strip_all_tags($story))===''){
        $story=wpautop('<p>بلوپرینت از یک میز کار کوچک شروع شد؛ از این ایده که برای ساختن، همیشه لازم نیست از صفر شروع کنی. کافی است کاغذ مناسب را برداری، خط اول را بکشی و اجازه بدهی ایده مسیرش را پیدا کند.</p><p>امروز بلوپرینت مجموعه‌ای از ابزارها، پک‌های آماده و راهنماهاست؛ انتخاب‌هایی که به تو کمک می‌کنند سریع‌تر به اصل ماجرا برسی: ساختن.</p>');
    }

    $stats=[];
    for($i=1;$i<=3;$i++){
        $number=trim($data['stat_'.$i.'_number']);
        $label=trim($data['stat_'.$i.'_label']);
        if($number!==''||$label!=='')$stats[]=['number'=>$number,'label'=>$label];
    }

    $values=[];
    for($i=1;$i<=3;$i++){
        $value_title=trim($data['value_'.$i.'_title']);
        $value_text=trim($data['value_'.$i.'_text']);
        if($value_title!==''||$value_text!=='')$values[]=['index'=>sprintf('%02d',$i),'title'=>$value_title,'text'=>$value_text];
    }

    $has_story_title=trim($data['story_title'])!=='';
    $has_values_title=trim($data['values_title'])!=='';
    $has_cta_title=trim($data['cta_title'])!=='';
    $has_cta_action=trim($data['cta_button_text'])!==''&&trim($cta_url)!=='';
    $show_values=!empty($values);
    $show_cta=$has_cta_title||trim($data['cta_text'])!==''||$has_cta_action;
?>
<main id="main-content" class="bp-about">
    <section class="bp-about-hero" aria-labelledby="bp-about-title">
        <div class="bp-about-hero__grid" aria-hidden="true"></div>
        <div class="bp-about__container">
            <?php get_template_part('template-parts/components/breadcrumbs/breadcrumbs',null,[
                'variant'=>'hero',
                'label'=>'مسیر صفحه',
                'class'=>'bp-about-breadcrumb',
            ]); ?>

            <div class="bp-about-hero__layout">
                <div class="bp-about-hero__intro-wrap">
                    <?php if(trim($data['eyebrow'])!==''): ?>
                        <p class="bp-about-hero__eyebrow"><?php echo esc_html($data['eyebrow']); ?></p>
                    <?php endif; ?>
                    <h1 class="bp-about-hero__title" id="bp-about-title"><?php echo esc_html($title); ?></h1>
                    <?php if(trim($intro)!==''): ?>
                        <p class="bp-about-hero__intro"><?php echo nl2br(esc_html($intro)); ?></p>
                    <?php endif; ?>
                </div>

                <div class="bp-about-hero__sheet" aria-hidden="true">
                    <span>SHEET A—01</span>
                    <span>REV. <?php echo esc_html(date_i18n('Y')); ?></span>
                    <span><?php echo esc_html(get_bloginfo('name')); ?></span>
                </div>
            </div>

            <?php if(!empty($stats)): ?>
                <div class="bp-about-stats">
                    <?php foreach($stats as $stat): ?>
                        <div class="bp-about-stat">
                            <?php if($stat['number']!==''): ?>
                                <span class="bp-about-stat__number"><?php echo esc_html($stat['number']); ?></span>
                            <?php endif; ?>
                            <?php if($stat['label']!==''): ?>
                                <span class="bp-about-stat__label"><?php echo esc_html($stat['label']); ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="bp-about-story" <?php echo $has_story_title?'aria-labelledby="bp-about-story-title"':''; ?>>
        <div class="bp-about__container">
            <div class="bp-about-story__grid">
                <div class="bp-about-story__content">
                    <?php if(trim($data['story_eyebrow'])!==''): ?>
                        <p class="bp-about-section-eyebrow"><?php echo esc_html($data['story_eyebrow']); ?></p>
                    <?php endif; ?>
                    <?php if($has_story_title): ?>
                        <h2 class="bp-about-section-title" id="bp-about-story-title"><?php echo esc_html($data['story_title']); ?></h2>
                    <?php endif; ?>
                    <div class="bp-about-story__body"><?php echo $story; ?></div>
                </div>

                <figure class="bp-about-story__media">
                    <?php if($image): ?>
                        <?php echo $image; ?>
                    <?php else: ?>
                        <div class="bp-about-story__placeholder" aria-hidden="true">
                            <span class="bp-about-story__placeholder-code">A—01</span>
                            <span>BLUEPRINT SHEET</span>
                        </div>
                    <?php endif; ?>
                    <figcaption class="bp-about-story__caption"><?php echo esc_html($story_caption); ?></figcaption>
                </figure>
            </div>
        </div>
    </section>

    <?php if($show_values): ?>
        <section class="bp-about-values" <?php echo $has_values_title?'aria-labelledby="bp-about-values-title"':''; ?>>
            <div class="bp-about__container">
                <div class="bp-about-values__head">
                    <div class="bp-about-values__intro">
                        <?php if(trim($data['values_eyebrow'])!==''): ?>
                            <p class="bp-about-section-eyebrow"><?php echo esc_html($data['values_eyebrow']); ?></p>
                        <?php endif; ?>
                        <?php if($has_values_title): ?>
                            <h2 class="bp-about-section-title" id="bp-about-values-title"><?php echo esc_html($data['values_title']); ?></h2>
                        <?php endif; ?>
                    </div>
                    <span class="bp-about-values__rule" aria-hidden="true"></span>
                </div>

                <div class="bp-about-values__grid">
                    <?php foreach($values as $value): ?>
                        <article class="bp-about-value">
                            <span class="bp-about-value__index" aria-hidden="true"><?php echo esc_html($value['index']); ?></span>
                            <?php if($value['title']!==''): ?>
                                <h3 class="bp-about-value__title"><?php echo esc_html($value['title']); ?></h3>
                            <?php endif; ?>
                            <?php if($value['text']!==''): ?>
                                <p class="bp-about-value__text"><?php echo esc_html($value['text']); ?></p>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if($show_cta): ?>
        <section class="bp-about-cta" <?php echo $has_cta_title?'aria-labelledby="bp-about-cta-title"':''; ?>>
            <div class="bp-about__container">
                <div class="bp-about-cta__panel">
                    <div class="bp-about-cta__content">
                        <?php if($has_cta_title): ?>
                            <h2 class="bp-about-cta__title" id="bp-about-cta-title"><?php echo esc_html($data['cta_title']); ?></h2>
                        <?php endif; ?>
                        <?php if(trim($data['cta_text'])!==''): ?>
                            <p class="bp-about-cta__text"><?php echo nl2br(esc_html($data['cta_text'])); ?></p>
                        <?php endif; ?>
                    </div>

                    <?php if($has_cta_action): ?>
                        <div class="bp-about-cta__actions">
                            <?php
                            get_template_part('template-parts/components/buttons/button',null,[
                                'label'=>$data['cta_button_text'],
                                'url'=>$cta_url,
                                'variant'=>'primary',
                                'icon'=>'arrow-left',
                                'show_icon'=>true,
                            ]);
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>
</main>
<?php endwhile; ?>
<?php get_footer(); ?>