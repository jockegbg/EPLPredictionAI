<?php
// create_badge.php

// Set dimensions
$width = 200;
$height = 200;

// Create image resource
$image = imagecreatetruecolor($width, $height);

// Enable alpha blending and save alpha
imagealphablending($image, false);
imagesavealpha($image, true);

// Create transparent color
$transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
imagefilledrectangle($image, 0, 0, $width, $height, $transparent);

// Set drawing color (Light Grey)
$grey = imagecolorallocatealpha($image, 200, 200, 200, 0); // Opaque grey
$border = imagecolorallocatealpha($image, 150, 150, 150, 0); // Darker border

// Draw Shield Shape
// Simplified shield coordinates
$points = [
    20,
    20,    // Top Left
    180,
    20,   // Top Right
    180,
    100,  // Mid Right
    100,
    180,  // Bottom Tip
    20,
    100    // Mid Left
];

// Enable blending for drawing
imagealphablending($image, true);

// Draw filled polygon (using thicker lines by drawing multiple times or just outline)
// Let's just draw a thick outline for "minimalism"
imagesetthickness($image, 8);

// Shield Polygon
imagepolygon($image, $points, 5, $grey);

// Save to public path
$path = 'public/images/teams/default_badge.png';
if (file_exists($path)) {
    unlink($path);
}

imagepng($image, $path);
imagedestroy($image);

echo "Unicorn Badge Generated at $path\n";
