<?php
defined('ABSPATH')||exit;

/**
 * Contact page defaults, per-page settings (meta box), channel helpers,
 * secure contact form handling and SEO fallbacks.
 *
 * The structured content is edited in the page editor under
 * «محتوای صفحه تماس با ما». Messages are delivered by email through a
 * nonce-, honeypot-, timestamp- and rate-limited handler.
 */

function bluprint_contact_defaults(){
    return [
        'eyebrow'=>'',
        'title'=>'بیا روی خط بود.',
        'intro'=>'سؤالی درباره محصولات، سفارش یا پروژه‌ات داری؟ پیامت را بگذار؛ کارشناسان ما با دقت به آن پاسخ می‌دهند. اگر دوست داری سریع‌تر برسی، همین حالا از کانال‌های پشتیبانی استفاده کن.',
        'info_eyebrow'=>'۰۱ / ارتباط مستقیم',
        'info_title'=>'روی خط مستقیم برس.',
        'phone_label'=>'تماس تلفنی',
        'phone_number'=>'۰۲۱-۹۱۰۰-۱۲۳۴',
        'phone_url'=>'',
        'whatsapp_label'=>'چت واتس‌اپ',
        'whatsapp_number'=>'۰۹۱۲-۱۲۳-۴۵۶۷',
        'whatsapp_url'=>'',
        'telegram_label'=>'چت تلگرام',
        'telegram_username'=>'@blueprint',
        'telegram_url'=>'',
        'instagram_username'=>'blueprint',
        'instagram_url'=>'https://instagram.com/blueprint',
        'response_note'=>'معمولاً در کمتر از ۲۴ ساعت کاری پاسخ می‌دهیم.',
        'form_title'=>'فرم تماس',
        'form_intro'=>'پیامت را بنویس؛ ما در اسرع وقت پاسخ می‌دهیم.',
        'form_button_text'=>'ارسال پیام',
        'recipient_email'=>'',
    ];
}

/**
 * Sanitize the stored contact page meta.
 */
function bluprint_contact_sanitize($input){
    $input=is_array($input)?$input:[];
    $output=[];

    $text_fields=[
        'eyebrow','title','info_eyebrow','info_title',
        'phone_label','phone_number',
        'whatsapp_label','whatsapp_number',
        'telegram_label','telegram_username',
        'instagram_username','form_title','form_button_text',
    ];
    foreach($text_fields as $key){
        $output[$key]=sanitize_text_field(trim((string)($input[$key]??'')));
    }

    foreach(['intro','response_note','form_intro'] as $key){
        $output[$key]=sanitize_textarea_field($input[$key]??'');
    }

    foreach(['phone_url','whatsapp_url','telegram_url','instagram_url'] as $key){
        $output[$key]=esc_url_raw(trim((string)($input[$key]??'')));
    }

    $output['recipient_email']=sanitize_email($input['recipient_email']??'');

    return $output;
}

/**
 * Read the contact page data for a specific page.
 */
function bluprint_contact_get_data($post_id=0){
    $post_id=$post_id?absint($post_id):absint(get_queried_object_id());
    $stored=$post_id?get_post_meta($post_id,'_bluprint_contact_page',true):[];
    $data=wp_parse_args(is_array($stored)?$stored:[],bluprint_contact_defaults());

    // Drop the old technical eyebrow from pages saved before it was removed.
    if(trim((string)$data['eyebrow'])==='C—02 / تماس با بلوپرینت')$data['eyebrow']='';

    return $data;
}

/**
 * Inline icons used by the contact page channels and form notices.
 */
function bluprint_contact_icon($name){
    $icons=[
        'phone'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg>',
        'whatsapp'=>'<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c0-5.445 4.436-9.885 9.884-9.885a9.82 9.82 0 0 1 6.97 2.906 9.82 9.82 0 0 1 2.907 6.97c-.003 5.446-4.437 9.884-9.882 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>',
        'telegram'=>'<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>',
        'instagram'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="2.5" y="2.5" width="19" height="19" rx="5"/><circle cx="12" cy="12" r="4.2"/><circle cx="17.6" cy="6.4" r="1.1" fill="currentColor" stroke="none"/></svg>',
        'arrow-left'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>',
        'check'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M20 6 9 17l-5-5"/></svg>',
        'warning'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M12 9v4M12 17h.01"/><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg>',
        'clock'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>',
    ];

    return $icons[$name]??'';
}

/**
 * Convert Persian and Arabic digits to Latin digits and keep digits only.
 */
function bluprint_contact_digits($value){
    $map=[
        '۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9',
        '٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9',
    ];
    $value=strtr((string)$value,$map);

    return preg_replace('/\D+/','',$value);
}

/**
 * Build the tel: link for the phone channel.
 */
function bluprint_contact_phone_url($number,$url){
    if(trim((string)$url)!=='')return $url;
    $digits=bluprint_contact_digits($number);

    return $digits!==''?'tel:'.$digits:'';
}

/**
 * Build the wa.me link for the WhatsApp channel.
 */
function bluprint_contact_whatsapp_url($number,$url){
    if(trim((string)$url)!=='')return $url;
    $digits=bluprint_contact_digits($number);
    if($digits==='')return '';

    if(strpos($digits,'00')===0)$digits=substr($digits,2);
    if(strpos($digits,'0')===0)$digits='98'.ltrim($digits,'0');

    return 'https://wa.me/'.$digits;
}

/**
 * Build the t.me link for the Telegram channel.
 */
function bluprint_contact_telegram_url($username,$url){
    if(trim((string)$url)!=='')return $url;
    $username=trim((string)$username);
    if($username==='')return '';
    if(strpos($username,'http')===0)return esc_url_raw($username);

    $username=ltrim($username,'@');

    return 'https://t.me/'.$username;
}

/**
 * The support channels rendered on the contact page, in display order.
 */
function bluprint_contact_channels($data){
    $channels=[];

    $phone=trim((string)$data['phone_number']);
    $phone_url=trim((string)$data['phone_url']);
    if($phone===''&&$phone_url!=='')$phone=bluprint_contact_digits($phone_url);
    if($phone!==''||$phone_url!==''){
        $channels[]=[
            'key'=>'phone',
            'icon'=>'phone',
            'label'=>trim((string)$data['phone_label'])?:'تماس تلفنی',
            'value'=>$phone!==''?$phone:'tel',
            'url'=>bluprint_contact_phone_url($phone,$phone_url),
            'target'=>'',
        ];
    }

    $whatsapp=trim((string)$data['whatsapp_number']);
    $whatsapp_url=trim((string)$data['whatsapp_url']);
    if($whatsapp!==''||$whatsapp_url!==''){
        $value=$whatsapp;
        if($value===''&&preg_match('#wa\.me/(\d+)#',$whatsapp_url,$matches))$value=$matches[1];
        $channels[]=[
            'key'=>'whatsapp',
            'icon'=>'whatsapp',
            'label'=>trim((string)$data['whatsapp_label'])?:'چت واتس‌اپ',
            'value'=>$value!==''?$value:'واتس‌اپ',
            'url'=>bluprint_contact_whatsapp_url($whatsapp,$whatsapp_url),
            'target'=>'_blank',
        ];
    }

    $telegram=trim((string)$data['telegram_username']);
    $telegram_url=trim((string)$data['telegram_url']);
    if($telegram!==''||$telegram_url!==''){
        $value=$telegram;
        if($value===''&&preg_match('#t\.me/([^/?#]+)#',$telegram_url,$matches))$value='@'.$matches[1];
        $channels[]=[
            'key'=>'telegram',
            'icon'=>'telegram',
            'label'=>trim((string)$data['telegram_label'])?:'چت تلگرام',
            'value'=>$value!==''?$value:'تلگرام',
            'url'=>bluprint_contact_telegram_url($telegram,$telegram_url),
            'target'=>'_blank',
        ];
    }

    return $channels;
}

/**
 * Register the contact page content box on pages.
 */
function bluprint_contact_register_meta_box(){
    add_meta_box(
        'bluprint-contact-page',
        'محتوای صفحه تماس با ما',
        'bluprint_contact_render_meta_box',
        'page',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes','bluprint_contact_register_meta_box');

function bluprint_contact_render_meta_box($post){
    $data=bluprint_contact_get_data($post->ID);
    ?>
    <div class="bp-contact-admin">
        <?php wp_nonce_field('bluprint_contact_save','bluprint_contact_nonce'); ?>
        <p class="bp-contact-admin__notice">
            این بخش فقط در قالب «تماس با ما» نمایش داده می‌شود. با خالی گذاشتن هر کانال، آن کانال در صفحه پنهان می‌شود.
            پیام‌های دریافتی در بخش <a href="<?php echo esc_url(admin_url('edit.php?post_type=bp_message')); ?>">پیام‌های تماس</a> ذخیره می‌شوند.
        </p>

        <section class="bp-contact-admin__section">
            <h3>بخش معرفی</h3>
            <div class="bp-contact-admin__grid">
                <div class="bp-contact-admin__field">
                    <label for="bp-contact-eyebrow">عنوان بالایی</label>
                    <input type="text" id="bp-contact-eyebrow" name="bluprint_contact[eyebrow]" value="<?php echo esc_attr($data['eyebrow']); ?>" class="large-text">
                </div>
                <div class="bp-contact-admin__field">
                    <label for="bp-contact-title">عنوان اصلی (H1)</label>
                    <input type="text" id="bp-contact-title" name="bluprint_contact[title]" value="<?php echo esc_attr($data['title']); ?>" class="large-text">
                    <span class="description">اگر خالی بماند، عنوان صفحه استفاده می‌شود.</span>
                </div>
            </div>
            <div class="bp-contact-admin__field">
                <label for="bp-contact-intro">پاراگراف معرفی</label>
                <textarea id="bp-contact-intro" name="bluprint_contact[intro]" rows="3" class="large-text"><?php echo esc_textarea($data['intro']); ?></textarea>
            </div>
        </section>

        <section class="bp-contact-admin__section">
            <h3>کانال‌های پشتیبانی</h3>

            <h4>تماس تلفنی</h4>
            <div class="bp-contact-admin__grid">
                <div class="bp-contact-admin__field">
                    <label for="bp-contact-phone-label">برچسب</label>
                    <input type="text" id="bp-contact-phone-label" name="bluprint_contact[phone_label]" value="<?php echo esc_attr($data['phone_label']); ?>" class="large-text">
                </div>
                <div class="bp-contact-admin__field">
                    <label for="bp-contact-phone-number">شماره</label>
                    <input type="tel" id="bp-contact-phone-number" name="bluprint_contact[phone_number]" value="<?php echo esc_attr($data['phone_number']); ?>" class="large-text" dir="ltr">
                </div>
                <div class="bp-contact-admin__field bp-contact-admin__field--full">
                    <label for="bp-contact-phone-url">لینک اختصاصی</label>
                    <input type="url" id="bp-contact-phone-url" name="bluprint_contact[phone_url]" value="<?php echo esc_attr($data['phone_url']); ?>" class="large-text" dir="ltr" placeholder="tel:+982112345678">
                    <span class="description">اگر خالی بماند، از روی شماره، لینک tel: ساخته می‌شود.</span>
                </div>
            </div>

            <h4>واتس‌اپ</h4>
            <div class="bp-contact-admin__grid">
                <div class="bp-contact-admin__field">
                    <label for="bp-contact-whatsapp-label">برچسب</label>
                    <input type="text" id="bp-contact-whatsapp-label" name="bluprint_contact[whatsapp_label]" value="<?php echo esc_attr($data['whatsapp_label']); ?>" class="large-text">
                </div>
                <div class="bp-contact-admin__field">
                    <label for="bp-contact-whatsapp-number">شماره</label>
                    <input type="tel" id="bp-contact-whatsapp-number" name="bluprint_contact[whatsapp_number]" value="<?php echo esc_attr($data['whatsapp_number']); ?>" class="large-text" dir="ltr">
                </div>
                <div class="bp-contact-admin__field bp-contact-admin__field--full">
                    <label for="bp-contact-whatsapp-url">لینک اختصاصی</label>
                    <input type="url" id="bp-contact-whatsapp-url" name="bluprint_contact[whatsapp_url]" value="<?php echo esc_attr($data['whatsapp_url']); ?>" class="large-text" dir="ltr" placeholder="https://wa.me/989121234567">
                    <span class="description">اگر خالی بماند، لینک wa.me از روی شماره ساخته می‌شود.</span>
                </div>
            </div>

            <h4>تلگرام</h4>
            <div class="bp-contact-admin__grid">
                <div class="bp-contact-admin__field">
                    <label for="bp-contact-telegram-label">برچسب</label>
                    <input type="text" id="bp-contact-telegram-label" name="bluprint_contact[telegram_label]" value="<?php echo esc_attr($data['telegram_label']); ?>" class="large-text">
                </div>
                <div class="bp-contact-admin__field">
                    <label for="bp-contact-telegram-username">نام کاربری</label>
                    <input type="text" id="bp-contact-telegram-username" name="bluprint_contact[telegram_username]" value="<?php echo esc_attr($data['telegram_username']); ?>" class="large-text" dir="ltr" placeholder="@blueprint">
                </div>
                <div class="bp-contact-admin__field bp-contact-admin__field--full">
                    <label for="bp-contact-telegram-url">لینک اختصاصی</label>
                    <input type="url" id="bp-contact-telegram-url" name="bluprint_contact[telegram_url]" value="<?php echo esc_attr($data['telegram_url']); ?>" class="large-text" dir="ltr" placeholder="https://t.me/blueprint">
                    <span class="description">اگر خالی بماند، لینک t.me از روی نام کاربری ساخته می‌شود.</span>
                </div>
            </div>
        </section>

        <section class="bp-contact-admin__section">
            <h3>شبکه اجتماعی</h3>
            <div class="bp-contact-admin__grid">
                <div class="bp-contact-admin__field">
                    <label for="bp-contact-instagram-username">نام کاربری اینستاگرام</label>
                    <input type="text" id="bp-contact-instagram-username" name="bluprint_contact[instagram_username]" value="<?php echo esc_attr($data['instagram_username']); ?>" class="large-text" dir="ltr" placeholder="blueprint">
                </div>
                <div class="bp-contact-admin__field">
                    <label for="bp-contact-instagram-url">لینک صفحه اینستاگرام</label>
                    <input type="url" id="bp-contact-instagram-url" name="bluprint_contact[instagram_url]" value="<?php echo esc_attr($data['instagram_url']); ?>" class="large-text" dir="ltr" placeholder="https://instagram.com/blueprint">
                </div>
            </div>
        </section>

        <section class="bp-contact-admin__section">
            <h3>فرم تماس</h3>
            <div class="bp-contact-admin__field">
                <label for="bp-contact-form-title">عنوان فرم</label>
                <input type="text" id="bp-contact-form-title" name="bluprint_contact[form_title]" value="<?php echo esc_attr($data['form_title']); ?>" class="large-text">
            </div>
            <div class="bp-contact-admin__field">
                <label for="bp-contact-form-intro">توضیح کوتاه فرم</label>
                <textarea id="bp-contact-form-intro" name="bluprint_contact[form_intro]" rows="2" class="large-text"><?php echo esc_textarea($data['form_intro']); ?></textarea>
            </div>
            <div class="bp-contact-admin__grid">
                <div class="bp-contact-admin__field">
                    <label for="bp-contact-form-button-text">متن دکمه</label>
                    <input type="text" id="bp-contact-form-button-text" name="bluprint_contact[form_button_text]" value="<?php echo esc_attr($data['form_button_text']); ?>" class="large-text">
                </div>
                <div class="bp-contact-admin__field">
                    <label for="bp-contact-response-note">یادداشت پاسخگویی</label>
                    <input type="text" id="bp-contact-response-note" name="bluprint_contact[response_note]" value="<?php echo esc_attr($data['response_note']); ?>" class="large-text">
                    <span class="description">در نوار مشخصات بالای صفحه و زیر کانال‌ها نمایش داده می‌شود.</span>
                </div>
            </div>
            <div class="bp-contact-admin__field">
                <label for="bp-contact-recipient-email">ایمیل دریافت پیام‌ها</label>
                <input type="email" id="bp-contact-recipient-email" name="bluprint_contact[recipient_email]" value="<?php echo esc_attr($data['recipient_email']); ?>" class="large-text" dir="ltr" placeholder="<?php echo esc_attr(get_option('admin_email')); ?>">
                <span class="description">اگر خالی بماند، پیام‌ها به ایمیل مدیر سایت ارسال می‌شوند.</span>
            </div>
        </section>
    </div>
    <?php
}

/**
 * Persist the contact page meta.
 */
function bluprint_contact_save_meta($post_id){
    if(!isset($_POST['bluprint_contact_nonce'])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['bluprint_contact_nonce'])),'bluprint_contact_save'))return;
    if(defined('DOING_AUTOSAVE')&&DOING_AUTOSAVE)return;
    if(get_post_type($post_id)!=='page')return;
    if(!current_user_can('edit_post',$post_id))return;

    $raw=isset($_POST['bluprint_contact'])&&is_array($_POST['bluprint_contact'])?wp_unslash($_POST['bluprint_contact']):[];
    update_post_meta($post_id,'_bluprint_contact_page',bluprint_contact_sanitize($raw));
}
add_action('save_post_page','bluprint_contact_save_meta');

/**
 * Style the contact content box in the admin.
 */
function bluprint_contact_admin_assets($hook){
    if(!in_array($hook,['post.php','post-new.php'],true))return;
    $screen=get_current_screen();
    if(!$screen||$screen->post_type!=='page')return;

    wp_enqueue_style(
        'bluprint-contact-admin',
        get_theme_file_uri('assets/css/admin/contact-page.css'),
        [],
        bluprint_asset_version('assets/css/admin/contact-page.css')
    );
}
add_action('admin_enqueue_scripts','bluprint_contact_admin_assets');

/* ---------------------------------------------------------
   Secure form handling
   --------------------------------------------------------- */

function bluprint_contact_error_messages(){
    return [
        'security'=>'نشست شما منقضی شده است؛ لطفاً صفحه را تازه کنید و دوباره ارسال کنید.',
        'fast'=>'فرم خیلی زود ارسال شد؛ لطفاً چند ثانیه صبر کنید و دوباره تلاش کنید.',
        'rate'=>'بیش از حد مجاز فرم ارسال کرده‌اید؛ لطفاً کمی بعد دوباره تلاش کنید.',
        'name'=>'لطفاً نام و نام خانوادگی خود را وارد کنید.',
        'phone'=>'لطفاً یک شماره تماس معتبر وارد کنید.',
        'message'=>'لطفاً پیام خود را کامل‌تر بنویسید.',
        'mail'=>'ارسال پیام ممکن نشد؛ لطفاً بعداً تلاش کنید یا از کانال‌های پشتیبانی استفاده کنید.',
    ];
}

function bluprint_contact_client_ip(){
    $ip='';
    if(!empty($_SERVER['REMOTE_ADDR'])){
        $ip=sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']));
    }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
        $parts=explode(',',(string)wp_unslash($_SERVER['HTTP_X_FORWARDED_FOR']));
        $ip=sanitize_text_field(trim($parts[0]));
    }

    return $ip;
}

function bluprint_contact_redirect($status,$error=''){
    $url=get_permalink();
    if(!$url)$url=home_url('/');

    $args=['contact_status'=>$status];
    if($error!=='')$args['contact_error']=$error;

    wp_safe_redirect(add_query_arg($args,$url),303);
    exit;
}

/**
 * Validate a submitted phone number. Returns the normalized value or ''.
 */
function bluprint_contact_validate_phone($value){
    $map=[
        '۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9',
        '٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9',
    ];
    $value=strtr((string)$value,$map);

    if(!preg_match('/^[+\d\s\-\(\)]+$/u',$value))return '';

    $digits=preg_replace('/\D/','',$value);
    if(strlen($digits)<8||strlen($digits)>15)return '';

    $normalized=preg_replace('/[^\d\+\-\(\)\s]/u','',$value);

    return trim($normalized);
}

function bluprint_contact_length($value){
    return function_exists('mb_strlen')?mb_strlen($value,'UTF-8'):max(1,strlen($value));
}

/**
 * Handle contact form submissions: nonce, honeypot, timestamp, rate limit,
 * server-side validation, then email delivery. Always redirects after POST.
 */
function bluprint_contact_handle_submission(){
    if(empty($_SERVER['REQUEST_METHOD'])||$_SERVER['REQUEST_METHOD']!=='POST')return;
    if(!isset($_POST['bluprint_contact_submit'],$_POST['bluprint_contact_nonce']))return;
    if(!is_page_template('templates/page-contact.php'))return;

    $nonce=sanitize_text_field(wp_unslash($_POST['bluprint_contact_nonce']));
    if(!wp_verify_nonce($nonce,'bluprint_contact_send')){
        bluprint_contact_redirect('error','security');
    }

    // Honeypot: bots fill this hidden field, people do not. Silently discard.
    if(isset($_POST['bluprint_contact_website'])&&trim((string)wp_unslash($_POST['bluprint_contact_website']))!==''){
        bluprint_contact_redirect('success');
    }

    $submitted_at=absint($_POST['bluprint_contact_time']??0);
    if($submitted_at<=0||(time()-$submitted_at)<3){
        bluprint_contact_redirect('error','fast');
    }

    $rate_key='bluprint_contact_rl_'.md5(bluprint_contact_client_ip());
    $attempts=(int)get_transient($rate_key);
    if($attempts>=5){
        bluprint_contact_redirect('error','rate');
    }
    set_transient($rate_key,$attempts+1,600);

    $name=isset($_POST['bluprint_contact_name'])?sanitize_text_field(wp_unslash($_POST['bluprint_contact_name'])):'';
    $raw_phone=isset($_POST['bluprint_contact_phone'])?wp_unslash($_POST['bluprint_contact_phone']):'';
    $message=isset($_POST['bluprint_contact_message'])?sanitize_textarea_field(wp_unslash($_POST['bluprint_contact_message'])):'';

    if(bluprint_contact_length($name)<2||bluprint_contact_length($name)>80){
        bluprint_contact_redirect('error','name');
    }

    $phone=bluprint_contact_validate_phone($raw_phone);
    if($phone===''){
        bluprint_contact_redirect('error','phone');
    }

    $message=trim($message);
    if(bluprint_contact_length($message)<10||bluprint_contact_length($message)>2000){
        bluprint_contact_redirect('error','message');
    }

    $data=bluprint_contact_get_data();
    $to=trim((string)$data['recipient_email']);
    if(!is_email($to))$to=(string)get_option('admin_email');
    if(!is_email($to))$to='';

    // Store the message first so it is never lost, even if email fails.
    $source_id=(int)get_queried_object_id();
    $message_id=function_exists('bluprint_contact_store_message')
        ?bluprint_contact_store_message($name,$phone,$message,$source_id)
        :0;

    $site_name=get_bloginfo('name');
    $subject='پیام جدید از '.$name.' — '.$site_name;
    $body_lines=[
        'نام و نام خانوادگی: '.$name,
        'تلفن تماس: '.$phone,
        '',
        'پیام:',
        $message,
        '',
        '——',
        'فرم تماس '.$site_name.' / '.get_permalink(),
    ];
    if($message_id){
        $body_lines[]='مشاهده در پیشخوان: '.admin_url('post.php?post='.$message_id.'&action=edit');
    }
    $body=implode("\n",$body_lines);

    $from_name='=?UTF-8?B?'.base64_encode($site_name).'?=';
    $headers=[
        'Content-Type: text/plain; charset=UTF-8',
        'From: '.$from_name.' <'.get_option('admin_email').'>',
    ];

    $sent=$to!==''?wp_mail($to,$subject,$body,$headers):false;
    if(!$sent&&!$message_id){
        bluprint_contact_redirect('error','mail');
    }

    bluprint_contact_redirect('success');
}
add_action('template_redirect','bluprint_contact_handle_submission');

/**
 * Provide a sensible meta description when Rank Math has none.
 */
function bluprint_contact_meta_description($description){
    if(!is_page_template('templates/page-contact.php'))return $description;
    if(trim((string)$description)!=='')return $description;

    $data=bluprint_contact_get_data();
    $text=$data['intro']?:get_the_excerpt();
    $text=wp_strip_all_tags($text);

    return $text?wp_trim_words($text,32,'…'):$description;
}
add_filter('rank_math/frontend/description','bluprint_contact_meta_description');