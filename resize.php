<?php
require '../../conf/cfg.php';

$file_XY = explode('x',$_GET['size']);
$target_width = $file_XY[0];
$target_height = $file_XY[1];
$file_original = $data_dir . '/' . $_GET['file'];

# Check for resize request 
checkForValidResize($_GET['size']);

if (!file_exists($file_original)){
	header('HTTP/1.0 404 Not Found');
	echo "<h1>404 Not Found</h1>";
	echo "The image file that you have requested could not be found.";
	exit();     
}

# Resize image
$new_width = $target_width;
$new_height = $target_height;

$image = new Imagick($file_original);
list($orig_width, $orig_height, $type, $attr) = getimagesize($file_original);
 
# Preserve aspect ratio, fitting image to specified box
$new_height = $orig_height * $new_width / $orig_width;
if ($new_height > $target_height){
    $new_width = $orig_width * $target_height / $orig_height;
    $new_height = $target_height;
}
# Resize to specified box
$image->resizeImage($new_width, $new_height, imagick::FILTER_LANCZOS, 1);

# Add button if requested 
if(!empty($_GET['button_image'])){
	if (file_exists($button_dir . '/' . $_GET['button_image'])){
		$button_file = $_GET['button_image'];
	}else{
		$button_file = $default_button;
	}

	# Set cache path
	$file_cached=$cache_dir . '/' . $_GET['file'] . '/' . $_GET['size'] . '/' . $_GET['button_image'] . '/' . $_GET['file'] ;
	$button_image = new Imagick($button_dir . '/' . $_GET['button_image']);
	$button_image->compositeImage($image, imagick::COMPOSITE_DEFAULT, 0, 0 );
	$image_out = $button_image;
}else{
	# Set cache path
	$file_cached=$cache_dir . '/' . $_GET['file'] . '/' . $_GET['size'] . '/' . $_GET['file'] ;
	$image_out = $image;
}

# Cache and return image
# Check if new directory would need to be created
createFolderForFile($file_cached);

# Save and return the resized image file
$image_out->writeImage($file_cached);
$image_format = strtolower ($image_out->getImageFormat()); 
header('Content-Type: image/' . $image_format);
echo $image_out;
