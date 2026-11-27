<?php
defined('ABSPATH')||exit;

/**
 * Trust badges (eNamad) settings and footer output.
 *
 * The badge is a simple link + image. Only those tags are kept when saving, and
 * the image is rendered with lazy loading and fixed dimensions so the external
 * request never blocks rendering or shifts the footer.
 */

function bluprint_enamad_defaults(){
    $code=<<<'HTML'
<a referrerpolicy='origin' target='_blank' href='https://trustseal.enamad.ir/?id=8940400&Code=DsHjQIcs1zZzJ0j8LJ32hjhA6qxE9uXX'><img referrerpolicy='origin' src='https://trustseal.enamad.ir/logo.aspx?id=8940400&Code=DsHjQIcs1zZzJ0j8LJ32hjhA6qxE9uXX' alt='' style='cursor:pointer' code='DsHjQIcs1zZzJ0j8LJ32hjhA6qxE9uXX'></a>
HTML;

    return ['code'=>$code];
}

function bluprint_enamad_settings(){
    $stored=get_option('bluprint_enamad',[]);
    return wp_parse_args(is_array($stored)?$stored:[],bluprint_enamad_defaults());
}

function bluprint_enamad_allowed_html(){
    return [
        'a'=>[
            'href'=>true,
            'target'=>true,
            'rel'=>true,
            'referrerpolicy'=>true,
            'title'=>true,
            'aria-label'=>true,
            'class'=>true,
        ],
        'img'=>[
            'src'=>true,
            'alt'=>true,
            'width'=>true,
            'height'=>true,
            'loading'=>true,
            'decoding'=>true,
            'fetchpriority'=>true,
            'referrerpolicy'=>true,
            'class'=>true,
        ],
    ];
}

function bluprint_enamad_sanitize($input){
    $input=is_array($input)?$input:[];
    $code=trim((string)($input['code']??''));

    // Drop script/style blocks completely so their contents never become text.
    $code=preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is','',$code);

    // Keep only the badge markup. Scripts and arbitrary containers are dropped.
    $code=wp_kses($code,bluprint_enamad_allowed_html());

    return ['code'=>$code];
}

/**
 * Return the sanitized, performance-friendly badge markup.
 */
function bluprint_enamad_html(){
    static $cached=null;

    if($cached!==null)return $cached;

    $settings=bluprint_enamad_settings();
    $code=trim((string)$settings['code']);

    if($code==='')return $cached='';

    $code=preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is','',$code);
    $code=wp_kses($code,bluprint_enamad_allowed_html());

    if(class_exists('WP_HTML_Tag_Processor')){
        $processor=new WP_HTML_Tag_Processor($code);

        if($processor->next_tag('A')){
            if($processor->get_attribute('target')==='_blank'&&!$processor->get_attribute('rel')){
                $processor->set_attribute('rel','noopener noreferrer');
            }

            if(!$processor->get_attribute('aria-label')){
                $processor->set_attribute('aria-label','نماد اعتماد الکترونیکی اینماد');
            }
        }

        if($processor->next_tag('IMG')){
            $processor->set_attribute('loading','lazy');
            $processor->set_attribute('decoding','async');
            $processor->set_attribute('fetchpriority','low');
            $processor->set_attribute('width','96');
            $processor->set_attribute('height','96');

            if(!$processor->get_attribute('alt')){
                $processor->set_attribute('alt','اینماد');
            }
        }

        return $cached=$processor->get_updated_html();
    }

    // Fallback for older WordPress versions without the HTML API.
    $cached=preg_replace(
        '/<img\b/i',
        '<img loading="lazy" decoding="async" fetchpriority="low" width="96" height="96"',
        $code,
        1
    );

    return $cached;
}

function bluprint_enamad_register_settings(){
    register_setting('bluprint_enamad_group','bluprint_enamad',[
        'type'=>'array',
        'sanitize_callback'=>'bluprint_enamad_sanitize',
        'default'=>bluprint_enamad_defaults(),
    ]);
}
add_action('admin_init','bluprint_enamad_register_settings');

function bluprint_enamad_admin_menu(){
    add_submenu_page(
        'bluprint-settings',
        'اینماد',
        'اینماد',
        'manage_options',
        'bluprint-enamad',
        'bluprint_enamad_admin_page'
    );
}
add_action('admin_menu','bluprint_enamad_admin_menu',12);

function bluprint_enamad_admin_page(){
    if(!current_user_can('manage_options'))return;

    $settings=bluprint_enamad_settings();
    $preview=bluprint_enamad_html();
    ?>
    <div class="wrap">
        <h1>اینماد</h1>
        <p>کد کامل اینماد را از پنل خود کپی کنید و در کادر زیر قرار دهید. فقط لینک و تصویر badge نگه داشته می‌شود و اسکریپت‌های اضافه حذف می‌شوند.</p>

        <form method="post" action="options.php">
            <?php settings_fields('bluprint_enamad_group'); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="bluprint-enamad-code">کد اینماد</label>
                    </th>
                    <td>
                        <textarea id="bluprint-enamad-code" name="bluprint_enamad[code]" rows="8" class="large-text code" dir="ltr"><?php echo esc_textarea($settings['code']); ?></textarea>
                        <p class="description">برای حفظ سرعت سایت، تصویر اینماد به‌صورت lazy load و با ابعاد ثابت در فوتر نمایش داده می‌شود. برای حذف badge کادر را خالی کنید و ذخیره کنید.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button('ذخیره اینماد'); ?>
        </form>

        <?php if($preview!==''): ?>
            <hr>
            <h2>پیش‌نمایش</h2>
            <div style="display:inline-flex;align-items:center;justify-content:center;width:96px;height:96px;padding:6px;border:1px solid #dcdcde;background:#fff">
                <?php echo $preview; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
}
