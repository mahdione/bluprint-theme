<?php
defined('ABSPATH')||exit;

/**
 * Capability used for the top-level Bluprint menu.
 *
 * Picks the highest capability the current user actually has so the menu stays
 * visible to administrators, shop managers and theme editors alike.
 */
function bluprint_admin_menu_capability(){
    foreach(['manage_options','manage_woocommerce','edit_theme_options'] as $cap){
        if(current_user_can($cap))return $cap;
    }
    return 'manage_options';
}

/**
 * Whether the current admin request is for the given settings page slug.
 */
function bluprint_is_admin_page($page){
    if(!is_admin()||!isset($_GET['page'])||!is_string($_GET['page']))return false;
    return sanitize_key(wp_unslash($_GET['page']))===$page;
}

/**
 * Register the single top-level Bluprint settings menu.
 *
 * Runs before the individual settings pages (priority 9) so every page can be
 * attached to it as a submenu.
 */
function bluprint_register_admin_menu(){
    $cap=bluprint_admin_menu_capability();
    add_menu_page(
        'تنظیمات بلوپرینت',
        'بلوپرینت',
        $cap,
        'bluprint-settings',
        'bluprint_admin_overview_page',
        'dashicons-admin-customizer',
        58
    );
    add_submenu_page(
        'bluprint-settings',
        'تنظیمات کلی',
        'تنظیمات کلی',
        $cap,
        'bluprint-settings',
        'bluprint_admin_overview_page'
    );
}
add_action('admin_menu','bluprint_register_admin_menu',9);

/**
 * Landing page for the Bluprint settings menu.
 *
 * Links to every settings screen the current user is allowed to manage.
 */
function bluprint_admin_overview_page(){
    if(!current_user_can(bluprint_admin_menu_capability()))return;
    $pages=[
        ['slug'=>'bluprint-category-showcase','title'=>'دسته‌بندی‌های صفحه اصلی','description'=>'انتخاب چهار دسته‌بندی محصول برای کارت‌های صفحه اصلی.','capability'=>'manage_options','icon'=>'dashicons-grid-view'],
        ['slug'=>'bluprint-editorial','title'=>'سکشن معرفی','description'=>'تنظیم متن، تصویر و دکمه سکشن معرفی صفحه اصلی.','capability'=>'manage_options','icon'=>'dashicons-format-aside'],
        ['slug'=>'bluprint-product-showcase','title'=>'محصولات صفحه اصلی','description'=>'انتخاب منبع و ترتیب نمایش محصولات صفحه اصلی.','capability'=>'manage_options','icon'=>'dashicons-products'],
        ['slug'=>'bluprint-banners','title'=>'بنرها','description'=>'مدیریت تصویر، رنگ و متن بنر صفحه اصلی.','capability'=>'edit_theme_options','icon'=>'dashicons-format-image'],
        ['slug'=>'bluprint-blog','title'=>'سکشن بلاگ','description'=>'انتخاب نوشته‌های نمایش‌داده‌شده در سکشن بلاگ.','capability'=>'edit_theme_options','icon'=>'dashicons-admin-post'],
        ['slug'=>'bluprint-footer','title'=>'تنظیمات فوتر','description'=>'تنظیم عنوان، توضیحات، منوها و تصویر فوتر.','capability'=>'edit_theme_options','icon'=>'dashicons-align-center'],
        ['slug'=>'bluprint-faq-settings','title'=>'سؤالات متداول','description'=>'فعال‌سازی سکشن و تنظیم عنوان سؤالات متداول.','capability'=>'edit_theme_options','icon'=>'dashicons-editor-help'],
        ['slug'=>'bluprint-term-order','title'=>'ترتیب دسته‌بندی‌ها','description'=>'مرتب‌سازی دسته‌بندی‌ها، برچسب‌ها و برندها.','capability'=>'manage_woocommerce','icon'=>'dashicons-sort'],
        ['slug'=>'bluprint-shop-archive','title'=>'آرشیو فروشگاه','description'=>'تنظیم عنوان، توضیحات و تصاویر آرشیو فروشگاه.','capability'=>'manage_woocommerce','icon'=>'dashicons-store'],
        ['slug'=>'bluprint-theme-updates','title'=>'به‌روزرسانی قالب','description'=>'بررسی و نصب نسخه‌های جدید قالب از GitHub.','capability'=>'manage_options','icon'=>'dashicons-update'],
        ['slug'=>'bluprint-enamad','title'=>'اینماد','description'=>'نمایش نماد اعتماد الکترونیکی در فوتر.','capability'=>'manage_options','icon'=>'dashicons-shield'],
    ];
    ?>
    <div class="wrap">
        <h1>تنظیمات بلوپرینت</h1>
        <p>همه تنظیمات قالب بلوپرینت در یک جا. برای ویرایش هر بخش، روی کارت آن کلیک کن.</p>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px;margin-top:20px;max-width:1100px">
            <?php foreach($pages as $page): ?>
                <?php if(!current_user_can($page['capability']))continue; ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page='.$page['slug'])); ?>" style="display:block;padding:18px 20px;text-decoration:none;color:inherit;border:1px solid #dcdcde;border-radius:6px;background:#fff;box-shadow:0 1px 2px rgba(0,0,0,.08)">
                    <span class="dashicons <?php echo esc_attr($page['icon']); ?>" style="font-size:28px;width:28px;height:28px;color:#093FB9;margin-bottom:10px"></span>
                    <strong style="display:block;font-size:15px;margin-bottom:6px"><?php echo esc_html($page['title']); ?></strong>
                    <span style="display:block;color:#646970;line-height:1.6"><?php echo esc_html($page['description']); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}