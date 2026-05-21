<?php
session_start();
header('Content-Type: image/png');

$code = substr(str_shuffle("ABCDEFGHJKLMNPQRSTUVWXYZ23456789"), 0, 5);
$_SESSION['captcha_code'] = $code;

$width = 300; // Wider canvas for better spacing
$height = 100;
$image = imagecreatetruecolor($width, $height);

$background = imagecolorallocate($image, 255, 255, 255);
imagefilledrectangle($image, 0, 0, $width, $height, $background);

// Use a system font path - DejaVuSans-Bold is standard on most Linux VMs
$font = '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';

// Fallback check: if the font above doesn't exist, we'll use a simpler one
if (!file_exists($font)) {
    $font = '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf';
}

$text_color = imagecolorallocate($image, 15, 57, 95); // WHOI Blue

// Draw bold, large letters with TTF
for ($i = 0; $i < strlen($code); $i++) {
    $size = rand(28, 32); // Much larger font size
    $angle = rand(-10, 10); // Slight tilt for security
    $x = 40 + ($i * 50);   // Wide spacing
    $y = 65;               // Vertical alignment
    
    imagettftext($image, $size, $angle, $x, $y, $text_color, $font, $code[$i]);
}

// Subtle noise that doesn't block the letters
for($i=0; $i<4; $i++) {
    $line_color = imagecolorallocate($image, 220, 220, 220);
    imagesetthickness($image, 2);
    imageline($image, 0, rand(0,$height), $width, rand(0,$height), $line_color);
}

imagepng($image);
imagedestroy($image);
?>
