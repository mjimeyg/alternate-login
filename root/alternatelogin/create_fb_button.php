<?php
putenv('GDFONTPATH=' . realpath('.'));

if(isset($_GET['font']))
{
	$font = urldecode($_GET['font']);
}
else
{
	$font = 'tahomabd.ttf';
}

if(isset($_GET['label']))
{
	$label = urldecode($_GET['label']);
}
else
{
	$label = "Login with Facebook";
}

$font_size = 8;

$image_base_width = 28;
$image_end_padding = 8;

$image_top_padding = 7;
$image_bottom_padding = 6;

$max_label_height = 22 - ($image_top_padding + $image_bottom_padding);

//$font_id = imageloadfont($font);
$font_bb = imagettfbbox($font_size, 0, $font, $label);
//print_r($font_bb);
while($font_bb[1] > $max_label_height)
{
	$font_size--;
	$font_bb = imagettfbbox($font_size, 0, $font, $label);
}

$label_length = $font_bb[4];

$image_width = $image_base_width + $label_length + $image_end_padding;
//echo $image_width;
$fb_button = imagecreate($image_width, 22);
$background = imagecolorallocate($fb_button, 107, 122, 174);
$text_colour = imagecolorallocate($fb_button, 255, 255, 255);
$border_colour = imagecolorallocate($fb_button, 50, 67, 127);
$separator_colour = imagecolorallocate($fb_button, 78, 94, 149);
$separator_top_colour = imagecolorallocate($fb_button, 98, 113, 160);
$lower_left_highlight = imagecolorallocate($fb_button, 129, 142, 186);
$upper_highlight = imagecolorallocate($fb_button, 141, 153, 193);


imagettfText($fb_button, $font_size, 0, 29, 15, $text_colour, $font, $label);

imagesetthickness($fb_button, 1);

imageline($fb_button, 0, 0, $image_width - 1, 0, $border_colour); // Top Line
imageline($fb_button, $image_width - 1, 0, $image_width - 1, 21, $border_colour); // Right Line
imageline($fb_button, $image_width - 1, 21, 0, 21, $border_colour); // Bottom Line
imageline($fb_button, 0, 0, 0, 21, $border_colour); // Left Line

imageline($fb_button, 1, 1, $image_width - 1, 1, $upper_highlight); // Upper highlight

imageline($fb_button, 21, 0, 21, 21, $separator_colour); // Icon/Label separator
imageline($fb_button, 21, 1, 21, 1, $separator_top_colour); // Icon/Label separator top pixel



imagefilledrectangle($fb_button, 2, 17, 20, 20, $lower_left_highlight);

// 'F' logo
$c151_162_199 = imagecolorallocate($fb_button, 151, 162, 199);
$c224_228_238 = imagecolorallocate($fb_button, 224, 228, 238);
$c246_247_250 = imagecolorallocate($fb_button, 246, 247, 250);
$c224_228_238 = imagecolorallocate($fb_button, 224, 228, 238);
$c225_229_239 = imagecolorallocate($fb_button, 225, 229, 239);
$c222_226_237 = imagecolorallocate($fb_button, 222, 226, 237);
$c228_231_240 = imagecolorallocate($fb_button, 228, 231, 240);

// Line 4
imageline($fb_button, 13, 4, 13, 4, $c151_162_199);
//imageline($fb_button, 14, 4, 14, 4, $c151_162_199);
imageline($fb_button, 14, 4, 15, 4, $text_colour);
imageline($fb_button, 16, 4, 16, 4, $c246_247_250);
imageline($fb_button, 17, 4, 17, 4, $c225_229_239);

// Line 5
imageline($fb_button, 12, 5, 12, 5, $c151_162_199);
imageline($fb_button, 13, 5, 17, 5, $text_colour);

// Line 6
imageline($fb_button, 12, 6, 12, 6, $c224_228_238);
imageline($fb_button, 13, 6, 14, 6, $c225_229_239);
imageline($fb_button, 15, 6, 17, 6, $c224_228_238);

// Line 7
imageline($fb_button, 12, 7, 13, 7, $text_colour);
imageline($fb_button, 14, 7, 14, 7, $c246_247_250);

// Line 8
imageline($fb_button, 12, 8, 13, 8, $text_colour);
imageline($fb_button, 14, 8, 14, 8, $c224_228_238);


// Line 9
imageline($fb_button, 12, 9, 13, 9, $text_colour);
imageline($fb_button, 14, 9, 14, 9, $c224_228_238);


// Line 10
imageline($fb_button, 9, 10, 17, 10, $text_colour);


// Line 11
imageline($fb_button, 9, 11, 16, 11, $text_colour);
imageline($fb_button, 16, 11, 17, 11, $c224_228_238);


// Line 12
imageline($fb_button, 9, 12, 11, 12, $c222_226_237);
imageline($fb_button, 12, 12, 13, 12, $text_colour);
imageline($fb_button, 14, 12, 16, 12, $c222_226_237);
imageline($fb_button, 17, 12, 17, 12, $c224_228_238);

// Line 13
imageline($fb_button, 12, 13, 13, 13, $text_colour);
imageline($fb_button, 14, 13, 14, 13, $c222_226_237);

// Line 14
imageline($fb_button, 12, 14, 13, 14, $text_colour);
imageline($fb_button, 14, 14, 14, 14, $c222_226_237);

// Line 15
imageline($fb_button, 12, 15, 13, 15, $text_colour);
imageline($fb_button, 14, 15, 14, 15, $c222_226_237);

// Line 16
imageline($fb_button, 12, 16, 13, 16, $text_colour);
imageline($fb_button, 14, 16, 14, 16, $c222_226_237);

// Line 17
imageline($fb_button, 12, 17, 13, 17, $text_colour);
imageline($fb_button, 14, 17, 14, 17, $c228_231_240);

// Line 18
imageline($fb_button, 12, 18, 13, 18, $text_colour);
imageline($fb_button, 14, 18, 14, 18, $c228_231_240);
// Line 19
imageline($fb_button, 12, 19, 13, 19, $text_colour);
imageline($fb_button, 14, 19, 14, 19, $c228_231_240);
// Line 20
imageline($fb_button, 12, 20, 13, 20, $text_colour);
imageline($fb_button, 14, 20, 14, 20, $c228_231_240);

header("Content-type: image/png");

imagepng($fb_button);

imagecolordeallocate($line_colour);
imagecolordeallocate($text_colour);
imagecolordeallocate($background);
imagecolordeallocate($separator_colour);
imagecolordeallocate($border_colour);
imagecolordeallocate($lower_left_highlight);
imagecolordeallocate($upper_highlight);
imagecolordeallocate($c151_162_199);
imagecolordeallocate($c224_228_238);
imagecolordeallocate($c246_247_250);
imagecolordeallocate($c224_228_238);
imagecolordeallocate($c225_229_239);
imagecolordeallocate($c222_226_237);
imagecolordeallocate($c228_231_240);

imagedestroy($fb_button);

?>