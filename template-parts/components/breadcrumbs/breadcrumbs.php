<?php
defined('ABSPATH')||exit;

$args=wp_parse_args($args??[],[
    'class'=>'',
    'variant'=>'default',
    'label'=>'مسیر راهنما',
    'hide_home'=>false,
    'hide_front_page'=>true,
]);

if($args['hide_front_page']&&is_front_page())return;

$variant=sanitize_html_class($args['variant']);
$classes=['bp-breadcrumb','bp-breadcrumb--'.$variant];

if(!empty($args['class'])){
    foreach(preg_split('/\s+/',trim($args['class'])) as $class){
        $class=sanitize_html_class($class);
        if($class)$classes[]=$class;
    }
}
?>
<nav class="<?php echo esc_attr(implode(' ',array_unique($classes))); ?>" aria-label="<?php echo esc_attr($args['label']); ?>">
    <?php if(function_exists('rank_math_the_breadcrumbs')): ?>
        <?php rank_math_the_breadcrumbs(); ?>
    <?php elseif(function_exists('bluprint_seo_breadcrumb_items')): ?>
        <?php
        $items=bluprint_seo_breadcrumb_items();

        if($args['hide_home']&&$items)array_shift($items);

        if($items):
            $last=count($items)-1;
            ?>
            <div class="rank-math-breadcrumb">
                <p>
                    <?php foreach($items as $index=>$item): ?>
                        <?php if($index>0): ?><span class="separator" aria-hidden="true">/</span><?php endif; ?>
                        <?php if($index===$last): ?>
                            <span class="last" aria-current="page"><?php echo esc_html($item['name']); ?></span>
                        <?php else: ?>
                            <a href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html($item['name']); ?></a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </p>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</nav>
