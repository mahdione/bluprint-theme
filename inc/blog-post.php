<?php
defined('ABSPATH')||exit;

/**
 * Add an excerpt-based Rank Math description when a post has no custom one.
 */
function bluprint_single_post_meta_description($description){
    if(!is_singular('post')||trim((string)$description)!=='')return $description;

    $post_id=absint(get_queried_object_id());
    if(!$post_id)return $description;

    $text=get_the_excerpt($post_id);
    if(trim((string)$text)===''){
        $text=wp_strip_all_tags(strip_shortcodes(get_post_field('post_content',$post_id)));
    }

    $text=trim(wp_strip_all_tags($text));

    return $text!==''?wp_trim_words($text,32,'…'):$description;
}
add_filter('rank_math/frontend/description','bluprint_single_post_meta_description');

/**
 * Add stable anchor IDs to article headings and return a matching TOC model.
 *
 * The generated IDs are based on each heading's position when an author has
 * not already supplied one, so Persian headings remain short and URL-safe.
 */
function bluprint_post_content_toc($content){
    $items=[];
    $reserved_ids=[];
    $used_heading_ids=[];

    if(preg_match_all('/\bid=(["\'])(.*?)\1/is',$content,$matches)){
        foreach($matches[2] as $id){
            $reserved_ids[]=(string)$id;
        }
    }

    $sequence=0;
    $content=preg_replace_callback(
        '/<h([2-4])\b([^>]*)>(.*?)<\/h\1\s*>/is',
        function($heading)use(&$items,&$reserved_ids,&$used_heading_ids,&$sequence){
            $sequence++;
            $level=(int)$heading[1];
            $attributes=$heading[2];
            $inner=$heading[3];
            $text=html_entity_decode(
                trim(wp_strip_all_tags($inner)),
                ENT_QUOTES|ENT_HTML5,
                get_bloginfo('charset')?:'UTF-8'
            );

            if($text==='')return $heading[0];

            $has_id=preg_match('/\bid=(["\'])(.*?)\1/is',$attributes,$id_match);
            $id=$has_id?trim((string)$id_match[2]):'';

            if($id===''||isset($used_heading_ids[$id])){
                $base=sanitize_title($text);
                $base=preg_replace('/[^a-z0-9_-]+/i','-',$base);
                $base=trim((string)$base,'-_');
                if($base==='')$base='section-'.$sequence;

                $id=$base;
                $suffix=2;
                while(in_array($id,$reserved_ids,true)||isset($used_heading_ids[$id])){
                    $id=$base.'-'.$suffix;
                    $suffix++;
                }

                if($has_id){
                    $attributes=preg_replace(
                        '/\bid=(["\']).*?\1/is',
                        'id="'.esc_attr($id).'"',
                        $attributes,
                        1
                    );
                }else{
                    $attributes.=' id="'.esc_attr($id).'"';
                }
            }

            $used_heading_ids[$id]=true;
            $reserved_ids[]=$id;
            $items[]=[
                'id'=>$id,
                'title'=>$text,
                'level'=>$level,
            ];

            return '<h'.$level.$attributes.'>'.$inner.'</h'.$level.'>';
        },
        $content
    );

    return [
        'content'=>$content,
        'items'=>$items,
    ];
}