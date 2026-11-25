<?php
defined('ABSPATH')||exit;

if(!function_exists('bluprint_get_faq_data')){
    $bluprint_inc=get_theme_file_path('inc/faq-section.php');
    if(file_exists($bluprint_inc))require_once $bluprint_inc;
}
if(!function_exists('bluprint_get_faq_data'))return;

$data=bluprint_get_faq_data(); if(empty($data['enabled']))return; $items=bluprint_get_faq_items(); if(!$items)return; $title=trim($data['title']); ?> <section class="bp-faq-section" aria-labelledby="bp-faq-title"> <div class="bp-faq-section__container"> <?php if($title): ?> <h2 class="bp-faq-section__title" id="bp-faq-title"><?php echo esc_html($title); ?></h2> <?php endif; ?> <div class="bp-faq-section__list"> <?php foreach($items as $index=>$item): $question=get_the_title($item); $answer=apply_filters('the_content',$item->post_content); $question_id='bp-faq-question-'.$item->ID; $answer_id='bp-faq-answer-'.$item->ID; ?> <article class="bp-faq-item"> <h3 class="bp-faq-item__heading"> <button class="bp-faq-item__trigger" id="<?php echo esc_attr($question_id); ?>" type="button" aria-expanded="false" aria-controls="<?php echo esc_attr($answer_id); ?>" > <span class="bp-faq-item__question"><?php echo esc_html($question); ?></span> <span class="bp-faq-item__icon" aria-hidden="true">+</span> </button> </h3> <div class="bp-faq-item__panel" id="<?php echo esc_attr($answer_id); ?>" role="region" aria-labelledby="<?php echo esc_attr($question_id); ?>" aria-hidden="true" inert > <div class="bp-faq-item__answer"> <?php echo $answer; ?> </div> </div> </article> <?php endforeach; ?> </div> </div> </section>