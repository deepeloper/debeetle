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
Disabled panel:

![Disabled panel](https://deepeloper.github.io/debeetle-media/images/01.png?1)

Put the bar to the bottom:

![Put the bar to the bottom](https://deepeloper.github.io/debeetle-media/images/02.png?1)

Put the bar to the top:

![Put the bar to the top](https://deepeloper.github.io/debeetle-media/images/03.png?1)

Project homepage link:

![Project homepage link](https://deepeloper.github.io/debeetle-media/images/04.png?1)

Hide project homepage link:

![Hide project homepage link](https://deepeloper.github.io/debeetle-media/images/05.png?1)

Show project homepage link:

![Show project homepage link](https://deepeloper.github.io/debeetle-media/images/06.png?1)

Turn on the panel:

![Turn on the panel](https://deepeloper.github.io/debeetle-media/images/07.png?1)

Turn off the panel:

![Turn off the panel](https://deepeloper.github.io/debeetle-media/images/08.png?1)

Reload the page to view debug:

![Reload the page to view debug](https://deepeloper.github.io/debeetle-media/images/09.png?1)

Server time:

![Server time](https://deepeloper.github.io/debeetle-media/images/10.png?1)

PHP version:

![PHP version](https://deepeloper.github.io/debeetle-media/images/11.png?1)

Page total time:

![Page total time](https://deepeloper.github.io/debeetle-media/images/12.png)

Memory usage:

![Memory usage](https://deepeloper.github.io/debeetle-media/images/13.png)

Peak memory usage:

![Peak memory usage](https://deepeloper.github.io/debeetle-media/images/14.png)

Included files:

![Included files](https://deepeloper.github.io/debeetle-media/images/15.png)

Click to show the panel:

![Click to show the panel](https://deepeloper.github.io/debeetle-media/images/16.png)

Click to hide the panel, trace &amp; dumping object:

![Click to hide the panel, trace &amp; dumping object](https://deepeloper.github.io/debeetle-media/images/.png)

Backslashed tab name\:

![Backslashed tab name\](https://deepeloper.github.io/debeetle-media/images/18.png)

Nested tabs:

![Nested tabs](https://deepeloper.github.io/debeetle-media/images/19.png)

Environment &raquo; Included files:

![](https://deepeloper.github.io/debeetle-media/images/20.png)

Error reports (plugin):

![Error reports (plugin)](https://deepeloper.github.io/debeetle-media/images/21.png)

Benchmarks (plugin):

![Benchmarks (plugin)](https://deepeloper.github.io/debeetle-media/images/22.png)

Debeetle &raquo; Visited pages history containing summary information:

![Debeetle &raquo; Visited pages history](https://deepeloper.github.io/debeetle-media/images/23.png)

Debeetle &raquo; Settings:

![Debeetle &raquo; Settings](https://deepeloper.github.io/debeetle-media/images/24.png)

Color theme, panel opacity and zoom:

![Color theme, panel opacity and zoom](https://deepeloper.github.io/debeetle-media/images/25.png)

&laquo;Trace&apos;n&apos;Dump&raquo; plugin global actions:

![&laquo;Trace&apos;n&apos;Dump&raquo; plugin global actions](https://deepeloper.github.io/debeetle-media/images/26.png)
