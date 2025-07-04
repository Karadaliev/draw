# draw.php

**Draw.php** is a one file tool for a inline image thumbnails that works in real-time.

## Description

This tool allow you to just use the basic `<img />` tag and to implement thumbnail size images. If the cache file structure is available and writable the tool will store the resized thumbnails there and will reuse them in the future (instead of creating the thumbnails on the fly).

## Getting Started

### Dependencies

* PHP 8.3
* GD library

### Installing

* Download *draw.php* file and put it in the **root** of your project
* *(optional)* Create a **cache** file structure in `files/draw.cache/` with sub-folders for the image types you are planning to use, for example `files/draw.cache/jpg/`, and `files/draw.cache/png/`

### Usage

* In any kind of pure HTML, or html-type templates just put it in the image tag's src attribute, for example:
```
<img src="/draw.php?do=resize&path=images/example.jpg&w=640&h=480" />
```

### Attributes
* **do**:
  * *resize*: Resizes the image to a provided `w` and `h` size in pixels (this can stretch the image if the aspect it's wrong)
  * *crop*: Creates an thumbnail in the exact `w` and `h` size in pixels and fit's the whole image inside *(cropping the image to fit if needed)*
  * *max*: Creates an thumbnail in the exact `w` and `h` size in pixels and fit's the whole image inside *(adding borders if needed)*

* **path**: This is the relative path to the image you want to use, for example `images/1.jpg`, or even external url like `https://en.wikipedia.org/static/images/icons/wikipedia.png`
* **w**: Width of the desired image
* **h**: Height of the desired image

## Author

Danail Karadaliev [@danail]()

## Version History

* 1.0.9
    * Updated for usage with PHP 8.3

## License

This project is licensed under the MIT License - see the LICENSE file for details
