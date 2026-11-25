<?php
defined('ABSPATH')||exit;

/**
 * Rich term content for pack categories and WooCommerce product taxonomies.
 *
 * The native term description stays as the short summary shown in archive
 * heroes. This field stores optional long-form content displayed below the
 * archive grid inside a collapsible "read more" panel.
 */
function bluprint_term_content_taxonomies(){
    return ['bp_pack_category','product_cat','product_tag'];
}

function bluprint_term_content_is_enabled($taxonomy){
    return in_array($taxonomy,bluprint_term_content_taxonomies(),true);
}

function bluprint_get_term_content($term_id){
    $term_id=absint($term_id);
    if(!$term_id)return '';

    $content=get_term_meta($term_id,'_bluprint_term_content',true);

    return is_string($content)?$content:'';
}

function bluprint_term_content_editor_settings(){
    return [
        'textarea_name'=>'bluprint_term_content',
        'textarea_rows'=>8,
        'media_buttons'=>false,
        'teeny'=>true,
        'quicktags'=>true,
        'tinymce'=>[
            'toolbar1'=>'formatselect,bold,italic,bullist,numlist,blockquote,link,unlink,undo,redo',
            'toolbar2'=>'',
        ],
    ];
}

function bluprint_term_content_add_field($taxonomy){
    if(!bluprint_term_content_is_enabled($taxonomy))return;

    wp_nonce_field('bluprint_term_content_save','bluprint_term_content_nonce');
    ?>
    <div class="form-field bluprint-term-content">
        <label for="bluprint_term_content">محتوای تکمیلی</label>
        <?php wp_editor('', 'bluprint_term_content', bluprint_term_content_editor_settings()); ?>
        <p>این محتوا در پایین صفحه آرشیو، داخل بخش «ادامه مطلب» نمایش داده می‌شود.</p>
    </div>
    <?php
}

function bluprint_term_content_edit_field($term,$taxonomy){
    if(!bluprint_term_content_is_enabled($taxonomy))return;

    $content=bluprint_get_term_content($term->term_id);
    ?>
    <tr class="form-field bluprint-term-content">
        <th scope="row">
            <label for="bluprint_term_content">محتوای تکمیلی</label>
        </th>
        <td>
            <?php wp_nonce_field('bluprint_term_content_save','bluprint_term_content_nonce'); ?>
            <?php wp_editor($content, 'bluprint_term_content', bluprint_term_content_editor_settings()); ?>
            <p class="description">این محتوا در پایین صفحه آرشیو، داخل بخش «ادامه مطلب» نمایش داده می‌شود.</p>
        </td>
    </tr>
    <?php
}

function bluprint_term_content_save($term_id){
    if(!isset($_POST['bluprint_term_content_nonce']))return;
    if(!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['bluprint_term_content_nonce'])),'bluprint_term_content_save'))return;

    $term=get_term(absint($term_id));
    if(!$term||is_wp_error($term)||!bluprint_term_content_is_enabled($term->taxonomy))return;

    $taxonomy=get_taxonomy($term->taxonomy);
    if(!$taxonomy||!current_user_can($taxonomy->cap->edit_terms))return;

    $content=isset($_POST['bluprint_term_content'])
        ?wp_kses_post(wp_unslash($_POST['bluprint_term_content']))
        :'';

    update_term_meta($term->term_id,'_bluprint_term_content',$content);
}

function bluprint_term_content_register_hooks(){
    foreach(bluprint_term_content_taxonomies() as $taxonomy){
        add_action($taxonomy.'_add_form_fields','bluprint_term_content_add_field');
        add_action($taxonomy.'_edit_form_fields','bluprint_term_content_edit_field',10,2);
        add_action('created_'.$taxonomy,'bluprint_term_content_save');
        add_action('edited_'.$taxonomy,'bluprint_term_content_save');
    }
}
add_action('init','bluprint_term_content_register_hooks');

function bluprint_term_content_admin_assets($hook){
    if(!in_array($hook,['edit-tags.php','term.php'],true))return;

    $screen=get_current_screen();
    $taxonomy=$screen->taxonomy??'';

    if(!bluprint_term_content_is_enabled($taxonomy))return;

    if(function_exists('wp_enqueue_editor'))wp_enqueue_editor();
}
add_action('admin_enqueue_scripts','bluprint_term_content_admin_assets');