# About jkphl/dom-factory

[![CI][ci-image]][ci-url] [![Clear architecture][clear-architecture-image]][clear-architecture-url]

> Simple HTML5/XML DOM factory

*dom-factory* instantiates DOM objects from web documents (HTML5, SVG, XML, etc.). It differs from PHP's default `DOMDocument` creation by

* Support for HTML5 elements (e.g. `<main>`, `<header>`, `<footer>`, etc.)
* Support for inlined foreign formats (e.g. [SVG](https://www.w3.org/TR/SVG/), [MathML](https://www.w3.org/TR/MathML2/overview.html), etc.)
* Support for [XML style namespaces](https://www.w3.org/TR/REC-xml-names/)

The factory can be used with [strings](#strings), [files](#files) or [URIs](#uris) and returns **regular PHP [DOMDocument](https://www.php.net/manual/en/class.domdocument.php) objects**. Source data will first be treated as XML using PHP's native [DOM implementation](https://www.php.net/manual/en/domdocument.loadxml.php). If parsing fails (e.g. because of non-wellformedness), the factory falls back to the [masterminds/html5](https://github.com/Masterminds/html5-php) parser.

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

If the [cURL](https://www.php.net/manual/en/book.curl.php) PHP extension is available on your system the factory uses [Guzzle](https://docs.guzzlephp.org) to fetch the web resource or otherwise fall back to a native `file_get_contents()` strategy. Depending on the request mechanism, the second (array) parameter for `Dom::createFromUri()` will be used

* as Guzzle [client](https://docs.guzzlephp.org/en/stable/quickstart.html#creating-a-client) / [request options](https://docs.guzzlephp.org/en/stable/request-options.html) or
* as `$options` for creating a PHP [stream context](https://www.php.net/manual/en/function.stream-context-create.php) (`client` key) respectively [setting its parameters](https://www.php.net/manual/en/en/function.stream-context-set-params.php) (`request` key).

Please make sure to pass in a compatible set of options.

### Character encodings

All sources are converted to UTF-8 before parsing, so the resulting `DOMDocument` is always UTF-8 encoded. The source encoding is determined in the following order:

1. Byte order mark (BOM)
2. The `charset` parameter of the HTTP `Content-Type` header (`Dom::createFromUri()` only)
3. The in-document declaration — either the XML declaration (`<?xml version="1.0" encoding="…"?>`) or a `<meta charset="…">` / `<meta http-equiv="Content-Type" content="…; charset=…">` element within the first 1024 bytes
4. UTF-8, if the source is valid UTF-8
5. Windows-1252 as fallback

In-document encoding declarations are adjusted to UTF-8 during the conversion. Following the [WHATWG Encoding Standard](https://encoding.spec.whatwg.org/), the labels `ISO-8859-1` and `US-ASCII` are treated as Windows-1252.

### Errors

All factory methods throw exceptions implementing `Jkphl\Domfactory\Ports\DomfactoryExceptionInterface`:

* `Jkphl\Domfactory\Ports\InvalidArgumentException` if a file can't be read (`Dom::createFromFile()`)
* `Jkphl\Domfactory\Ports\RuntimeException` if a resource can't be retrieved (`Dom::createFromUri()`)

## Installation

This library requires PHP 7.2.5 or later (including PHP 8.x) with the `dom`, `libxml` and `mbstring` extensions. I recommend using the latest available version of PHP as a matter of principle. It's installable and autoloadable via [Composer](https://getcomposer.org/) as [jkphl/dom-factory](https://packagist.org/packages/jkphl/dom-factory).

```bash
composer require jkphl/dom-factory
```

## Quality

To run the unit tests, the coding standard check and the static analysis at the command line, issue `composer install` and then `composer test` at the package root. The individual checks are available as `composer phpunit`, `composer check-style` and `composer phpstan`.

This library attempts to comply with [PSR-1][], [PSR-12][], and [PSR-4][]. If you notice compliance oversights, please send a patch via pull request.

## Contributing

Found a bug or have a feature request? [Please have a look at the known issues](https://github.com/jkphl/dom-factory/issues) first and open a new issue if necessary. Please see [contributing](CONTRIBUTING.md) and [conduct](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email joschi@kuphal.net instead of using the issue tracker.

## Credits

- [Joschi Kuphal][author-url]
- [All Contributors](../../contributors)

## License

Copyright © 2017 [Joschi Kuphal][author-url] / joschi@kuphal.net. Licensed under the terms of the [MIT license](LICENSE).


[ci-image]: https://github.com/jkphl/dom-factory/actions/workflows/ci.yml/badge.svg
[ci-url]: https://github.com/jkphl/dom-factory/actions/workflows/ci.yml

[clear-architecture-image]: https://img.shields.io/badge/Clear%20Architecture-%E2%9C%94-brightgreen.svg
[clear-architecture-url]: https://github.com/jkphl/clear-architecture
[author-url]: https://jkphl.is
[PSR-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[PSR-12]: https://www.php-fig.org/psr/psr-12/
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md
