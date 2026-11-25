<?php
defined('ABSPATH')||exit;

if(!function_exists('bluprint_get_editorial_data')){
    $bluprint_inc=get_theme_file_path('inc/editorial-section.php');
    if(file_exists($bluprint_inc))require_once $bluprint_inc;
}
if(!function_exists('bluprint_get_editorial_data'))return;

$data=bluprint_get_editorial_data();
if(empty($data['enabled']))return;

$image=!empty($data['image_id'])?wp_get_attachment_image_url($data['image_id'],'full'):'';
$image=$image?:get_theme_file_uri('assets/images/editorial-banner.png');
$alt=$data['image_alt']??'';
$shadow_color=$data['shadow_color']??'#093FB9';
?>
<section class="bp-editorial" style="--editorial-shadow-color:<?php echo esc_attr($shadow_color); ?>" aria-labelledby="bp-editorial-title">
<div class="bp-editorial__container">
<div class="bp-editorial__artwork">
<img class="bp-editorial__image" src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($alt); ?>" width="552" height="420" loading="lazy" decoding="async">
</div>
<div class="bp-editorial__content">
<?php if(!empty($data['eyebrow'])): ?>
<p class="bp-editorial__eyebrow"><?php echo esc_html($data['eyebrow']); ?></p>
<?php endif; ?>
<?php if(!empty($data['title'])): ?>
<h2 class="bp-editorial__title" id="bp-editorial-title"><?php echo nl2br(esc_html($data['title'])); ?></h2>
<?php endif; ?>
<?php if(!empty($data['description'])): ?>
<p class="bp-editorial__description"><?php echo esc_html($data['description']); ?></p>
<?php endif; ?>
<?php if(!empty($data['button_text'])&&!empty($data['button_url'])): ?>
<div class="bp-editorial__actions">
<?php get_template_part('template-parts/components/buttons/button',null,[
'label'=>$data['button_text'],
'url'=>$data['button_url'],
'variant'=>'outline',
'icon'=>'arrow-left',
'show_icon'=>true,
'class'=>'bp-editorial__button',
'target'=>!empty($data['button_target'])?'_blank':''
]); ?>
</div>
<?php endif; ?>
</div>
</div>
</section>