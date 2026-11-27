<?php
defined('ABSPATH')||exit;

function bluprint_asset_version($file){
    $path=get_theme_file_path($file);
    return file_exists($path)?(string)filemtime($path):wp_get_theme()->get('Version');
}

/**
 * Pick a readable text colour for a pack colour.
 *
 * Packs can use any hex colour, so the card button and category chip need a
 * contrast colour that follows WCAG instead of a fixed white/dark choice.
 * The brand ink is preferred when it reaches 4.5:1; otherwise the stronger of
 * white and near-black is returned.
 */
function bluprint_pack_contrast_color($hex){
    $hex=ltrim((string)$hex,'#');

    if(strlen($hex)===3){
        $hex=$hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    }

    if(!preg_match('/^[0-9a-f]{6}$/i',$hex))return '#073294';

    $luminance=static function($hex){
        $channels=[
            hexdec(substr($hex,0,2))/255,
            hexdec(substr($hex,2,2))/255,
            hexdec(substr($hex,4,2))/255,
        ];

        foreach($channels as &$channel){
            $channel=$channel<=0.03928?$channel/12.92:pow(($channel+0.055)/1.055,2.4);
        }

        return 0.2126*$channels[0]+0.7152*$channels[1]+0.0722*$channels[2];
    };

    $ratio=static function($a,$b){
        $light=max($a,$b);
        $dark=min($a,$b);
        return ($light+0.05)/($dark+0.05);
    };

    $background=$luminance($hex);
    $ink='#073294';

    if($ratio($background,$luminance(ltrim($ink,'#')))>=4.5)return $ink;

    $white='#FFFFFF';
    $black='#0B1220';

    return $ratio($background,$luminance(ltrim($white,'#')))>=$ratio($background,$luminance(ltrim($black,'#')))
        ?$white
        :$black;
}
