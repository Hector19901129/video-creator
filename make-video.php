<?php

namespace Emojione;
// include the PHP library (if not autoloaded)
require('vendor/emojione/emojione/lib/php/autoload.php');

require 'general.php';

$folder = "./".$user_id.'/temp';

//Get a list of all of the file names in the folder.
$files = glob($folder . '/*');
 
//Loop through the file list.
foreach($files as $file){
    //Make sure that this is a file and not a directory.
    if(is_file($file)){
        //Use the unlink function to delete the file.
        unlink($file);
    }
}

function escape_text($text)
{
    $text = str_replace('"', '\"', $text);
    $text = str_replace("\\\\", "\\\\\\\\\\\\\\\\", $text);
    $text = str_replace("'", "'\\\\\\\\\\\\\\''", $text);
    $text = str_replace("%", "\\\\\%", $text);
    $text = str_replace(".", "\\\\.", $text);
    $text = str_replace(",", "\\\\,", $text);
    $text = str_replace(":", "\\\\:", $text);
    return $text;
}

function get_zoompan_filter($animation, $seconds, $index, $result, $image_vid)
{
    if($result === false){
        $pp = 'on';
        $pm = "11*${seconds}-on-1";

        $filter = "zoompan=fps=11:d=11*${seconds}:s=700x700:";
        if ('pan-down-and-right' == $animation) {
            $filter .= "z=iw/(iw-11*${seconds}):x=${pp}:y=${pp}".$image_vid;
        } elseif ('pan-down-and-left' == $animation) {
            $filter .= "z=iw/(iw-11*${seconds}):x=${pm}:y=${pp}".$image_vid;
        } elseif ('pan-up-and-right' == $animation) {
            $filter .= "z=iw/(iw-11*${seconds}):x=${pp}:y=${pm}".$image_vid;
        } elseif ('pan-up-and-left' == $animation) {
            $filter .= "z=iw/(iw-11*${seconds}):x=${pm}:y=${pm}".$image_vid;
        } else {
            $filter .= 'z=(iw+trunc(on/2)*4)/iw:x=trunc(on/2)*2:y=trunc(on/2)*2'.$image_vid;
        }

        return $filter;
    }
    else {
        $pp = 'on';
        $pm = "11*${seconds}-on-1";

        $filter = "zoompan=fps=11:d=11*${seconds}:s=700x700:";
        if ('pan-down-and-right' == $animation) {
            $filter .= "z=iw/(iw-11*${seconds}):x=${pp}:y=${pp}[zoompan".$index."]";
        } elseif ('pan-down-and-left' == $animation) {
            $filter .= "z=iw/(iw-11*${seconds}):x=${pm}:y=${pp}[zoompan".$index."]";
        } elseif ('pan-up-and-right' == $animation) {
            $filter .= "z=iw/(iw-11*${seconds}):x=${pp}:y=${pm}[zoompan".$index."]";
        } elseif ('pan-up-and-left' == $animation) {
            $filter .= "z=iw/(iw-11*${seconds}):x=${pm}:y=${pm}[zoompan".$index."]";
        } else {
            $filter .= 'z=(iw+trunc(on/2)*4)/iw:x=trunc(on/2)*2:y=trunc(on/2)*2[zoompan'.$index.']';
        }

        return $filter;
    }
}

function get_text_filter($text, $index_of_input, $i)
{
    return 
    "[".$index_of_input.":v]format=argb,colorchannelmixer=aa=0.8[zork".$i."],[zoompan".$i."][zork".$i."]overlay=-0:540";
}

function get_top_bar_text_filter($text, $index, $bg_color)
{
    if ($bg_color == 1) {
        $color = "black";
    } else {
        $color = "white";
    }
        return
        'drawbox=w=in_w:h=80:c='. $color .':t=fill[top],'.
        "[top][".$index.":v]overlay=(main_w-overlay_w)/2:(80-overlay_h)/2";
}

function get_bottom_bar_text_filter($text, $index, $bg_color)
{
    if ($bg_color == 1) {
        $color = "black";
    } else {
        $color = "white";
    }
        return
        'drawbox=y=in_h-80:w=in_w:h=80:c='. $color .':t=fill[bottom],'.
        "[bottom][".$index.":v]overlay=(main_w-overlay_w)/2:main_h-80+(80-overlay_h)/2";
}

function set_name_to_last($vid_name)
{
    global $filters;
    $filters[count($filters) - 1] .= $vid_name;
}

function remove_name_to_last($len)
{
    global $filters;
    $filters[count($filters) - 1] = substr($filters[count($filters) - 1], 0, strlen($filters[count($filters) - 1]) - $len);
}

function merge_videos_with_slide_effect($vid1, $len1, $vid2, $len2, $i)
{
    global $filters;

    $split1_1 = "[split${i}_1_1]";
    $split1_2 = "[split${i}_1_2]";
    $split2_1 = "[split${i}_2_1]";
    $split2_2 = "[split${i}_2_2]";
    array_push($filters, "${vid1}split${split1_1}${split1_2}");
    array_push($filters, "${vid2}split${split2_1}${split2_2}");

    $aspect_ratio = "72/72";
    $vid1_1 = "[vid${i}_1_1]";
    $vid11_1 = "[vid${i}${i}_1_1]";
    $vid1_2 = "[vid${i}_1_2]";
    $vid2_1 = "[vid${i}_2_1]";
    $vid2_2 = "[vid${i}_2_2]";
    $vid21_2 = "[vid${i}${i}_2_2]";
    $slide_overlay = "[slide_overlay_${i}]";
    $slide_overlay1 = "[slide${i}_overlay_${i}]";
    array_push($filters, "${split1_1}trim=start_frame=0:end_frame=".($len1 - 5). ",setpts=PTS-STARTPTS". $vid1_1);
    array_push($filters, "${split1_2}trim=start_frame=".($len1 - 5 + 1).":end_frame=${len1},setpts=PTS-STARTPTS${vid1_2}");
    array_push($filters, "${split2_1}trim=start_frame=0:end_frame=4,setpts=PTS-STARTPTS${vid2_1}");
    array_push($filters, "${split2_2}trim=start_frame=5:end_frame=${len2},setpts=PTS-STARTPTS${vid2_2}");

    $direction = rand(1,4);
    if($direction == 1)
    {
        array_push($filters, $vid1_2.$vid2_1."overlay=x='W/5*n-W'".$slide_overlay);
    }
    else if($direction == 2)
    {
        array_push($filters, $vid1_2.$vid2_1."overlay=x='W-W/5*n'".$slide_overlay);
    }
    else if($direction == 3)
    {
        array_push($filters, $vid1_2.$vid2_1."overlay=y='H/5*n-H'".$slide_overlay);
    }
    else
    {
        array_push($filters, $vid1_2.$vid2_1."overlay=y='H-H/5*n'".$slide_overlay);
    }
    array_push($filters, $vid1_1."setsar=sar=".$aspect_ratio.$vid11_1);
    array_push($filters, $slide_overlay."setsar=sar=".$aspect_ratio.$slide_overlay1);
    array_push($filters, $vid2_2."setsar=sar=".$aspect_ratio.$vid21_2);
    array_push($filters, $vid11_1.$slide_overlay1.$vid21_2.'concat=n=3');
}
function adjustBottom($text) {
    $rg = "/g|j|p|q|y|@/";
    if ( preg_match($rg, $text) ) {
        return 2.0;
    } else {
        return 4.0;
    }
}
function textToImage($text, $font_size, $bg_rgb_color, $filename){
    $font_file = 'arialbd.ttf'; // This is the path to your font file.
    // Retrieve bounding box:
    $box   = imagettfbbox($font_size, 0, $font_file, $text); 
    if( !$box ) 
        return false; 
    $min_x = min( array($box[0], $box[2], $box[4], $box[6]) ); 
    $max_x = max( array($box[0], $box[2], $box[4], $box[6]) ); 
    $min_y = min( array($box[1], $box[3], $box[5], $box[7]) ); 
    $max_y = max( array($box[1], $box[3], $box[5], $box[7]) ); 
    $image_width  = ( $max_x - $min_x ); 
    $image_height = ( $max_y - $min_y ); 

    
    $image_height += $font_size / 2;
    
    //added 
    $space = 3;
    $image_width += $space;
    // Create image:
    $image = imagecreatetruecolor($image_width, $image_height);

    // Allocate text and background colors (RGB format):
    $text_color = imagecolorallocate($image, 255 - $bg_rgb_color["r"], 255 - $bg_rgb_color["r"], 255 - $bg_rgb_color["r"]);
    $bg_color = imagecolorallocate($image, $bg_rgb_color["r"], $bg_rgb_color["g"], $bg_rgb_color["b"]);

    // Fill image:
    imagefill($image, 0, 0, $bg_color);

    // Fix starting x and y coordinates for the text:
    $x = $space / 2; // Padding of 5 pixels.
    // $y = $image_height - $font_size / 4; // So that the text is vertically centered.
    $y = $image_height - (float)$font_size / adjustBottom($text);
    // Add TrueType text to image:
    imagettftext($image, $font_size, 0, 0, $y, $text_color, $font_file, $text);

    // Generate and send image to browser:
    header('Content-type: image/png');
    imagepng($image, $filename);

    // Destroy image in memory to free-up resources:
    imagedestroy($image);
}
function concatenateTwoImages($filepath1, $filepath2, $bg_rgb_color, $font_size){
    $dest = imagecreatefrompng($filepath1);
    $src = imagecreatefrompng($filepath2);
    
    //newly added for matching font size of emoji
    if(strpos($filepath2, "https://cdn") !== false && $font_size === 42) {
        $src = imagescale($src, 48);
    } else if (strpos($filepath2, "https://cdn") !== false && $font_size === 32){
        $src = imagescale($src, 38);
    }

    $w1 = imagesx($dest);
    $w2 = imagesx($src);
    $h1 = imagesy($dest);
    $h2 = imagesy($src);

    // added
    $space = 0;
    $newWidth = $w1 + $w2 + $space;
    $newHeight = $h1 > $h2 ? $h1 : $h2;
    $newImage = imagecreatetruecolor($newWidth, $newHeight);
    $bg_color = imagecolorallocate($newImage, $bg_rgb_color["r"], $bg_rgb_color["g"], $bg_rgb_color["b"]);
    // $bg_color = imagecolorallocate($newImage, 100,100,100);
    imagefill($newImage, 0, 0, $bg_color);
    
    if($h1 > $h2){
        imagecopyresampled($newImage, $dest, 0, 0, 0, 0, $w1, $h1, $w1, $h1);    
        imagecopyresampled($newImage, $src, $w1 + 0, ($h1 - $h2) / 2, 0, 0, $w2, $h2, $w2, $h2);
    }
    else{
        imagecopyresampled($newImage, $dest, 0, ($h2 - $h1) / 2, 0, 0, $w1, $h1, $w1, $h1);
        imagecopyresampled($newImage, $src, $w1 + 0, 0, 0, 0, $w2, $h2, $w2, $h2);
    } 
    
    header('Content-Type: image/png');
    imagepng($newImage, $filepath1);
}
function textToArray($text){
    $client = new Client(new Ruleset());
 
    $client->ascii = true;

    $src = "";
    $target_array = [];
    if($text !== "") {
        
        $src = $client->toImage($text);

        $arr1 = explode("<img ", $src);
        foreach($arr1 as $item){
            if(strpos($item, "class=\"emojione\"") !== false){
                $arr2 = explode("src=\"", $item);
                $str1 = $arr2[1];
                $arr3 = explode("\"", $str1);
                $url = $arr3[0];
                array_push($target_array, $url);
                $arr4 = explode("/>", $item);
                if(count($arr4) == 0){
                    continue;
                }else{
                    $text1 = $arr4[1];
                    array_push($target_array, $text1);
                }
            }
            else{
                array_push($target_array, $item);
            }
        }
        var_dump($target_array);
        return $target_array;
    }
    return false;
}
function arrayToImage($array, $target_filename, $bg_rgb_color, $font_size, $user_id){
    if($array[0] !== ""){
        if(strpos($array[0], "https://cdn") !== false){
            
            $src = imagecreatefrompng($array[0]);
            $w = imagesx($src);
            $h = imagesy($src);

            $newImage = imagecreatetruecolor($w, $h);
            $bg_color = imagecolorallocate($newImage, $bg_rgb_color["r"], $bg_rgb_color["g"], $bg_rgb_color["b"]);
            imagefill($newImage, 0, 0, $bg_color);
            imagecopyresampled($newImage, $src, 0, 0, 0, 0, $w, $h, $w, $h);

            header('Content-Type: image/png');
            imagepng($newImage, $target_filename);
            imagedestroy($newImage);
            // textToImage("w", $font_size, $bg_rgb_color, $target_filename);
        }
        else{
            textToImage($array[0], $font_size, $bg_rgb_color, $target_filename);
        }
    }
    
    foreach($array as $key => $item){
        // if($key === 0){
        //     textToImage(".", $font_size, setBgColor(1), $target_filename);
        // }
            
        
        if($item !== "" && $key !== 0){
            if(strpos($item, "https://cdn") !== false){
                concatenateTwoImages($target_filename, $item, $bg_rgb_color,$font_size);
            }
            else{
                $temp_file = "./".$user_id."/temp/text.png"; // temporary image step by step
                textToImage($item, $font_size, $bg_rgb_color, $temp_file);
                concatenateTwoImages($target_filename, $temp_file, $bg_rgb_color,$font_size);
            }
        }
    }
    return true;
}

function textToFinalImage($text, $bg_rgb_color, $font_size, $target_filename, $user_id){
    $array = textToArray($text);
    if($array === false){
        return false;
    }

    arrayToImage($array, $target_filename, $bg_rgb_color, $font_size, $user_id);
}
function setBgColor($bg_color) {
    if($bg_color == 1) {
        return array("r" => 0, "g" => 0, "b" => 0);
    } else {
        return array("r" => 255, "g" => 255, "b" => 255);
    }
}
function makePaddingImage($width, $height, $bg_rgb_color, $target_filename){
    $newImage = imagecreatetruecolor($width, $height);
    $bg_color = imagecolorallocate($newImage, $bg_rgb_color["r"], $bg_rgb_color["g"], $bg_rgb_color["b"]);
    imagefill($newImage, 0, 0, $bg_color);
    imagepng($newImage, $target_filename);
}
function makefinalOverlayImage($padding_file, $target_file, $bg_rgb_color){
    $pad = imagecreatefrompng($padding_file);
    $tar = imagecreatefrompng($target_file);
    
    $w1 = imagesx($pad);
    $w2 = imagesx($tar);
    $h1 = imagesy($pad);
    $h2 = imagesy($tar);

    // added
    $space = 0;
    $newWidth = $w1 + $w2 + $w1;
    $newHeight = $h1 > $h2 ? $h1 : $h2;
    $newImage = imagecreatetruecolor($newWidth, $newHeight);
    $bg_color = imagecolorallocate($newImage, $bg_rgb_color["r"], $bg_rgb_color["g"], $bg_rgb_color["b"]);
    // $bg_color = imagecolorallocate($newImage, 100,100,100);
    imagefill($newImage, 0, 0, $bg_color);
    
    if($h1 > $h2){
        imagecopyresampled($newImage, $pad, 0, 0, 0, 0, $w1, $h1, $w1, $h1);    
        imagecopyresampled($newImage, $tar, $w1 + 0, ($h1 - $h2) / 2, 0, 0, $w2, $h2, $w2, $h2);
        imagecopyresampled($newImage, $pad, $w1 + $w2, ($h1 - $h2) / 2, 0, 0, $w1, $h1, $w1, $h1);
    }
    else{
        imagecopyresampled($newImage, $pad, 0, ($h2 - $h1) / 2, 0, 0, $w1, $h1, $w1, $h1);
        imagecopyresampled($newImage, $tar, $w1 + 0, 0, 0, 0, $w2, $h2, $w2, $h2);
        imagecopyresampled($newImage, $pad, $w1 + $w2, ($h2 - $h1) / 2, 0, 0, $w1, $h1, $w1, $h1);
    }
    
    header('Content-Type: image/png');
    imagepng($newImage, $target_file);
    // var_dump($target_file);
    // var_dump($padding_file);
    // exit();
    return true;
}
$param = json_decode($_POST['param']);
if (0 == count($param->images)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Please select at least a image',
    ]);
    die();
}


$inputs = [];
$filters = [];

$slide_len = 0;
$overlay_image_index_array = [];
$overlay_image_index = -1;
$src_image_index_array = [];

$bg_rgb_color = setBgColor($param->select_bg_color);
makePaddingImage(16, 32, setBgColor(1), "./".$user_id."/temp/overlay_padding.png");
var_dump()
for ($i = 0; $i < count($param->images); ++$i) {
    array_push($inputs, '-i "'.dirname(__FILE__).'/'.$param->images[$i]->src.'"');
    $overlay_image_index ++;
    array_push($src_image_index_array, $overlay_image_index);
    
    $result = textToFinalImage($param->images[$i]->overlay_text, array("r" => 0, "g" => 0, "b" => 0), 28, "./".$user_id."/temp/overlay".$i.".png", $user_id);
    if($param->images[$i]->overlay_text !== ''){
        makefinalOverlayImage('./'.$user_id.'/temp/overlay_padding.png', "./".$user_id."/temp/overlay".$i.".png", setBgColor(1));
    }

    if($result !== false){
        
        array_push($inputs, '-i "'.dirname(__FILE__).'/'.$user_id.'/temp/overlay'.$i.'.png"');
        $overlay_image_index ++;
        array_push($overlay_image_index_array, $overlay_image_index);
    }else{
        array_push($overlay_image_index_array, -1);
    }
    
    $image_vid = "[image_vid${i}]";
    if (count($param->images) == 1)
    {
        $image_vid = '';
    }
    else if (0 == $i) {
        $image_vid = '[slide_out_0]';
        $slide_len = 11 * $param->select_per_frame;
    }
    array_push(
        $filters,
        '['.$src_image_index_array[$i].']'.get_zoompan_filter(
            $param->images[$i]->animation,
            $param->select_per_frame,
            $i, $result, $image_vid
    )
    );
    if($result !== false){
        array_push($filters, get_text_filter($param->images[$i]->overlay_text, $overlay_image_index_array[$i], $i).$image_vid);
    }
    

    if ($i > 0) {
        merge_videos_with_slide_effect('[slide_out_'.($i - 1).']', $slide_len, $image_vid, 11 * $param->select_per_frame, $i);
        $slide_len += 11 * $param->select_per_frame - 5;
        if($i<count($param->images)-1)
        {
            set_name_to_last("[slide_out_{$i}]");
        }
    }
}

if ('' != $param->top_bar_text) {
    // $bg_rgb_color = setBgColor($param->select_bg_color);
    $result = textToFinalImage($param->top_bar_text, $bg_rgb_color, 32, "./".$user_id."/temp/top_bar.png", $user_id);
    array_push($inputs, '-i "'.dirname(__FILE__).'/'.$user_id.'/temp/top_bar.png"');
    $overlay_image_index ++;
    array_push($filters, get_top_bar_text_filter($param->top_bar_text, $overlay_image_index, $param->select_bg_color));
}
if ('' != $param->bottom_bar_text) {
    // $bg_rgb_color = setBgColor($param->select_bg_color);
    $result = textToFinalImage($param->bottom_bar_text, $bg_rgb_color, 32, "./".$user_id."/temp/bottom_bar.png", $user_id);
    array_push($inputs, '-i "'.dirname(__FILE__).'/'.$user_id.'/temp/bottom_bar.png"');
    $overlay_image_index ++;
    array_push($filters, get_bottom_bar_text_filter($param->bottom_bar_text, $overlay_image_index, $param->select_bg_color));
}
// should modify
if ('' != $param->end_screen_text || $param->your_brand_name) {
    set_name_to_last('[total_images_vid]');
    if ($param->select_bg_color == 1)
        array_push($filters, 'color=c=0x101010:s=700x700:d=5:sar=72/72[end]');
    else 
        array_push($filters, 'color=c=0xffffff:s=700x700:d=5:sar=72/72[end]');
    if ('' != $param->end_screen_text) {
        //$param->end_screen_text = escape_text($param->end_screen_text);
        // $bg_rgb_color = setBgColor($param->select_bg_color);
        $result = textToFinalImage($param->end_screen_text, $bg_rgb_color, 42, "./".$user_id."/temp/end.png", $user_id);
        array_push($inputs, '-i "'.dirname(__FILE__).'/'.$user_id.'/temp/end.png"');
        $overlay_image_index ++;
        array_push($filters, "[end][".$overlay_image_index.":v]overlay=(main_w-overlay_w)/2:main_h/2-overlay_h-28");
        if ('' != $param->your_brand_name) {
            set_name_to_last('[brand]');
            //var_dump($param->your_brand_name);
            //$param->your_brand_name = escape_text($param->your_brand_name);
            //var_dump($param->your_brand_name);
            // $bg_rgb_color = array("r" => 0, "g" => 0, "b" => 0);
            $result = textToFinalImage($param->your_brand_name, $bg_rgb_color, 32, "./".$user_id."/temp/brand.png", $user_id);
            array_push($inputs, '-i "'.dirname(__FILE__).'/'.$user_id.'/temp/brand.png"');
            $overlay_image_index ++;
            array_push($filters, "[brand][".$overlay_image_index.":v]overlay=(main_w-overlay_w)/2:main_h/2");
            set_name_to_last('[credit_vid]');
            array_push($filters, '[credit_vid]setsar=sar=72/72[credit1_vid]');
            array_push($filters, '[total_images_vid]setsar=sar=72/72[total1_images_vid]');
            array_push($filters, '[total1_images_vid][credit1_vid]concat=n=2');
        }
        else{
            set_name_to_last('[credit_vid]');
            array_push($filters, '[credit_vid]setsar=sar=72/72[credit1_vid]');
            array_push($filters, '[total_images_vid]setsar=sar=72/72[total1_images_vid]');
            array_push($filters, '[total1_images_vid][credit1_vid]concat=n=2');
        }
        
    }
    else{
        if ('' != $param->your_brand_name) {
            //$param->your_brand_name = escape_text($param->your_brand_name);
            // $bg_rgb_color = array("r" => 0, "g" => 0, "b" => 0);
            $result = textToFinalImage($param->your_brand_name, $bg_rgb_color, 32, "./".$user_id."/temp/brand.png", $user_id);
            array_push($inputs, '-i "'.dirname(__FILE__).'/'.$user_id.'/temp/brand.png"');
            $overlay_image_index ++;
            array_push($filters, "[end][".$overlay_image_index.":v]overlay=(main_w-overlay_w)/2:main_h/2");
            set_name_to_last('[credit_vid]');
            array_push($filters, '[credit_vid]setsar=sar=72/72[credit1_vid]');
            array_push($filters, '[total_images_vid]setsar=sar=72/72[total1_images_vid]');
            array_push($filters, '[total1_images_vid][credit1_vid]concat=n=2');
        }
    
        
    }
    
}

set_name_to_last('[total_vid]');

$maps = '-map [total_vid]';
$filter_string = implode(',', $filters);

if ('Select audio file' != $param->select_sound) {
    array_push($inputs, '-i "'.dirname(__FILE__).'/audio/'.$param->select_sound.'"');
    $maps .= ' -map '.($overlay_image_index + 1);
}


$cmd = 'ffmpeg -y '.implode(' ', $inputs).' -filter_complex "'.$filter_string.'" '.$maps." -shortest -strict -2 ${user_id}/output_video.mp4 2>&1";
echo $cmd;
echo shell_exec($cmd);
echo "\r";
echo $cmd;

die();
