# About jkphl/dom-factory

[![Build Status][travis-image]][travis-url] [![Coverage Status][coveralls-image]][coveralls-url] [![Scrutinizer Code Quality][scrutinizer-image]][scrutinizer-url] [![Code Climate][codeclimate-image]][codeclimate-url]  [![Clear architecture][clear-architecture-image]][clear-architecture-url]

> Simple HTML5/XML DOM factory

*dom-factory* instantiates DOM objects from web documents (HTML5, SVG, XML, etc.). It differs from PHP's default `DOMDocument` creation by

* Support for HTML5 elements (e.g. `<main>`, `<header>`, `<footer>`, etc.)
* Support for inlined foreign formats (e.g. [SVG](https://www.w3.org/TR/SVG/), [MathML](https://www.w3.org/TR/MathML2/overview.html), etc.)
* Support for [XML style namespaces](https://www.w3.org/TR/REC-xml-names/)

The factory can be used with [strings](#strings), [files](#files) or [URIs](#uris) and returns **regular PHP [DOMDocument](http://php.net/manual/de/class.domdocument.php) objects**. Source data will first be treated as XML using PHP's native [DOM implementation](http://php.net/manual/de/domdocument.loadxml.php). If parsing fails (e.g. because of non-wellformedness), the factory falls back to the [masterminds/html5](https://github.com/Masterminds/html5-php) parser.

### Strings

```php
use Jkphl\Domfactory\Ports\Dom;
$dom = Dom::createFromString('<html><head/><body>...</body></html>');
```

### Files

```php
use Jkphl\Domfactory\Ports\Dom;
$dom = Dom::createFromFile('/path/to/file.html');
```

### URIs

```php
use Jkphl\Domfactory\Ports\Dom;
$dom = Dom::createFromUri('https://example.com/path/to/file.xml');

// With HTTP client options
$options = [
    'client' => ['timeout' => 30],
    'request' => ['verify' => false],
];
$dom = Dom::createFromUri('https://example.com/path/to/file.xml', $options);
```

If the [cURL](http://php.net/manual/book.curl.php) PHP extension is available on your system the factory uses [Guzzle](http://docs.guzzlephp.org) to fetch the web resource or otherwise fall back to a native `file_get_contents()` strategy. Depending on the request mechanism, the second (array) parameter for `Dom::createFromUri()` will be used

* as Guzzle [client](http://docs.guzzlephp.org/en/latest/quickstart.html#creating-a-client) / [request options](http://docs.guzzlephp.org/en/latest/request-options.html) or
* as `$options` for creating a PHP [stream context](http://php.net/manual/function.stream-context-create.php) (`client` key) respectively [setting its parameters](http://php.net/manual/en/function.stream-context-set-params.php) (`request` key).

Please make sure to pass in a compatible set of options.

## Installation

This library requires PHP >=5.6 or later. I recommend using the latest available version of PHP as a matter of principle. It has no userland dependencies. It's installable and autoloadable via [Composer](https://getcomposer.org/) as [jkphl/dom-factory](https://packagist.org/packages/jkphl/dom-factory).
        
```bash
composer require jkphl/dom-factory
```

Alternatively, [download a release](https://github.com/jkphl/dom-factory/releases) or clone this repository, then require or include its [`autoload.php`](autoload.php) file.

## Dependencies

![Composer dependency graph](https://rawgit.com/jkphl/dom-factory/master/doc/dependencies.svg)

## Quality

To run the unit tests at the command line, issue `composer install` and then `phpunit` at the package root. This requires [Composer](http://getcomposer.org/) to be available as `composer`, and [PHPUnit](http://phpunit.de/manual/) to be available as `phpunit`.

This library attempts to comply with [PSR-1][], [PSR-2][], and [PSR-4][]. If you notice compliance oversights, please send a patch via pull request.

## Contributing

Found a bug or have a feature request? [Please have a look at the known issues](https://github.com/jkphl/dom-factory/issues) first and open a new issue if necessary. Please see [contributing](CONTRIBUTING.md) and [conduct](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email joschi@kuphal.net instead of using the issue tracker.

## Credits

- [Joschi Kuphal][author-url]
- [All Contributors](../../contributors)

## License

Copyright © 2017 [Joschi Kuphal][author-url] / joschi@kuphal.net. Licensed under the terms of the [MIT license](LICENSE).


[travis-image]: https://secure.travis-ci.org/jkphl/dom-factory.svg
[travis-url]: https://travis-ci.org/jkphl/dom-factory
[coveralls-image]: https://coveralls.io/repos/jkphl/dom-factory/badge.svg?branch=master&service=github
[coveralls-url]: https://coveralls.io/github/jkphl/dom-factory?branch=master
[scrutinizer-image]: https://scrutinizer-ci.com/g/jkphl/dom-factory/badges/quality-score.png?b=master
[scrutinizer-url]: https://scrutinizer-ci.com/g/jkphl/dom-factory/?branch=master
[codeclimate-image]: https://lima.codeclimate.com/github/jkphl/dom-factory/badges/gpa.svg
[codeclimate-url]: https://lima.codeclimate.com/github/jkphl/dom-factory

[clear-architecture-image]: https://img.shields.io/badge/Clear%20Architecture-%E2%9C%94-brightgreen.svg
[clear-architecture-url]: https://github.com/jkphl/clear-architecture
[author-url]: https://jkphl.is
[PSR-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md
