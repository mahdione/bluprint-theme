<?php
defined('ABSPATH')||exit;

/**
 * Contact message inbox.
 *
 * Every contact form submission is stored as a private `bp_message` entry so
 * nothing is lost when email delivery fails. The list is nested under the
 * Bluprint admin menu, unread messages are surfaced in the admin bar and the
 * menu badge, and messages can be searched, marked read/unread and trashed.
 */

/* ---------------------------------------------------------
   Storage
   --------------------------------------------------------- */

function bluprint_contact_inbox_post_type(){
    register_post_type('bp_message',[
        'labels'=>[
            'name'=>'پیام‌های تماس',
            'singular_name'=>'پیام تماس',
            'menu_name'=>'پیام‌های تماس',
            'all_items'=>'پیام‌های تماس',
            'edit_item'=>'جزئیات پیام',
            'view_item'=>'مشاهده پیام',
            'search_items'=>'جستجوی پیام‌ها',
            'not_found'=>'پیامی پیدا نشد.',
            'not_found_in_trash'=>'پیامی در زباله‌دان نیست.',
        ],
        'public'=>false,
        'publicly_queryable'=>false,
        'exclude_from_search'=>true,
        'show_ui'=>true,
        'show_in_menu'=>'bluprint-settings',
        'show_in_admin_bar'=>false,
        'show_in_rest'=>false,
        'supports'=>['title'],
        'capability_type'=>'post',
        'capabilities'=>[
            'create_posts'=>'do_not_allow',
        ],
        'map_meta_cap'=>true,
        'menu_icon'=>'dashicons-email-alt',
        'hierarchical'=>false,
        'rewrite'=>false,
        'query_var'=>false,
        'can_export'=>true,
        'delete_with_user'=>false,
    ]);
}
add_action('init','bluprint_contact_inbox_post_type');

/**
 * Persist a validated submission. Returns the new message ID or 0.
 */
function bluprint_contact_store_message($name,$phone,$message,$source_id=0){
    $post_id=wp_insert_post([
        'post_type'=>'bp_message',
        'post_status'=>'publish',
        'post_title'=>$name,
        'post_content'=>$message,
        'post_author'=>0,
    ],true);

    if(is_wp_error($post_id)||!$post_id)return 0;

    $digits=bluprint_contact_digits($phone);
    $ip=bluprint_contact_client_ip();
    if($ip!==''&&function_exists('wp_privacy_anonymize_ip'))$ip=wp_privacy_anonymize_ip($ip);

    update_post_meta($post_id,'_bp_message_phone',$phone);
    update_post_meta($post_id,'_bp_message_phone_digits',$digits);
    update_post_meta($post_id,'_bp_message_read','0');
    update_post_meta($post_id,'_bp_message_ip',$ip);
    update_post_meta($post_id,'_bp_message_source',absint($source_id));

    delete_transient('bluprint_contact_unread_count');

    return (int)$post_id;
}

/* ---------------------------------------------------------
   Unread state
   --------------------------------------------------------- */

function bluprint_contact_mark_read($post_id,$read=true){
    if(get_post_type($post_id)!=='bp_message')return;
    update_post_meta($post_id,'_bp_message_read',$read?'1':'0');
    delete_transient('bluprint_contact_unread_count');
}

function bluprint_contact_unread_count(){
    $cached=get_transient('bluprint_contact_unread_count');
    if($cached!==false)return (int)$cached;

    $query=new WP_Query([
        'post_type'=>'bp_message',
        'post_status'=>'publish',
        'posts_per_page'=>1,
        'fields'=>'ids',
        'no_found_rows'=>false,
        'update_post_term_cache'=>false,
        'update_post_meta_cache'=>false,
        'meta_query'=>[
            'relation'=>'OR',
            [
                'key'=>'_bp_message_read',
                'value'=>'1',
                'compare'=>'!=',
            ],
            [
                'key'=>'_bp_message_read',
                'compare'=>'NOT EXISTS',
            ],
        ],
    ]);

    $count=(int)$query->found_posts;
    set_transient('bluprint_contact_unread_count',$count,5*MINUTE_IN_SECONDS);

    return $count;
}

function bluprint_contact_inbox_flush_count($post_id){
    if(get_post_type($post_id)==='bp_message')delete_transient('bluprint_contact_unread_count');
}
add_action('save_post_bp_message','bluprint_contact_inbox_flush_count');
add_action('deleted_post','bluprint_contact_inbox_flush_count');
add_action('trashed_post','bluprint_contact_inbox_flush_count');
add_action('untrashed_post','bluprint_contact_inbox_flush_count');

/* ---------------------------------------------------------
   Admin menu + admin bar badges
   --------------------------------------------------------- */

function bluprint_contact_inbox_menu_badge(){
    global $submenu;
    if(empty($submenu['bluprint-settings']))return;

    $count=bluprint_contact_unread_count();
    if($count<1)return;

    foreach($submenu['bluprint-settings'] as $index=>$item){
        if(($item[2]??'')!=='edit.php?post_type=bp_message')continue;
        $submenu['bluprint-settings'][$index][0].=' <span class="awaiting-mod"><span class="pending-count">'.number_format_i18n($count).'</span></span>';
        break;
    }
}
add_action('admin_menu','bluprint_contact_inbox_menu_badge',999);

function bluprint_contact_inbox_admin_bar($bar){
    if(!is_admin_bar_showing()||!current_user_can('edit_posts'))return;

    $count=bluprint_contact_unread_count();
    $title='<span class="ab-label">پیام‌ها</span>';

    if($count>0){
        $title.='<span class="bp-inbox-count" aria-hidden="true">'.number_format_i18n($count).'</span>';
    }

    $bar->add_node([
        'id'=>'bp-messages',
        'title'=>$title,
        'href'=>admin_url('edit.php?post_type=bp_message'),
        'meta'=>[
            'title'=>$count>0?sprintf('%s پیام خوانده‌نشده',$count):'پیام‌های تماس',
            'class'=>$count>0?'bp-inbox-has-unread':'',
        ],
    ]);

    if($count<1)return;

    $latest=get_posts([
        'post_type'=>'bp_message',
        'post_status'=>'publish',
        'posts_per_page'=>3,
        'orderby'=>'date',
        'order'=>'DESC',
        'no_found_rows'=>true,
        'meta_query'=>[
            'relation'=>'OR',
            [
                'key'=>'_bp_message_read',
                'value'=>'1',
                'compare'=>'!=',
            ],
            [
                'key'=>'_bp_message_read',
                'compare'=>'NOT EXISTS',
            ],
        ],
    ]);

    foreach($latest as $message){
        $sender=trim((string)$message->post_title);
        $excerpt=wp_trim_words(wp_strip_all_tags((string)$message->post_content),5,'…');

        $bar->add_node([
            'parent'=>'bp-messages',
            'id'=>'bp-message-'.$message->ID,
            'title'=>sprintf(
                '<span class="bp-ab-sender">%s</span><span class="bp-ab-excerpt">%s</span>',
                esc_html($sender!==''?$sender:'بدون نام'),
                esc_html($excerpt)
            ),
            'href'=>get_edit_post_link($message->ID,'raw'),
        ]);
    }

    $bar->add_node([
        'parent'=>'bp-messages',
        'id'=>'bp-messages-all',
        'title'=>'مشاهده همه پیام‌ها',
        'href'=>admin_url('edit.php?post_type=bp_message'),
    ]);
}
add_action('admin_bar_menu','bluprint_contact_inbox_admin_bar',90);

/**
 * Badge and message panel styles, loaded both in the admin and on the front
 * end (the admin bar is visible there too).
 */
function bluprint_contact_inbox_assets(){
    if(!current_user_can('edit_posts'))return;
    if(!is_admin()&&!is_admin_bar_showing())return;

    wp_enqueue_style(
        'bluprint-contact-inbox',
        get_theme_file_uri('assets/css/admin/contact-inbox.css'),
        [],
        bluprint_asset_version('assets/css/admin/contact-inbox.css')
    );
}
add_action('admin_enqueue_scripts','bluprint_contact_inbox_assets');
add_action('wp_enqueue_scripts','bluprint_contact_inbox_assets');

/* ---------------------------------------------------------
   List table
   --------------------------------------------------------- */

function bluprint_contact_inbox_title_placeholder($text,$post){
    if($post&&$post->post_type==='bp_message')return 'نام فرستنده';
    return $text;
}
add_filter('enter_title_here','bluprint_contact_inbox_title_placeholder',10,2);

function bluprint_contact_inbox_disable_quick_edit($enabled,$post_type){
    return $post_type==='bp_message'?false:$enabled;
}
add_filter('quick_edit_enabled_for_post_type','bluprint_contact_inbox_disable_quick_edit',10,2);

function bluprint_contact_inbox_columns($columns){
    return [
        'cb'=>$columns['cb'],
        'title'=>'فرستنده',
        'bp_phone'=>'تلفن تماس',
        'bp_message'=>'پیام',
        'bp_state'=>'وضعیت',
        'date'=>'تاریخ',
    ];
}
add_filter('manage_bp_message_posts_columns','bluprint_contact_inbox_columns');

function bluprint_contact_inbox_column_content($column,$post_id){
    if($column==='bp_phone'){
        $phone=get_post_meta($post_id,'_bp_message_phone',true);
        $digits=get_post_meta($post_id,'_bp_message_phone_digits',true);
        if($phone===''){
            echo '—';
            return;
        }
        $url=$digits!==''?'tel:'.$digits:'';
        if($url!==''){
            printf('<a href="%s" dir="ltr">%s</a>',$url,esc_html($phone));
        }else{
            printf('<span dir="ltr">%s</span>',esc_html($phone));
        }
        return;
    }

    if($column==='bp_message'){
        $message=wp_strip_all_tags(get_post_field('post_content',$post_id));
        $excerpt=wp_trim_words($message,14,'…');
        echo esc_html($excerpt!==''?$excerpt:'—');
        return;
    }

    if($column==='bp_state'){
        $read=get_post_meta($post_id,'_bp_message_read',true)==='1';
        printf(
            '<span class="bp-inbox-state %s">%s</span>',
            $read?'is-read':'is-unread',
            $read?'خوانده‌شده':'خوانده‌نشده'
        );
    }
}
add_action('manage_bp_message_posts_custom_column','bluprint_contact_inbox_column_content',10,2);

function bluprint_contact_inbox_sortable_columns($columns){
    $columns['bp_state']='bp_message_read';
    return $columns;
}
add_filter('manage_edit-bp_message_sortable_columns','bluprint_contact_inbox_sortable_columns');

function bluprint_contact_inbox_sort_by_state($query){
    if(!is_admin()||!$query->is_main_query())return;
    if($query->get('post_type')!=='bp_message')return;
    if($query->get('orderby')!=='bp_message_read')return;

    $query->set('meta_key','_bp_message_read');
    $query->set('orderby','meta_value');
    if(!$query->get('order'))$query->set('order','ASC');
}
add_action('pre_get_posts','bluprint_contact_inbox_sort_by_state');

function bluprint_contact_inbox_style_row($classes,$css_class,$post_id){
    if(get_post_type($post_id)!=='bp_message')return $classes;
    if(get_post_meta($post_id,'_bp_message_read',true)!=='1')$classes[]='bp-message-unread';
    return $classes;
}
add_filter('post_class','bluprint_contact_inbox_style_row',10,3);

function bluprint_contact_inbox_post_states($states,$post){
    if($post->post_type!=='bp_message')return $states;
    if(get_post_meta($post->ID,'_bp_message_read',true)!=='1'){
        $states['bp_unread']='<span class="bp-inbox-dot" aria-hidden="true"></span>خوانده‌نشده';
    }
    return $states;
}
add_filter('display_post_states','bluprint_contact_inbox_post_states',10,2);

/* ---------------------------------------------------------
   Reading, row actions and bulk actions
   --------------------------------------------------------- */

function bluprint_contact_inbox_toggle_url($post_id,$state){
    $args=[
        'post_type'=>'bp_message',
        'bp_toggle'=>$state,
        'post'=>$post_id,
    ];
    if(isset($_GET['bp_unread'])&&sanitize_key(wp_unslash($_GET['bp_unread']))==='1'){
        $args['bp_unread']=1;
    }

    return wp_nonce_url(
        add_query_arg($args,admin_url('edit.php')),
        'bp_toggle_'.$post_id
    );
}

function bluprint_contact_inbox_row_actions($actions,$post){
    if($post->post_type!=='bp_message')return $actions;

    $read=get_post_meta($post->ID,'_bp_message_read',true)==='1';
    $actions['bp_toggle']=sprintf(
        '<a href="%s">%s</a>',
        esc_url(bluprint_contact_inbox_toggle_url($post->ID,$read?'unread':'read')),
        $read?'علامت‌گذاری خوانده‌نشده':'علامت‌گذاری خوانده‌شده'
    );

    return $actions;
}
add_filter('post_row_actions','bluprint_contact_inbox_row_actions',10,2);

/**
 * Handle the single-message read/unread toggle before the list table loads.
 */
function bluprint_contact_inbox_handle_toggle(){
    if(!is_admin()||!isset($_GET['bp_toggle'],$_GET['post']))return;
    if(!isset($_GET['post_type'])||sanitize_key(wp_unslash($_GET['post_type']))!=='bp_message')return;
    if(!in_array($_GET['bp_toggle'],['read','unread'],true))return;

    $post_id=absint($_GET['post']);
    check_admin_referer('bp_toggle_'.$post_id);

    if(!current_user_can('edit_post',$post_id)){
        wp_die('شما اجازه انجام این کار را ندارید.','دسترسی غیرمجاز',['response'=>403]);
    }

    bluprint_contact_mark_read($post_id,$_GET['bp_toggle']==='read');

    // Always return to the inbox; the edit screen would immediately mark the
    // message read again.
    $redirect=admin_url('edit.php?post_type=bp_message');
    if(isset($_GET['bp_unread'])&&sanitize_key(wp_unslash($_GET['bp_unread']))==='1'){
        $redirect=add_query_arg('bp_unread',1,$redirect);
    }

    wp_safe_redirect($redirect);
    exit;
}
add_action('load-edit.php','bluprint_contact_inbox_handle_toggle');

/**
 * Opening a message marks it as read.
 */
function bluprint_contact_inbox_mark_opened(){
    $post_id=isset($_GET['post'])?absint($_GET['post']):0;
    if(!$post_id||get_post_type($post_id)!=='bp_message')return;
    if(!current_user_can('edit_post',$post_id))return;
    if(get_post_meta($post_id,'_bp_message_read',true)!=='1'){
        bluprint_contact_mark_read($post_id,true);
    }
}
add_action('load-post.php','bluprint_contact_inbox_mark_opened');

function bluprint_contact_inbox_bulk_actions($actions){
    $actions['bp_mark_read']='علامت‌گذاری به‌عنوان خوانده‌شده';
    $actions['bp_mark_unread']='علامت‌گذاری به‌عنوان خوانده‌نشده';
    return $actions;
}
add_filter('bulk_actions-edit-bp_message','bluprint_contact_inbox_bulk_actions');

function bluprint_contact_inbox_handle_bulk($redirect,$action,$post_ids){
    if($action!=='bp_mark_read'&&$action!=='bp_mark_unread')return $redirect;
    if(empty($post_ids)||!is_array($post_ids))return $redirect;

    $read=$action==='bp_mark_read';
    $done=0;

    foreach($post_ids as $post_id){
        $post_id=absint($post_id);
        if(!$post_id||!current_user_can('edit_post',$post_id))continue;
        if(get_post_type($post_id)!=='bp_message')continue;
        bluprint_contact_mark_read($post_id,$read);
        $done++;
    }

    return add_query_arg('bp_bulk_done',$done,$redirect);
}
add_filter('handle_bulk_actions-edit-bp_message','bluprint_contact_inbox_handle_bulk',10,3);

function bluprint_contact_inbox_bulk_notice(){
    $screen=get_current_screen();
    if(!$screen||$screen->id!=='edit-bp_message')return;
    if(!isset($_GET['bp_bulk_done']))return;

    $done=absint($_GET['bp_bulk_done']);
    if($done<1)return;

    printf(
        '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
        esc_html(sprintf('%s پیام به‌روزرسانی شد.',number_format_i18n($done)))
    );
}
add_action('admin_notices','bluprint_contact_inbox_bulk_notice');

/* ---------------------------------------------------------
   Message detail panel
   --------------------------------------------------------- */

function bluprint_contact_inbox_meta_box(){
    add_meta_box(
        'bluprint-message-detail',
        'جزئیات پیام',
        'bluprint_contact_inbox_render_meta_box',
        'bp_message',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes','bluprint_contact_inbox_meta_box');

function bluprint_contact_inbox_render_meta_box($post){
    $read=get_post_meta($post->ID,'_bp_message_read',true)==='1';
    $phone=get_post_meta($post->ID,'_bp_message_phone',true);
    $digits=get_post_meta($post->ID,'_bp_message_phone_digits',true);
    $ip=get_post_meta($post->ID,'_bp_message_ip',true);
    $source=absint(get_post_meta($post->ID,'_bp_message_source',true));
    $message=wp_strip_all_tags(get_post_field('post_content',$post->ID));
    $date=get_the_date('Y/m/d H:i',$post);
    ?>
    <div class="bp-inbox-message">
        <div class="bp-inbox-message__head">
            <p class="bp-inbox-message__sender"><?php echo esc_html(get_the_title($post)); ?></p>
            <span class="bp-inbox-state <?php echo $read?'is-read':'is-unread'; ?>">
                <?php echo $read?'خوانده‌شده':'خوانده‌نشده'; ?>
            </span>
            <span class="bp-inbox-message__date" dir="ltr"><?php echo esc_html($date); ?></span>
        </div>

        <div class="bp-inbox-message__contacts">
            <span class="bp-inbox-message__contact">
                <span class="bp-inbox-message__label">تلفن</span>
                <?php if($phone!==''&&$digits!==''): ?>
                    <a href="tel:<?php echo esc_attr($digits); ?>" dir="ltr"><?php echo esc_html($phone); ?></a>
                <?php elseif($phone!==''): ?>
                    <span dir="ltr"><?php echo esc_html($phone); ?></span>
                <?php else: ?>
                    <span>—</span>
                <?php endif; ?>
            </span>

            <span class="bp-inbox-message__contact">
                <span class="bp-inbox-message__label">واتس‌اپ</span>
                <?php $whatsapp_url=bluprint_contact_whatsapp_url($phone,''); ?>
                <?php if($whatsapp_url!==''): ?>
                    <a href="<?php echo esc_url($whatsapp_url); ?>" target="_blank" rel="noopener noreferrer">شروع گفتگو</a>
                <?php else: ?>
                    <span>—</span>
                <?php endif; ?>
            </span>

            <?php if($source): ?>
                <span class="bp-inbox-message__contact">
                    <span class="bp-inbox-message__label">صفحه مبدأ</span>
                    <a href="<?php echo esc_url(get_permalink($source)); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html(get_the_title($source)); ?></a>
                </span>
            <?php endif; ?>

            <?php if($ip!==''): ?>
                <span class="bp-inbox-message__contact">
                    <span class="bp-inbox-message__label">IP</span>
                    <span dir="ltr"><?php echo esc_html($ip); ?></span>
                </span>
            <?php endif; ?>
        </div>

        <div class="bp-inbox-message__body"><?php echo nl2br(esc_html($message)); ?></div>

        <div class="bp-inbox-message__actions">
            <a class="button" href="<?php echo esc_url(bluprint_contact_inbox_toggle_url($post->ID,$read?'unread':'read')); ?>">
                <?php echo $read?'علامت‌گذاری خوانده‌نشده':'علامت‌گذاری خوانده‌شده'; ?>
            </a>
            <?php if(current_user_can('delete_post',$post->ID)): ?>
                <a class="button button-link-delete" href="<?php echo esc_url(get_delete_post_link($post->ID)); ?>">حذف پیام</a>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

/**
 * Message entries are not editable content; only the sender label is shown.
 */
function bluprint_contact_inbox_hide_editor(){
    $screen=get_current_screen();
    if(!$screen||$screen->post_type!=='bp_message')return;
    remove_post_type_support('bp_message','editor');
}
add_action('admin_head','bluprint_contact_inbox_hide_editor');

/**
 * The unread view: ?bp_unread=1 on the message list.
 */
function bluprint_contact_inbox_filter_unread($query){
    if(!is_admin()||!$query->is_main_query())return;
    if($query->get('post_type')!=='bp_message')return;
    if(!isset($_GET['bp_unread'])||sanitize_key(wp_unslash($_GET['bp_unread']))!=='1')return;

    $existing=(array)$query->get('meta_query');
    $existing[]=[
        'relation'=>'OR',
        [
            'key'=>'_bp_message_read',
            'value'=>'1',
            'compare'=>'!=',
        ],
        [
            'key'=>'_bp_message_read',
            'compare'=>'NOT EXISTS',
        ],
    ];
    $query->set('meta_query',$existing);
}
add_action('pre_get_posts','bluprint_contact_inbox_filter_unread');

function bluprint_contact_inbox_views($views){
    $unread=bluprint_contact_unread_count();
    $current=isset($_GET['bp_unread'])&&sanitize_key(wp_unslash($_GET['bp_unread']))==='1';

    $url=add_query_arg([
        'post_type'=>'bp_message',
        'bp_unread'=>1,
    ],admin_url('edit.php'));

    $views['bp_unread']=sprintf(
        '<a href="%s"%s>%s <span class="count">(%s)</span></a>',
        esc_url($url),
        $current?' class="current" aria-current="page"':'',
        'خوانده‌نشده',
        number_format_i18n($unread)
    );

    return $views;
}
add_filter('views_edit-bp_message','bluprint_contact_inbox_views');