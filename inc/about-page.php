<?php
defined('ABSPATH')||exit;

/**
 * Default content for the About page template.
 *
 * Everything here is editable from the page editor. The long-form story lives
 * in the normal editor, the hero image is the featured image, and the
 * structured blocks below are stored as page meta.
 */
function bluprint_about_defaults(){
    $shop=function_exists('wc_get_page_permalink')?wc_get_page_permalink('shop'):home_url('/shop/');

    return [
        'eyebrow'=>'A—01 / ABOUT BLUEPRINT',
        'title'=>'ما نقشه‌ای برای ایده‌های تو می‌کشیم.',
        'intro'=>'بلوپرینت جایی است برای شروع؛ جایی که ابزار درست، کاغذ درست و یک ایده نیمه‌تمام کنار هم می‌نشینند تا چیزی ساخته شود. ما محصولات را نه به‌عنوان ویترین، بلکه به‌عنوان ابزار فکر کردن انتخاب می‌کنیم.',
        'story_eyebrow'=>'۰۱ / داستان ما',
        'story_title'=>'از یک صفحه سفید شروع شد.',
        'story_caption'=>'BLUEPRINT / SHEET A—01',
        'stat_1_number'=>'۹',
        'stat_1_label'=>'سال تجربه',
        'stat_2_number'=>'+۱۲۰',
        'stat_2_label'=>'پروژه همراه',
        'stat_3_number'=>'۴۸',
        'stat_3_label'=>'پک آماده',
        'values_eyebrow'=>'۰۲ / اصول کار',
        'values_title'=>'چیزهایی که تغییر نمی‌کنند.',
        'value_1_title'=>'ابزار، نه ویترین',
        'value_1_text'=>'هر محصول باید کاری را ساده‌تر کند؛ اگر فقط تزئین باشد، جایش اینجا نیست.',
        'value_2_title'=>'ساده و روشن',
        'value_2_text'=>'بدون شلوغی و بدون قدم‌های اضافه. انتخاب باید سریع و مطمئن باشد.',
        'value_3_title'=>'همراه تا خط آخر',
        'value_3_text'=>'از انتخاب اولین ابزار تا تحویل سفارش، کنارت می‌مانیم.',
        'cta_title'=>'بیا اولین خط را با هم بکشیم.',
        'cta_text'=>'اگر نمی‌دانی از کجا شروع کنی، از پک‌های آماده یا فروشگاه ما شروع کن. بقیه‌اش را با هم می‌سازیم.',
        'cta_button_text'=>'مشاهده محصولات',
        'cta_button_url'=>$shop,
    ];
}

/**
 * Sanitize the stored About page meta.
 */
function bluprint_about_sanitize($input){
    $input=is_array($input)?$input:[];
    $output=[];
    $text_fields=[
        'eyebrow','title','story_eyebrow','story_title','story_caption',
        'stat_1_number','stat_1_label','stat_2_number','stat_2_label','stat_3_number','stat_3_label',
        'values_eyebrow','values_title',
        'value_1_title','value_2_title','value_3_title',
        'cta_title','cta_button_text',
    ];

    foreach($text_fields as $key){
        $output[$key]=sanitize_text_field($input[$key]??'');
    }

    $output['intro']=sanitize_textarea_field($input['intro']??'');
    $output['value_1_text']=sanitize_textarea_field($input['value_1_text']??'');
    $output['value_2_text']=sanitize_textarea_field($input['value_2_text']??'');
    $output['value_3_text']=sanitize_textarea_field($input['value_3_text']??'');
    $output['cta_text']=sanitize_textarea_field($input['cta_text']??'');
    $output['cta_button_url']=esc_url_raw(trim($input['cta_button_url']??''));

    return $output;
}

/**
 * Read the About page data for a specific page.
 */
function bluprint_about_get_data($post_id=0){
    $post_id=$post_id?absint($post_id):absint(get_queried_object_id());
    $stored=$post_id?get_post_meta($post_id,'_bluprint_about_page',true):[];
    return wp_parse_args(is_array($stored)?$stored:[],bluprint_about_defaults());
}

/**
 * Register the About page content box on pages.
 */
function bluprint_about_register_meta_box(){
    add_meta_box(
        'bluprint-about-page',
        'محتوای صفحه درباره ما',
        'bluprint_about_render_meta_box',
        'page',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes','bluprint_about_register_meta_box');

function bluprint_about_render_meta_box($post){
    $data=bluprint_about_get_data($post->ID);
    ?>
    <div class="bp-about-admin">
        <?php wp_nonce_field('bluprint_about_save','bluprint_about_nonce'); ?>
        <p class="bp-about-admin__notice">
            این بخش فقط در قالب «درباره ما» نمایش داده می‌شود. متن اصلی داستان را در ویرایشگر همین صفحه بنویسید و تصویر صفحه را از «تصویر شاخص» انتخاب کنید.
        </p>

        <section class="bp-about-admin__section">
            <h3>بخش معرفی</h3>
            <div class="bp-about-admin__grid">
                <div class="bp-about-admin__field">
                    <label for="bp-about-eyebrow">عنوان بالایی</label>
                    <input type="text" id="bp-about-eyebrow" name="bluprint_about[eyebrow]" value="<?php echo esc_attr($data['eyebrow']); ?>" class="large-text">
                </div>
                <div class="bp-about-admin__field">
                    <label for="bp-about-title">عنوان اصلی (H1)</label>
                    <input type="text" id="bp-about-title" name="bluprint_about[title]" value="<?php echo esc_attr($data['title']); ?>" class="large-text">
                    <span class="description">اگر خالی بماند، عنوان صفحه استفاده می‌شود.</span>
                </div>
            </div>
            <div class="bp-about-admin__field">
                <label for="bp-about-intro">متن معرفی</label>
                <textarea id="bp-about-intro" name="bluprint_about[intro]" rows="4" class="large-text"><?php echo esc_textarea($data['intro']); ?></textarea>
            </div>
        </section>

        <section class="bp-about-admin__section">
            <h3>بخش داستان</h3>
            <div class="bp-about-admin__grid">
                <div class="bp-about-admin__field">
                    <label for="bp-about-story-eyebrow">عنوان بالایی داستان</label>
                    <input type="text" id="bp-about-story-eyebrow" name="bluprint_about[story_eyebrow]" value="<?php echo esc_attr($data['story_eyebrow']); ?>" class="large-text">
                </div>
                <div class="bp-about-admin__field">
                    <label for="bp-about-story-title">عنوان داستان</label>
                    <input type="text" id="bp-about-story-title" name="bluprint_about[story_title]" value="<?php echo esc_attr($data['story_title']); ?>" class="large-text">
                </div>
            </div>
            <div class="bp-about-admin__field">
                <label for="bp-about-story-caption">برچسب زیر تصویر</label>
                <input type="text" id="bp-about-story-caption" name="bluprint_about[story_caption]" value="<?php echo esc_attr($data['story_caption']); ?>" class="large-text">
            </div>
        </section>

        <section class="bp-about-admin__section">
            <h3>آمار</h3>
            <div class="bp-about-admin__grid bp-about-admin__grid--3">
                <?php for($i=1;$i<=3;$i++): ?>
                    <div class="bp-about-admin__group">
                        <div class="bp-about-admin__field">
                            <label for="bp-about-stat-<?php echo esc_attr($i); ?>-number">عدد <?php echo esc_html($i); ?></label>
                            <input type="text" id="bp-about-stat-<?php echo esc_attr($i); ?>-number" name="bluprint_about[stat_<?php echo esc_attr($i); ?>_number]" value="<?php echo esc_attr($data['stat_'.$i.'_number']); ?>" class="large-text">
                        </div>
                        <div class="bp-about-admin__field">
                            <label for="bp-about-stat-<?php echo esc_attr($i); ?>-label">برچسب <?php echo esc_html($i); ?></label>
                            <input type="text" id="bp-about-stat-<?php echo esc_attr($i); ?>-label" name="bluprint_about[stat_<?php echo esc_attr($i); ?>_label]" value="<?php echo esc_attr($data['stat_'.$i.'_label']); ?>" class="large-text">
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </section>

        <section class="bp-about-admin__section">
            <h3>اصول کار</h3>
            <div class="bp-about-admin__grid">
                <div class="bp-about-admin__field">
                    <label for="bp-about-values-eyebrow">عنوان بالایی اصول</label>
                    <input type="text" id="bp-about-values-eyebrow" name="bluprint_about[values_eyebrow]" value="<?php echo esc_attr($data['values_eyebrow']); ?>" class="large-text">
                </div>
                <div class="bp-about-admin__field">
                    <label for="bp-about-values-title">عنوان اصول</label>
                    <input type="text" id="bp-about-values-title" name="bluprint_about[values_title]" value="<?php echo esc_attr($data['values_title']); ?>" class="large-text">
                </div>
            </div>
            <div class="bp-about-admin__grid bp-about-admin__grid--3">
                <?php for($i=1;$i<=3;$i++): ?>
                    <div class="bp-about-admin__group">
                        <div class="bp-about-admin__field">
                            <label for="bp-about-value-<?php echo esc_attr($i); ?>-title">عنوان <?php echo esc_html($i); ?></label>
                            <input type="text" id="bp-about-value-<?php echo esc_attr($i); ?>-title" name="bluprint_about[value_<?php echo esc_attr($i); ?>_title]" value="<?php echo esc_attr($data['value_'.$i.'_title']); ?>" class="large-text">
                        </div>
                        <div class="bp-about-admin__field">
                            <label for="bp-about-value-<?php echo esc_attr($i); ?>-text">توضیح <?php echo esc_html($i); ?></label>
                            <textarea id="bp-about-value-<?php echo esc_attr($i); ?>-text" name="bluprint_about[value_<?php echo esc_attr($i); ?>_text]" rows="4" class="large-text"><?php echo esc_textarea($data['value_'.$i.'_text']); ?></textarea>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </section>

        <section class="bp-about-admin__section">
            <h3>دعوت به اقدام</h3>
            <div class="bp-about-admin__field">
                <label for="bp-about-cta-title">عنوان</label>
                <input type="text" id="bp-about-cta-title" name="bluprint_about[cta_title]" value="<?php echo esc_attr($data['cta_title']); ?>" class="large-text">
            </div>
            <div class="bp-about-admin__field">
                <label for="bp-about-cta-text">توضیحات</label>
                <textarea id="bp-about-cta-text" name="bluprint_about[cta_text]" rows="3" class="large-text"><?php echo esc_textarea($data['cta_text']); ?></textarea>
            </div>
            <div class="bp-about-admin__grid">
                <div class="bp-about-admin__field">
                    <label for="bp-about-cta-button-text">متن دکمه</label>
                    <input type="text" id="bp-about-cta-button-text" name="bluprint_about[cta_button_text]" value="<?php echo esc_attr($data['cta_button_text']); ?>" class="large-text">
                </div>
                <div class="bp-about-admin__field">
                    <label for="bp-about-cta-button-url">لینک دکمه</label>
                    <input type="url" id="bp-about-cta-button-url" name="bluprint_about[cta_button_url]" value="<?php echo esc_attr($data['cta_button_url']); ?>" class="large-text" placeholder="<?php echo esc_attr(home_url('/shop/')); ?>">
                    <span class="description">اگر خالی بماند، به فروشگاه لینک می‌شود.</span>
                </div>
            </div>
        </section>
    </div>
    <?php
}

/**
 * Persist the About page meta.
 */
function bluprint_about_save_meta($post_id){
    if(!isset($_POST['bluprint_about_nonce'])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['bluprint_about_nonce'])),'bluprint_about_save'))return;
    if(defined('DOING_AUTOSAVE')&&DOING_AUTOSAVE)return;
    if(get_post_type($post_id)!=='page')return;
    if(!current_user_can('edit_post',$post_id))return;

    $raw=isset($_POST['bluprint_about'])?wp_unslash($_POST['bluprint_about']):[];
    update_post_meta($post_id,'_bluprint_about_page',bluprint_about_sanitize($raw));
}
add_action('save_post_page','bluprint_about_save_meta');

/**
 * Style the About page content box in the admin.
 */
function bluprint_about_admin_assets($hook){
    if(!in_array($hook,['post.php','post-new.php'],true))return;
    $screen=get_current_screen();
    if(!$screen||$screen->post_type!=='page')return;

    wp_enqueue_style(
        'bluprint-about-admin',
        get_theme_file_uri('assets/css/admin/about-page.css'),
        [],
        bluprint_asset_version('assets/css/admin/about-page.css')
    );
}
add_action('admin_enqueue_scripts','bluprint_about_admin_assets');

/**
 * Provide a sensible meta description when Rank Math has none.
 */
function bluprint_about_meta_description($description){
    if(!is_page_template('templates/page-about.php'))return $description;
    if(trim((string)$description)!=='')return $description;

    $data=bluprint_about_get_data();
    $text=$data['intro']?:get_the_excerpt();
    $text=wp_strip_all_tags($text);

    return $text?wp_trim_words($text,32,'…'):$description;
}
add_filter('rank_math/frontend/description','bluprint_about_meta_description');