<?php
defined('ABSPATH')||exit;

/**
 * eNamad trust badge settings and footer output.
 *
 * The badge is supplied by the eNamad panel as a complete HTML snippet. The
 * snippet is printed exactly as entered (only administrators can edit it), so
 * the provider's own markup and scripts keep working without modification.
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

function bluprint_enamad_sanitize($input){
    $input=is_array($input)?$input:[];
    $code=isset($input['code'])?(string)wp_unslash($input['code']):'';

    return ['code'=>trim($code)];
}

/**
 * Print the exact snippet entered in the settings screen.
 */
function bluprint_enamad_html(){
    $settings=bluprint_enamad_settings();
    return trim((string)$settings['code']);
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
        <p>کد کامل اینماد را از پنل خود کپی کنید و در کادر زیر قرار دهید. همان کد، بدون هیچ تغییری، در فوتر سایت چاپ می‌شود.</p>

        <form method="post" action="options.php">
            <?php settings_fields('bluprint_enamad_group'); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="bluprint-enamad-code">کد اینماد</label>
                    </th>
                    <td>
                        <textarea id="bluprint-enamad-code" name="bluprint_enamad[code]" rows="8" class="large-text code" dir="ltr"><?php echo esc_textarea($settings['code']); ?></textarea>
                        <p class="description">برای حذف badge کادر را خالی کنید و ذخیره کنید.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button('ذخیره اینماد'); ?>
        </form>

        <?php if($preview!==''): ?>
            <hr>
            <h2>پیش‌نمایش</h2>
            <div><?php echo $preview; ?></div>
        <?php endif; ?>
    </div>
    <?php
}
