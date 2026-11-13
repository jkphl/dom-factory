<?php

/**
 * dom-factory
 *
 * @category   Jkphl
 * @package    Jkphl\Domfactory
 * @subpackage Jkphl\Domfactory\Tests
 * @author     Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @copyright  Copyright © 2020 Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/***********************************************************************************
 *  The MIT License (MIT)
 *
 *  Copyright © 2020 Joschi Kuphal <joschi@kuphal.net> / @jkphl
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy of
 *  this software and associated documentation files (the "Software"), to deal in
 *  the Software without restriction, including without limitation the rights to
 *  use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 *  the Software, and to permit persons to whom the Software is furnished to do so,
 *  subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 *  FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 *  COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 *  IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 *  CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 ***********************************************************************************/

declare(strict_types=1);

namespace Jkphl\Domfactory\Tests\Domain;

use Jkphl\Domfactory\Domain\Encoding;
use PHPUnit\Framework\Attributes\DataProvider;
use Jkphl\Domfactory\Tests\AbstractTestBase;

/**
 * Character encoding service tests
 *
 * @package    Jkphl\Domfactory
 * @subpackage Jkphl\Domfactory\Tests
 */
class EncodingTest extends AbstractTestBase
{
    /**
     * Test the encoding detection
     *
     * @param string $source         Source
     * @param string|null $external  Externally provided encoding
     * @param string $expected       Expected encoding
     */
    #[DataProvider('detectProvider')]
    public function testDetect(string $source, ?string $external, string $expected): void
    {
        $this->assertEquals($expected, Encoding::detect($source, $external));
    }

    /**
     * Encoding detection test cases
     *
     * @return array<string, array<int, string|null>> Test cases
     */
    public static function detectProvider(): array
    {
        return [
            'UTF-8 BOM'                       => ["\xEF\xBB\xBF<p>\xE4</p>", 'ISO-8859-2', 'UTF-8'],
            'UTF-16LE BOM'                    => ["\xFF\xFE<\x00p\x00>\x00", null, 'UTF-16LE'],
            'UTF-16BE BOM'                    => ["\xFE\xFF\x00<\x00p\x00>", null, 'UTF-16BE'],
            'External encoding'               => ['<meta charset="utf-8"><p>x</p>', 'ISO-8859-2', 'ISO-8859-2'],
            'External encoding (quoted)'      => ['<p>x</p>', '"ISO-8859-2"', 'ISO-8859-2'],
            'Unsupported external encoding'   => ['<meta charset="ISO-8859-2"><p>x</p>', 'invalid', 'ISO-8859-2'],
            'XML declaration'                 => ['<?xml version="1.0" encoding="ISO-8859-15"?>', null, 'ISO-8859-15'],
            'XML declaration (single quotes)' => ["<?xml version='1.0' encoding='KOI8-R'?><r/>", null, 'KOI8-R'],
            'Meta charset'                    => ['<html><head><meta charset="iso-8859-2">', null, 'ISO-8859-2'],
            'Meta charset (unquoted)'         => ['<html><head><meta charset=windows-1251>', null, 'Windows-1251'],
            'Meta http-equiv'                 => [
                '<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-2">',
                null,
                'ISO-8859-2'
            ],
            'Declared ISO-8859-1'             => ['<meta charset="ISO-8859-1">', null, 'Windows-1252'],
            'Declared US-ASCII'               => ['<meta charset="us-ascii">', null, 'Windows-1252'],
            'Declared UTF-16'                 => ['<meta charset="utf-16">', null, 'UTF-8'],
            'Declared Shift_JIS'              => ['<meta charset="Shift_JIS">', null, 'SJIS-win'],
            'Unsupported declaration (UTF-8)' => ["<meta charset=\"invalid\"><p>\xC3\xA4</p>", null, 'UTF-8'],
            'Valid UTF-8'                     => ["<p>\xC3\xA4 \xF0\x9F\x98\x80</p>", null, 'UTF-8'],
            'ASCII only'                      => ['<p>abc</p>', null, 'UTF-8'],
            'Invalid UTF-8'                   => ["<p>\xE4\x80</p>", null, 'Windows-1252'],
            'Declaration beyond prescan'      => [str_repeat(' ', 1024) . '<meta charset="ISO-8859-2">', null, 'UTF-8'],
        ];
    }

    /**
     * Test the conversion to UTF-8
     *
     * @param string $source         Source
     * @param string|null $external  Externally provided encoding
     * @param string $expected       Expected result
     */
    #[DataProvider('toUtf8Provider')]
    public function testToUtf8(string $source, ?string $external, string $expected): void
    {
        $this->assertSame($expected, Encoding::toUtf8($source, $external));
    }

    /**
     * UTF-8 conversion test cases
     *
     * @return array<string, array<int, string|null>> Test cases
     */
    public static function toUtf8Provider(): array
    {
        return [
            'UTF-8 with emoji'          => ["<p>\xC3\xA4\xF0\x9F\x98\x80</p>", null, "<p>\xC3\xA4\xF0\x9F\x98\x80</p>"],
            'UTF-8 BOM'                 => ["\xEF\xBB\xBF<p>\xC3\xA4</p>", null, "<p>\xC3\xA4</p>"],
            'UTF-16LE BOM'              => ["\xFF\xFE<\x00p\x00>\x00\xE4\x00", null, "<p>\xC3\xA4"],
            'Windows-1252 fallback'     => ["<p>\xE4\x80</p>", null, "<p>\xC3\xA4\xE2\x82\xAC</p>"],
            'XML declaration'           => [
                "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?><r>\xE4</r>",
                null,
                "<?xml version=\"1.0\" encoding=\"UTF-8\"?><r>\xC3\xA4</r>"
            ],
            'Meta charset'              => [
                "<meta charset=\"iso-8859-2\"><p>\xB9</p>",
                null,
                "<meta charset=\"UTF-8\"><p>\xC5\xA1</p>"
            ],
            'Meta http-equiv'           => [
                "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-2\"><p>\xB9</p>",
                null,
                "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\"><p>\xC5\xA1</p>"
            ],
            'External encoding wins'    => [
                "<meta charset=\"utf-8\"><p>\xB9</p>",
                'ISO-8859-2',
                "<meta charset=\"UTF-8\"><p>\xC5\xA1</p>"
            ],
            'Script content untouched'  => [
                "<script>var s = '\xC3\xA4';</script>",
                null,
                "<script>var s = '\xC3\xA4';</script>"
            ],
            'Empty source'              => ['', null, ''],
        ];
    }
}
