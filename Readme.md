# Rewrite Url

This module allows you to create general redirection rules for your website. You can also manage rewritten urls for products, categories, folders, contents, brands.

* Allows you to redirect a url to another based on a regular expression or matching GET parameters
* Allows you to reassign a url to another (product, category, folder, content, brand)
* Allows you to reassign all urls to another (product, category, folder, content, brand)
* Allows you to reassign a default url to your (product, category, folder, content, brand)
* Allows you to display the list of not rewriten urls (product, category, folder, content, brand)

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/thelia-modules/RewriteUrl/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/thelia-modules/RewriteUrl/?branch=master)
[![License](https://poser.pugx.org/thelia/rewrite-url-module/license)](https://packagist.org/packages/thelia/rewrite-url-module)
[![Latest Stable Version](https://poser.pugx.org/thelia/rewrite-url-module/v/stable)](https://packagist.org/packages/thelia/rewrite-url-module)

#### [See the changelog](https://github.com/thelia-modules/RewriteUrl/blob/master/CHANGELOG.md)

## Compatibility

Thelia > 2.1

## Installation

### Manually

* Copy the module into ```<thelia_root>/local/modules/``` directory and be sure that the name of the module is RewriteUrl.
* Activate it in your thelia administration panel

### Composer

Add it in your main thelia composer.json file

```
composer require thelia/rewrite-url-module:~1.5.1
```

## Usage

BackOffice :
- in Product edit tab modules
- in Folder edit tab modules
- in Content edit tab modules
- in Brand edit tab modules
- in Category edit tab modules
- in Configuration list not rewriten urls
- in the module configuration page

## Screenshot

#### In "Modules" tab

![RewriteUrl](https://github.com/thelia-modules/RewriteUrl/blob/master/screenshot/screenshot-1.jpeg)

#### In Thelia configuration

![RewriteUrl](https://github.com/thelia-modules/RewriteUrl/blob/master/screenshot/screenshot-2.jpeg)

#### In the module configuration

![RewriteUrl](https://github.com/thelia-modules/RewriteUrl/blob/master/screenshot/screenshot-3.png)