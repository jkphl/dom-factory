# jkphl/dom-factory

[![CI][ci-image]][ci-url] [![Clear architecture][clear-architecture-image]][clear-architecture-url]

> Simple HTML5/XML DOM factory

*dom-factory* creates regular PHP [`DOMDocument`](https://www.php.net/manual/en/class.domdocument.php) objects from web documents (HTML5, SVG, XML, etc.) — from strings, files or URIs. Compared to PHP's default `DOMDocument` creation, it supports

* HTML5 elements (e.g. `<main>`, `<header>`, `<footer>`, etc.),
* inlined foreign formats (e.g. SVG and MathML),
* XML style namespaces and
* automatic character encoding detection (byte order mark, HTTP header, XML declaration and `<meta>` element).

```php
use Jkphl\Domfactory\Ports\Dom;

$dom = Dom::createFromString('<!DOCTYPE html><html><body><main>…</main></body></html>');
$dom = Dom::createFromFile('/path/to/file.html');
$dom = Dom::createFromUri('https://example.com/path/to/file.xml');
```

## Documentation

Please find the [project documentation](doc/index.md) in the `doc` directory.

## Installation

This library requires PHP 8.2 or later with the `dom`, `libxml` and `mbstring` extensions. I recommend using the latest available version of PHP as a matter of principle. It's installable and autoloadable via [Composer](https://getcomposer.org/) as [jkphl/dom-factory](https://packagist.org/packages/jkphl/dom-factory).

```bash
composer require jkphl/dom-factory
```

### Supported versions

| Version | PHP         | Guzzle      |
|---------|-------------|-------------|
| 2.x     | 8.2 – 8.5   | 7.x and 8.x |
| 1.x     | 7.2.5 – 8.5 | 7.x and 8.x |

### Upgrading from 1.x

Version 2.0 raises the minimum PHP version to 8.2 and adds native type declarations. The public API (`Dom::createFromString()`, `Dom::createFromFile()` and `Dom::createFromUri()`) is unchanged apart from the stricter parameter and return types. Please see the [changelog](CHANGELOG.md) for the details, especially if you implement `HtmlParserInterface` or extend one of the library classes.

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

Copyright © 2020 [Joschi Kuphal][author-url] / joschi@kuphal.net. Licensed under the terms of the [MIT license](LICENSE).


[ci-image]: https://github.com/jkphl/dom-factory/actions/workflows/ci.yml/badge.svg
[ci-url]: https://github.com/jkphl/dom-factory/actions/workflows/ci.yml

[clear-architecture-image]: https://img.shields.io/badge/Clear%20Architecture-%E2%9C%94-brightgreen.svg
[clear-architecture-url]: https://github.com/jkphl/clear-architecture
[author-url]: https://jkphl.is
[PSR-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[PSR-12]: https://www.php-fig.org/psr/psr-12/
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md
