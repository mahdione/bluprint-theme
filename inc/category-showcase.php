<?php
defined('ABSPATH')||exit;

function bluprint_category_showcase_sanitize($value){
    $clean=[];
    $used=[];
    for($i=0;$i<4;$i++){
        $id=isset($value[$i])?absint($value[$i]):0;
        if($id&&!isset($used[$id])&&term_exists($id,'product_cat')){
            $clean[$i]=$id;
            $used[$id]=true;
        }else{
            $clean[$i]=0;
        }
    }
    return $clean;
}

function bluprint_category_showcase_register(){
    register_setting('bluprint_category_showcase_group','bluprint_category_showcase',[
        'type'=>'array',
        'sanitize_callback'=>'bluprint_category_showcase_sanitize',
        'default'=>[0,0,0,0]
    ]);
    add_submenu_page(
        'bluprint-settings',
        'دسته‌بندی‌های صفحه اصلی',
        'دسته‌بندی‌های صفحه اصلی',
        'manage_options',
        'bluprint-category-showcase',
        'bluprint_category_showcase_admin'
    );
}
add_action('admin_menu','bluprint_category_showcase_register');

function bluprint_category_showcase_admin(){
    if(!current_user_can('manage_options'))return;
    $selected=array_pad((array)get_option('bluprint_category_showcase',[0,0,0,0]),4,0);
    $terms=bluprint_get_ordered_terms('product_cat',['hide_empty'=>false]);
    ?>
    <div class="wrap">
        <h1>دسته‌بندی‌های صفحه اصلی</h1>
        <p>برای هر جایگاه یک دسته‌بندی انتخاب کن. ترتیب جایگاه‌ها، ترتیب نمایش کارت‌ها را تعیین می‌کند.</p>
        <form method="post" action="options.php">
            <?php settings_fields('bluprint_category_showcase_group'); ?>
            <table class="form-table">
                <?php for($i=0;$i<4;$i++): ?>
                    <tr>
                        <th scope="row">
                            <label for="bp-category-<?php echo $i; ?>">جایگاه <?php echo sprintf('%02d',$i+1); ?></label>
                        </th>
                        <td>
                            <select id="bp-category-<?php echo $i; ?>" name="bluprint_category_showcase[<?php echo $i; ?>]" class="regular-text">
                                <option value="0">— انتخاب دسته‌بندی —</option>
                                <?php foreach($terms as $term): ?>
                                    <option value="<?php echo esc_attr($term->term_id); ?>" <?php selected((int)$selected[$i],(int)$term->term_id); ?>>
                                        <?php echo esc_html($term->name); ?> (<?php echo number_format_i18n($term->count); ?> محصول)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                <?php endfor; ?>
            </table>
            <?php submit_button('ذخیره دسته‌بندی‌ها'); ?>
        </form>
    </div>
    <?php
}

function bluprint_get_category_showcase_terms(){
    $ids=get_option('bluprint_category_showcase',[0,0,0,0]);
    $terms=[];
    foreach((array)$ids as $id){
        $id=absint($id);
        if(!$id)continue;
        $term=get_term($id,'product_cat');
        if(!$term||is_wp_error($term))continue;
        $terms[]=$term;
    }
    return $terms;
}