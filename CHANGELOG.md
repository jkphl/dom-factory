# Changelog

All Notable changes to *jkphl/dom-factory* will be documented in this file.

Updates should follow the [Keep a CHANGELOG](http://keepachangelog.com/) principles.


## [2.0.0] Major Release (2026-11-13)

### Changed

* Require PHP 8.2 or later (use the 1.x releases for PHP 7.2.5 – 8.1)
* Added native parameter, return and property types to all classes and interfaces
* Declared strict types in all source files
* Updated PHPUnit to 11.5 / 12.5 / 13.4 (depending on the PHP version), PHPStan to 2.x
* CI tests PHP 8.2 to 8.5
* Removed the unused PHPMD configuration

### Upgrading from 1.x

* `Ports\Dom::createFromString(string $str)`, `createFromFile(string $file)` and `createFromUri(string $url, array $options = [])` now declare their parameter types and return `DOMDocument`. Passing non-string values results in a `TypeError` when the calling code uses strict types.
* Custom HTML parsers must implement the typed interface method `HtmlParserInterface::loadHTML(string $html): DOMDocument`. The HTML source passed in is always UTF-8 encoded.
* Classes extending `Domain\Dom`, `Infrastructure\Dom`, `Ports\Dom` or one of the HTML parsers must adapt the signatures of overridden methods to the new type declarations. `Domain\Dom::$htmlParser` is now a typed property.

## [1.1.0] Maintenance Release (2026-11-13)

### Added

* Support for Guzzle 8
* Support for PHP 8.1 to 8.5
* Character encoding detection based on byte order mark, HTTP `Content-Type` header, XML declaration and `<meta>` element
* GitHub Actions workflow testing PHP 7.2 to 8.5 with the lowest and highest dependency versions
* PHPStan static analysis

### Changed

* Sources are converted to UTF-8 before parsing instead of converting non-ASCII characters to HTML entities
* All Guzzle exceptions (including connection errors) are converted to `RuntimeException`
* The stream wrapper throws a `RuntimeException` if a resource can't be retrieved
* `Dom::createFromFile()` throws an `InvalidArgumentException` for directories
* Stream wrapper options are replaced instead of being merged into arrays
* Updated the stream wrapper user agent
* Require `guzzlehttp/guzzle` ^7.15.2 or ^8.0.1 (versions affected by security advisories are excluded; Guzzle 6 is no longer supported)
* Require `masterminds/html5` ^2.9
* Switched the coding standard from PSR-2 to PSR-12
* Removed Travis CI, Scrutinizer, Code Climate and Coveralls

### Fixed

* Deprecated `HTML-ENTITIES` mbstring conversion ([#2](https://github.com/jkphl/dom-factory/issues/2), [#6](https://github.com/jkphl/dom-factory/pull/6))
* Implicitly nullable parameter deprecation on PHP 8.4 ([#6](https://github.com/jkphl/dom-factory/pull/6))
* Corrupted script contents, CDATA sections and comments containing non-ASCII characters
* Corrupted characters outside the Basic Multilingual Plane (e.g. emoji)
* Garbled non-UTF-8 documents
* Test web server not being terminated after the test run

## [1.0.1] Maintenance Release (2021-06-28)

### Changed

* Add test config for PHP 8.0
* Add Guzzle 7.0 support ([#4](https://github.com/jkphl/dom-factory/pull/4))
* Use original clue/graph-composer dependency ([#5](https://github.com/jkphl/dom-factory/pull/5))

## [1.0.0] Maintenance Release (2020-09-18)

### Changed

* Updated dependencies (e.g. PHP >= 7.2)
* Add missing ext-mbstring requirement ([#3](https://github.com/jkphl/dom-factory/pull/3))

## [0.1.2] - 2017-06-14

### Fixed

* Removed usage of old Guzzle components ([#1](https://github.com/jkphl/dom-factory/issues/1))

## [0.1.1] - 2017-05-29

### Added

* Improved HTTP client / request options

## [0.1.0] - 2017-05-24

Initial release
