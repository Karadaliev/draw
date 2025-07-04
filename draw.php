<?php

// draw.php
//
// version 1.0.9
// last update: 2025, January 13 (updated to PHP 8.3.0)

ini_set('memory_limit', '32M');
define('cache_path', 'files/draw.cache/');
define('separate_by_folders', 'yes');

function generateFilename($pathinfo, $imgtype)
{
    $imagename = md5($pathinfo['dirname']) . "_" . $pathinfo['filename']; // пътят става MD5 от целия път + само името
    $dirname = '';
    if (separate_by_folders && separate_by_folders == 'yes') {
        $dirname = substr($imagename, strrpos($imagename, '.') - 5);
    }
    $cachedname = cache_path . $imgtype . '/' . $dirname . '/' . $imagename . '_' . @$_GET['do'] . '_' . (isset($_GET['size']) && $_GET['size'] > 0 ? 's' . $_GET['size'] : 'w' . @$_GET['w'] . '_h' . @$_GET['h']);
    $cachedname .= (isset($_GET['x']) ? '_x' . $_GET['x'] : '');
    $cachedname .= (isset($_GET['y']) ? '_y' . $_GET['y'] : '');
    $cachedname .= (isset($_GET['ratio']) ? '_r' . $_GET['ratio'] : '');
    $cachedname .= '.' . $imgtype;
    return $cachedname;
}

if ((isset($_GET['do'])) and (isset($_GET['path']))) {
    // get filename and path
    if (mb_strpos($_GET['path'], "http://", 0, "UTF-8")) {
        $_GET['path'] = substr($_GET['path'], 7);
        $parts = explode("/", $_GET['path']);
        foreach ($parts as &$value) {
            $value = rawurlencode($value);
        }
        $imgname = "http://" . implode("/", $parts);
    } else {
        $imgname = rawurldecode($_GET['path']);
    }

    // get image info
    $info = @getimagesize($imgname);
    $pathinfo = pathinfo($imgname);
    if (!empty($info)) {
        // the file exists
        $x = $info[0];
        $y = $info[1];
        $imgtype = 'unknown';
        switch ($info[2]) {
            case 1:
                $imgtype = 'gif';
                break;
            case 2:
                $imgtype = 'jpg';
                break;
            case 3:
                $imgtype = 'png';
                break;
            case 6:
                $imgtype = 'bmp';
                break;
            default:
                die("Unsupported format " . $info[2] . "!");
                break;
        }
    } else {
        $imgtype = substr(strrchr($_GET['path'], '.'), 1);
    }
    // sample cached filename -> someimage_resize_w100_h50.jpg
    //$imagename = strtolower(strtr($_GET['path'], array('/' => "_"))); // old
    $cachedname = generateFilename($pathinfo, $imgtype);

    if (file_exists($cachedname)) {
        $image = file_get_contents($cachedname);
    } else {
        switch ($info[2]) {
            case 1:
                $im = @imagecreatefromgif($imgname) or die("<h2>Can't open image file !</h2> ");
                break;
            case 2:
                $im = @imagecreatefromjpeg($imgname) or die("<h2>Can't open image file !</h2> ");
                break;
            case 3:
                $im = @imagecreatefrompng($imgname) or die("<h2>Can't open image file !</h2> ");
                break;
            case 6:
                $im = @imagecreatefrombmp($imgname) or die("<h2>Can't open image file !</h2> ");
                break;
            default:
                die("Unsupported format!");
                break;
        }
        // generating the file path
        if (separate_by_folders && separate_by_folders == 'yes') {
            $imagename = md5($pathinfo['dirname']) . "_" . $pathinfo['filename'];
            $dirname = substr($imagename, strrpos($imagename, '.') - 5);
            @mkdir(cache_path . $imgtype . '/' . $dirname, 0777);
        }
        //select action
        switch ($_GET['do']) {
            case 'zoom':
                //get dimensions
                if (isset($_GET['size'])) {
                    $width = $_GET['size'];
                    $height = $_GET['size'];
                } else {
                    $width = @$_GET['w'];
                    $height = @$_GET['h'];
                }
                //get zoom ratio
                if (isset($_GET['ratio'])) {
                    $ratio = $_GET['ratio'];
                } else {
                    $ratio = 1;
                }
                //get positions
                $xs = isset($_GET['x']) ? $_GET['x'] : 0;
                $ys = isset($_GET['y']) ? $_GET['y'] : 0;
                //generate image
                $img_dest = imagecreatetruecolor($width, $height);
                if ($imgtype != 'jpg') {
                    imagealphablending($img_dest, false);
                    imagesavealpha($img_dest, true);
                }
                $r = imagecopyresampled(
                    $img_dest,
                    $im,
                    -$xs,
                    -$ys,
                    0,
                    0,
                    ($x / $ratio),
                    ($y / $ratio),
                    $x,
                    $y
                ) or die("");
                break;
            case 'resize':
                //get dimensions
                $width = isset($_GET['w']) ? $_GET['w'] : null;
                $height = isset($_GET['h']) ? $_GET['h'] : null;
                //set ratio
                if (!isset($height) || $height == "x") {
                    $ratio = $y / $x;
                    $height = $width * $ratio;
                } elseif (!isset($width) || $width == "x") {
                    $ratio = $x / $y;
                    $width = $height * $ratio;
                }
                //generate image
                $img_dest = imagecreatetruecolor($width, $height);
                if ($imgtype != 'jpg') {
                    imagealphablending($img_dest, false);
                    imagesavealpha($img_dest, true);
                }
                $r = imagecopyresampled($img_dest, $im, 0, 0, 0, 0, $width, $height, $x, $y) or die("");
                break;
            case 'fixed':
            case 'crop':
                //get dimensions
                if (isset($_GET['size'])) {
                    $width = $_GET['size'];
                    $height = $_GET['size'];
                } else {
                    $width = @$_GET['w'];
                    $height = @$_GET['h'];
                }

                if (($width * ($y / $x)) > $height) {
                    $w = $width;
                    $h = $width * ($y / $x);
                    $xs = 0;
                    $ys = - (($h - $height) / 2);
                } else {
                    $h = $height;
                    $w = $height * ($x / $y);
                    $xs = - (($w - $width) / 2);
                    $ys = 0;
                };
                //generate image
                $img_dest = imagecreatetruecolor($width, $height);
                if ($imgtype != 'jpg') {
                    imagealphablending($img_dest, false);
                    imagesavealpha($img_dest, true);
                }
                $r = imagecopyresampled($img_dest, $im, $xs, $ys, 0, 0, $w, $h, $x, $y) or die("");
                break;
            case 'max':
                //get dimensions
                $w = $max = isset($_GET['size']) ? $_GET['size'] : @$_GET['w'];
                $height = $_GET['h'];
                $xoffset = 0;
                $yoffset = 0;
                $woffset = 0;
                $hoffset = 0;
                if (isset($_GET['h']) && $_GET['h'] > 0) {
                    // имаме зададена височина
                    // картинката ще трябва задължително да
                    // влезе в указаните w/size и h
                    $h = $_GET['h'];
                    $rect_w = $w;
                    $rect_h = $h;

                    // преоразмеряваме правилно картинката
                    $width = $w;
                    $height = round(($rect_w * $y) / $x);
                    $by_width = true;
                    if ($height > $rect_h) {
                        $by_width = false;
                        // височината не съвпада, намаляме и нея и широчината
                        $dest_ratio = $height / $rect_h;
                        $width = round($width / $dest_ratio);
                        $height = round($height / $dest_ratio);
                    }

                    // оправяме отместването на картинката
                    if ($by_width) {
                        $yoffset = ($rect_h / 2) - ($height / 2);
                    } else {
                        $xoffset = ($rect_w / 2) - ($width / 2);
                    }
                } else {
                    // нямаме зададена височина
                    // използваме аспекта
                    // за да си създадем височината
                    // спрямо $max
                    if ($x > $y) {
                        $ratio = $y / $x;
                        $height = $max * $ratio;
                        $width = $max;
                    } else {
                        $ratio = $x / $y;
                        $width = $max * $ratio;
                        $height = $max;
                    }
                }
                //generate image
                $img_dest = imagecreatetruecolor((isset($rect_w) ? $rect_w : $width),
                    (isset($rect_h) ? $rect_h : $height)
                );
                if ($imgtype != 'jpg') {
                    imagealphablending($img_dest, false);
                    imagesavealpha($img_dest, true);
                    $background_color = imagecolorallocatealpha($img_dest, 255, 255, 255, 127);
                } else {
                    $background_color = imagecolorallocate($img_dest, 255, 255, 255);
                }
                imagefill($img_dest, 0, 0, $background_color);
                $r = imagecopyresampled(
                    $img_dest,
                    $im,
                    $xoffset,
                    $yoffset,
                    $woffset,
                    $hoffset,
                    $width,
                    $height,
                    $x,
                    $y
                ) or die("");
                break;
            default:
                echo '!';
                break;
        };

        ob_start();
        switch ($imgtype) {
            default:
            case 'jpg':
                imagejpeg($img_dest, null, 80);
                imagejpeg($img_dest, $cachedname, 80);
                break;
            case 'png':
                imagepng($img_dest);
                imagepng($img_dest, $cachedname);
                break;
            case 'gif':
                imagegif($img_dest);
                imagegif($img_dest, $cachedname);
                break;
            case 'bmp':
                imagejpeg($img_dest);
                imagejpeg($img_dest, $cachedname);
                break;
        }
        $image = ob_get_clean();
        ImageDestroy($im);
        ImageDestroy($img_dest);
    }
    $stats = @stat($imgname);
    $time_ = $stats['mtime'] > 0 ? gmdate("D, d M Y H:i:s", $stats['mtime']) . " GMT" : gmdate(
        "D, d M Y H:i:s",
        mktime(0, 0, 0, date("m"), date("d"), date("Y") - 1)
    ) . " GMT";
    $eTag = "ci-" . md5($image . $time_);
    $headers = getallheaders();
    if (isset($headers['If-None-Match']) && $headers['If-None-Match'] == $eTag && isset($headers['If-Modified-Since']) && ($time_ == $headers['If-Modified-Since'])) {
        header('HTTP/1.1 304 Not Modified');
        header('Cache-Control: private');
        header("Pragma: ");
        header('Expires: ');
        header("Content-Type: {$info['mime']}");
        header('ETag: "' . $eTag . '"');
        exit;
    } else {
        header('Etag: ' . $eTag);
        header("Expires: " . gmdate("D, d M Y H:i:s", mktime(0, 0, 0, date("m"), date("d"), date("Y") + 1)) . " GMT");
        header("Last-Modified: " . $time_);
        header("Cache-Control: max-age=3600, must-revalidate, public");
        header("Pragma: private");

        $size = strlen($image);
        header("Content-Length: $size");
        header("Content-Type: {$info['mime']}");
        print $image;
    }
}
