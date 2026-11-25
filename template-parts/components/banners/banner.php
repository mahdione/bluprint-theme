<?php
defined('ABSPATH')||exit;

/*
 * The banner renderer is used by the front page, single posts and pack pages.
 * If the theme's include list is stale or a deployment missed inc/banners.php,
 * load the banner API directly so a missing function can never fatal the site.
 */
if(!function_exists('bluprint_get_banner_data')){
    $banners_file=get_theme_file_path('inc/banners.php');
    if(file_exists($banners_file))require_once $banners_file;
}

if(!function_exists('bluprint_get_banner_data'))return;

$args=isset($args)&&is_array($args)?$args:[];
$banner_id=absint($args['banner_id']??1);
if(!$banner_id)return;

$banner=bluprint_get_banner_data($banner_id);
if(empty($banner['enabled']))return;

$title=trim((string)($banner['title']??''));
$eyebrow=trim((string)($banner['eyebrow']??''));
$description=trim((string)($banner['description']??''));
$button_text=trim((string)($banner['button_text']??''));
$button_url=trim((string)($banner['button_url']??''));

if($title===''&&$eyebrow===''&&$description===''&&$button_text==='')return;

$image_id=absint($banner['image_id']??0);
$image_url=$image_id?wp_get_attachment_image_url($image_id,'full'):'';
if(!$image_url)$image_url=get_theme_file_uri('assets/images/banners/banner-1-artwork.png');
$target=!empty($banner['button_target'])?'_blank':'';
$style=sprintf(
    '--banner-bg:%1$s;--banner-shadow:%2$s;--banner-image-opacity:%3$s;',
    esc_attr($banner['background_color']),
    esc_attr($banner['shadow_color']),
    esc_attr((string)(min(100,max(0,(int)$banner['image_opacity']))/100))
);
$heading_id='bp-banner-'.$banner_id.'-title';
?>
<section class="bp-banner" style="<?php echo esc_attr($style); ?>"<?php if($title): ?> aria-labelledby="<?php echo esc_attr($heading_id); ?>"<?php else: ?> aria-label="<?php echo esc_attr($eyebrow?:'بنر تبلیغاتی'); ?>"<?php endif; ?>>
    <div class="bp-banner__content">
        <?php if($eyebrow): ?>
            <p class="bp-banner__eyebrow"><?php echo esc_html($eyebrow); ?></p>
        <?php endif; ?>
        <?php if($title): ?>
            <h2 class="bp-banner__title" id="<?php echo esc_attr($heading_id); ?>"><?php echo esc_html($title); ?></h2>
        <?php endif; ?>
        <?php if($description): ?>
            <p class="bp-banner__description"><?php echo esc_html($description); ?></p>
        <?php endif; ?>
    </div>

    <?php if($button_text): ?>
        <div class="bp-banner__action">
            <?php get_template_part('template-parts/components/buttons/button',null,[
                'label'=>$button_text,
                'url'=>$button_url,
                'variant'=>'primary',
                'icon'=>'arrow-left',
                'show_icon'=>true,
                'target'=>$target,
                'class'=>'bp-banner__button',
            ]); ?>
        </div>
    <?php endif; ?>

    <?php if($image_url): ?>
        <div class="bp-banner__artwork" aria-hidden="true">
            <img class="bp-banner__image" src="<?php echo esc_url($image_url); ?>" alt="" width="560" height="206" loading="lazy" decoding="async">
        </div>
    <?php endif; ?>
</section>