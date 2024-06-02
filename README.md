# Debeetle
PHP Debugging Tools supporting third party plugins, skins and color themes.

Supports panel visibility for any conditions for `$_SERVER` / `$_COOKIE` / `$_SESSION` / `$_REQUEST` / `$_GET` / `$_POST`.

[![Packagist version](https://img.shields.io/packagist/v/deepeloper/debeetle)](https://packagist.org/packages/deepeloper/debeetle)
[![PHP from Packagist](https://img.shields.io/packagist/php-v/deepeloper/debeetle.svg)](http://php.net/)
[![GitHub license](https://img.shields.io/github/license/deepeloper/debeetle.svg)](https://github.com/deepeloper/debeetle/blob/main/LICENSE)
[![GitHub issues](https://img.shields.io/github/issues-raw/deepeloper/debeetle.svg)](https://github.com/deepeloper/debeetle/issues)
[![Packagist](https://img.shields.io/packagist/dt/deepeloper/debeetle.svg)](https://packagist.org/packages/deepeloper/debeetle)

[![Donation](https://img.shields.io/badge/Donation-Visa,%20MasterCard,%20Maestro,%20UnionPay,%20YooMoney,%20МИР-red)](https://yoomoney.ru/to/41001351141494)

## Compatibility
[![PHP 5.4](https://img.shields.io/badge/PHP->=5.4-%237A86B8)]()

## Live demo:
https://deepelopment.free.nf/debeetle/path/to/page/

## Installation
* Run `composer require deepeloper/debeetle`;
* Copy "skel.config.xml.php", "skel.config.json.php" and "debeetle.xsd" to your own appropriate place, then modify next nodes:
    * "debeetle/config/path/assets" and "debeetle/config/path/root" (optional) to your own paths;
    * Optionally change "debeetle/config/defaults/language" node to "ru";
    * Modify rules in "debeetle/config(name='localhost').
* Copy "public/debeetle.php" to appropriate public place;
* Modify "debeetle/config/path/script" node;
* Modify $autoloadPath and $autoloadPath in "debeetle.php";
* See https://github.com/deepeloper/debeetle-example repo on the top and on the bottom of "public/path/to/page/index.php". 

The configuration can be located either in the XML file or in the JSON file for acceleration and load reduction.

## Plugins
See <plugin/> nodes in "config.xml.php" and pluginType in "debeetle.xsd" and "plugins" folder for examples.  

## Skins &amp; color themes
See <skin/> nodes in "config.xml.php" and skinType/themeType in "debeetle.xsd" and "skins" folder for examples.

---
Dumping object:

![Dumping object](https://deepeloper.github.io/debeetle-media/images/01.png)

Nested tabs:

![Dumping object](https://deepeloper.github.io/debeetle-media/images/02.png)

Included files list:

![Dumping object](https://deepeloper.github.io/debeetle-media/images/03.png)

Error reports plugin:

![Dumping object](https://deepeloper.github.io/debeetle-media/images/04.png)

Page total time:

![Dumping object](https://deepeloper.github.io/debeetle-media/images/05.png)

Memory usage:

![Dumping object](https://deepeloper.github.io/debeetle-media/images/06.png)

Peak memory usage:

![Dumping object](https://deepeloper.github.io/debeetle-media/images/07.png)

Included files number:

![Dumping object](https://deepeloper.github.io/debeetle-media/images/08.png)

Visited pages history containing summary information:

![Dumping object](https://deepeloper.github.io/debeetle-media/images/09.png)

Color theme, panel opacity and zoom:

![Dumping object](https://deepeloper.github.io/debeetle-media/images/10.png)

Trace&apos;n&apos;Dump plugin global actions:

![Dumping object](https://deepeloper.github.io/debeetle-media/images/11.png)
