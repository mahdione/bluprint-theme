<?php
defined('ABSPATH')||exit;

/**
 * GitHub-powered theme updates.
 *
 * The updater reads the newest GitHub version tag (and uses a release ZIP
 * asset when one exists), then feeds it into WordPress's native theme update
 * system. A settings screen under the Bluprint menu exposes the repository,
 * token and manual check action.
 */

function bluprint_theme_updater_defaults(){
    return [
        'repository'=>'mahdione/bluprint-theme',
        'token'=>'',
    ];
}

function bluprint_theme_updater_settings(){
    $stored=get_option('bluprint_theme_updater',[]);
    return wp_parse_args(is_array($stored)?$stored:[],bluprint_theme_updater_defaults());
}

function bluprint_theme_updater_sanitize($input){
    $input=is_array($input)?$input:[];
    $repository=trim((string)($input['repository']??''));

    if($repository!==''&&!preg_match('#^[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+$#',$repository)){
        add_settings_error(
            'bluprint_theme_updater',
            'bp_theme_repository_invalid',
            'آدرس مخزن باید به شکل owner/repository باشد.',
            'error'
        );
        $repository='';
    }

    $token=sanitize_text_field((string)($input['token']??''));

    delete_transient('bluprint_theme_update_info');
    delete_site_transient('update_themes');

    return [
        'repository'=>$repository,
        'token'=>$token,
    ];
}

function bluprint_theme_updater_repository(){
    if(defined('BLUPRINT_THEME_GITHUB_REPO')&&BLUPRINT_THEME_GITHUB_REPO){
        return trim((string)BLUPRINT_THEME_GITHUB_REPO);
    }

    $settings=bluprint_theme_updater_settings();
    return trim((string)$settings['repository']);
}

function bluprint_theme_updater_token(){
    if(defined('BLUPRINT_THEME_GITHUB_TOKEN')&&BLUPRINT_THEME_GITHUB_TOKEN){
        return trim((string)BLUPRINT_THEME_GITHUB_TOKEN);
    }

    $settings=bluprint_theme_updater_settings();
    return trim((string)$settings['token']);
}

/**
 * Add the token to GitHub API and release download requests.
 */
function bluprint_theme_updater_http_args($args,$url){
    $token=bluprint_theme_updater_token();
    if($token==='')return $args;

    $host=wp_parse_url($url,PHP_URL_HOST);

    if(!in_array($host,['api.github.com','github.com','objects.githubusercontent.com'],true)){
        return $args;
    }

    if(!isset($args['headers'])||!is_array($args['headers'])){
        $args['headers']=[];
    }

    if(!isset($args['headers']['Authorization'])){
        $args['headers']['Authorization']='Bearer '.$token;
    }

    return $args;
}
add_filter('http_request_args','bluprint_theme_updater_http_args',10,2);

/**
 * Fetch the newest tag/release and normalize the data WordPress needs.
 */
function bluprint_theme_updater_github_request($path){
    $repository=bluprint_theme_updater_repository();
    if($repository==='')return new WP_Error('bp_repo_missing','مخزن GitHub تنظیم نشده است.');

    $headers=[
        'Accept'=>'application/vnd.github+json',
        'User-Agent'=>'Bluprint-Theme-Updater/'.wp_get_theme()->get('Version'),
        'X-GitHub-Api-Version'=>'2022-11-28',
    ];

    $response=wp_remote_get(
        'https://api.github.com/repos/'.implode('/',array_map('rawurlencode',explode('/',$repository))).$path,
        [
            'timeout'=>15,
            'headers'=>$headers,
        ]
    );

    if(is_wp_error($response))return $response;

    $code=(int)wp_remote_retrieve_response_code($response);
    $body=json_decode((string)wp_remote_retrieve_body($response),true);

    if($code!==200||!is_array($body)){
        $message='پاسخ معتبری از GitHub دریافت نشد.';

        if(is_array($body)&&!empty($body['message']))$message=(string)$body['message'];
        if($code===404)$message='مخزن یا نسخه‌ای با این مشخصات پیدا نشد.';

        return new WP_Error('bp_github_'.$code,$message);
    }

    return $body;
}

function bluprint_theme_updater_version_from_tag($tag){
    return preg_match('/\d+(?:\.\d+)*/',(string)$tag,$matches)?$matches[0]:'';
}

/**
 * Fetch the newest tag/release. Tags are the source of truth so a version can
 * be published by simply tagging the repository; a release ZIP asset is used
 * when it exists and matches the newest tag.
 */
function bluprint_theme_updater_get_remote($force=false){
    $cache_key='bluprint_theme_update_info';
    $cached=get_transient($cache_key);

    if(!$force&&is_array($cached))return $cached;

    $repository=bluprint_theme_updater_repository();

    if($repository===''){
        return [
            'error'=>'برای فعال‌سازی به‌روزرسانی خودکار، مخزن GitHub را در تنظیمات وارد کنید.',
            'checked_at'=>time(),
        ];
    }

    $release=bluprint_theme_updater_github_request('/releases/latest');
    $release=is_wp_error($release)?null:$release;

    $tags=bluprint_theme_updater_github_request('/tags?per_page=50');

    if(is_wp_error($tags)){
        $error=$tags->get_error_message();
        $result=['error'=>$error,'checked_at'=>time()];
        set_transient($cache_key,$result,15*MINUTE_IN_SECONDS);
        return $result;
    }

    $versions=[];

    foreach($tags as $tag){
        if(!is_array($tag))continue;
        $tag_name=(string)($tag['name']??'');
        $tag_version=bluprint_theme_updater_version_from_tag($tag_name);
        if($tag_version!=='')$versions[$tag_version]=$tag_name;
    }

    $release_version=$release?bluprint_theme_updater_version_from_tag((string)($release['tag_name']??'')):'';

    if($release_version!==''&&!isset($versions[$release_version])){
        $versions[$release_version]=(string)($release['tag_name']??$release_version);
    }

    if(!$versions){
        $result=['error'=>'هیچ تگ نسخه‌ای در مخزن GitHub پیدا نشد.','checked_at'=>time()];
        set_transient($cache_key,$result,15*MINUTE_IN_SECONDS);
        return $result;
    }

    uksort($versions,static function($a,$b){
        return version_compare($a,$b);
    });

    $version=(string)array_key_last($versions);
    $tag=(string)$versions[$version];
    $package='';

    // Prefer a release asset when it belongs to the newest tag.
    if($release&&$release_version===$version){
        $assets=isset($release['assets'])&&is_array($release['assets'])?$release['assets']:[];

        foreach($assets as $asset){
            $name=(string)($asset['name']??'');
            if(strtolower($name)==='bluprint-theme.zip'&&!empty($asset['browser_download_url'])){
                $package=(string)$asset['browser_download_url'];
                break;
            }
        }
    }

    if($package===''){
        $package='https://github.com/'.implode('/',array_map('rawurlencode',explode('/',$repository))).'/archive/refs/tags/'.rawurlencode($tag).'.zip';
    }

    $result=[
        'version'=>$version,
        'tag'=>$tag,
        'package'=>$package,
        'url'=>'https://github.com/'.$repository.'/tree/'.rawurlencode($tag),
        'published_at'=>$release?(string)($release['published_at']??''):'',
        'name'=>$release?(string)($release['name']??$tag):$tag,
        'body'=>$release?(string)($release['body']??''):'',
        'checked_at'=>time(),
        'error'=>'',
    ];

    set_transient($cache_key,$result,6*HOUR_IN_SECONDS);

    return $result;
}

/**
 * Register the update with WordPress's native theme update transient.
 */
function bluprint_theme_updater_check_update($transient){
    if(!is_object($transient))return $transient;

    // Avoid GitHub API latency on ordinary front-end requests.
    if(!is_admin()&&!wp_doing_cron())return $transient;

    $slug=get_template();
    $current_version=isset($transient->checked[$slug])
        ?(string)$transient->checked[$slug]
        :(string)wp_get_theme($slug)->get('Version');

    $remote=bluprint_theme_updater_get_remote();

    if(!empty($remote['error'])||empty($remote['version'])||empty($remote['package'])){
        return $transient;
    }

    if(version_compare($remote['version'],$current_version,'>')){
        $transient->response[$slug]=[
            'theme'=>$slug,
            'new_version'=>$remote['version'],
            'url'=>$remote['url'],
            'package'=>$remote['package'],
            'requires'=>wp_get_theme($slug)->get('RequiresWP'),
            'requires_php'=>wp_get_theme($slug)->get('RequiresPHP'),
        ];
    }elseif(isset($transient->response[$slug])){
        unset($transient->response[$slug]);
    }

    return $transient;
}
add_filter('pre_set_site_transient_update_themes','bluprint_theme_updater_check_update');

/**
 * GitHub tag archives extract to bluprint-theme-{tag}/. Rename the folder so
 * WordPress installs the update into the existing theme directory.
 */
function bluprint_theme_updater_source_selection($source,$remote_source,$upgrader,$hook_extra){
    $theme=isset($hook_extra['theme'])?(string)$hook_extra['theme']:'';
    $target_slug=get_template();

    if($theme!==$target_slug)return $source;

    $source=rtrim((string)$source,'/\\');
    $base=basename($source);

    if($base===$target_slug)return $source.'/';

    $target=dirname($source).'/'.$target_slug;

    global $wp_filesystem;

    if($wp_filesystem&&$wp_filesystem->move($source,$target,true)){
        return $target.'/';
    }

    if(!$wp_filesystem&&@rename($source,$target)){
        return $target.'/';
    }

    return $source.'/';
}
add_filter('upgrader_source_selection','bluprint_theme_updater_source_selection',10,4);

/**
 * Provide version details for the "View version details" modal.
 */
function bluprint_theme_updater_themes_api($result,$action,$args){
    if($action!=='theme_information'||empty($args->slug)||$args->slug!==get_template()){
        return $result;
    }

    $remote=bluprint_theme_updater_get_remote();

    if(!empty($remote['error'])||empty($remote['version']))return $result;

    $theme=wp_get_theme();

    return (object)[
        'name'=>$theme->get('Name'),
        'slug'=>get_template(),
        'version'=>$remote['version'],
        'author'=>$theme->get('Author'),
        'homepage'=>$remote['url'],
        'requires'=>$theme->get('RequiresWP'),
        'tested'=>$theme->get('Tested up to'),
        'requires_php'=>$theme->get('RequiresPHP'),
        'download_link'=>$remote['package'],
        'last_updated'=>$remote['published_at'],
        'sections'=>[
            'description'=>wpautop(wp_kses_post($theme->get('Description'))),
            'changelog'=>wpautop(wp_kses_post($remote['body'])),
        ],
    ];
}
add_filter('themes_api','bluprint_theme_updater_themes_api',20,3);

function bluprint_theme_updater_register_settings(){
    register_setting('bluprint_theme_updater_group','bluprint_theme_updater',[
        'type'=>'array',
        'sanitize_callback'=>'bluprint_theme_updater_sanitize',
        'default'=>bluprint_theme_updater_defaults(),
    ]);
}
add_action('admin_init','bluprint_theme_updater_register_settings');

function bluprint_theme_updater_admin_menu(){
    add_submenu_page(
        'bluprint-settings',
        'به‌روزرسانی قالب',
        'به‌روزرسانی قالب',
        'manage_options',
        'bluprint-theme-updates',
        'bluprint_theme_updater_admin_page'
    );
}
add_action('admin_menu','bluprint_theme_updater_admin_menu',11);

function bluprint_theme_updater_admin_page(){
    if(!current_user_can('manage_options'))return;

    $settings=bluprint_theme_updater_settings();
    $current_version=(string)wp_get_theme()->get('Version');
    $remote=bluprint_theme_updater_get_remote();
    $has_update=empty($remote['error'])&&!empty($remote['version'])&&version_compare($remote['version'],$current_version,'>');
    ?>
    <div class="wrap">
        <h1>به‌روزرسانی قالب</h1>

        <?php if(isset($_GET['checked'])&&$_GET['checked']==='1'): ?>
            <div class="notice notice-success is-dismissible"><p>وضعیت به‌روزرسانی بررسی شد.</p></div>
        <?php elseif(isset($_GET['checked'])&&$_GET['checked']==='error'): ?>
            <div class="notice notice-error is-dismissible"><p>بررسی به‌روزرسانی انجام نشد. پیام خطا را در پایین ببینید.</p></div>
        <?php endif; ?>

        <p>
            نسخه فعلی: <strong><?php echo esc_html($current_version); ?></strong>
            <?php if(!empty($remote['version'])): ?>
                &nbsp;|&nbsp; آخرین نسخه GitHub: <strong><?php echo esc_html($remote['version']); ?></strong>
            <?php endif; ?>
        </p>

        <?php if($has_update): ?>
            <div class="notice notice-warning">
                <p>
                    نسخه جدید قالب منتشر شده است.
                    <a class="button button-primary" href="<?php echo esc_url(admin_url('themes.php')); ?>">به صفحه قالب‌ها برو</a>
                </p>
            </div>
        <?php elseif(!empty($remote['error'])): ?>
            <div class="notice notice-error"><p><?php echo esc_html($remote['error']); ?></p></div>
        <?php else: ?>
            <div class="notice notice-success"><p>قالب به‌روز است.</p></div>
        <?php endif; ?>

        <form method="post" action="options.php">
            <?php settings_fields('bluprint_theme_updater_group'); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="bluprint-theme-repository">مخزن GitHub</label></th>
                    <td>
                        <input id="bluprint-theme-repository" type="text" class="regular-text" dir="ltr" name="bluprint_theme_updater[repository]" value="<?php echo esc_attr($settings['repository']); ?>" placeholder="username/bluprint-theme">
                        <p class="description">به شکل <code>owner/repository</code>. آخرین تگ نسخه (مثل v1.2.0) مبنای به‌روزرسانی است.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="bluprint-theme-token">GitHub Token</label></th>
                    <td>
                        <input id="bluprint-theme-token" type="password" class="regular-text" dir="ltr" name="bluprint_theme_updater[token]" value="<?php echo esc_attr($settings['token']); ?>" autocomplete="new-password">
                        <p class="description">برای مخزن عمومی اختیاری است؛ برای مخزن خصوصی یا جلوگیری از محدودیت GitHub لازم است. بهتر است در <code>wp-config.php</code> با ثابت <code>BLUPRINT_THEME_GITHUB_TOKEN</code> تعریف شود.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button('ذخیره تنظیمات'); ?>
        </form>

        <hr>

        <p>
            <a class="button" href="<?php echo esc_url(wp_nonce_url(add_query_arg('action','bluprint_theme_check_update',admin_url('admin-post.php')),'bluprint_theme_check_update')); ?>">بررسی فوری به‌روزرسانی</a>
            <?php if(!empty($remote['url'])): ?>
                <a class="button button-link" href="<?php echo esc_url($remote['url']); ?>" target="_blank" rel="noopener noreferrer">مشاهده در GitHub</a>
            <?php endif; ?>
        </p>

        <p class="description">
            هر بار که تگ جدید (مثل <code>v1.2.0</code>) در GitHub ساخته می‌شود، وردپرس می‌تواند نسخه جدید را از صفحه «قالب‌ها» یا همین صفحه نصب کند. اگر Release دارای فایل <code>bluprint-theme.zip</code> باشد، همان فایل ترجیح داده می‌شود.
        </p>
    </div>
    <?php
}

function bluprint_theme_updater_manual_check(){
    if(!current_user_can('manage_options')){
        wp_die('شما اجازه انجام این کار را ندارید.','دسترسی غیرمجاز',['response'=>403]);
    }

    check_admin_referer('bluprint_theme_check_update');

    delete_transient('bluprint_theme_update_info');
    delete_site_transient('update_themes');

    $remote=bluprint_theme_updater_get_remote(true);
    $status=empty($remote['error'])?'1':'error';

    wp_safe_redirect(add_query_arg([
        'page'=>'bluprint-theme-updates',
        'checked'=>$status,
    ],admin_url('admin.php')));
    exit;
}
add_action('admin_post_bluprint_theme_check_update','bluprint_theme_updater_manual_check');
