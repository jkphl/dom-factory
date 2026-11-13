<?php

/**
 * dom-factory
 *
 * @category   Jkphl
 * @package    Jkphl\Domfactory
 * @subpackage Jkphl\Domfactory\Domain
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

namespace Jkphl\Domfactory\Domain;

/**
 * Character encoding service
 *
 * Determines the character encoding of an XML / HTML source and normalizes it to UTF-8. The encoding is determined
 * in the following order (roughly following the HTML Living Standard's encoding sniffing algorithm):
 *
 * 1. Byte order mark (BOM)
 * 2. Externally provided character encoding (e.g. from an HTTP `Content-Type` header)
 * 3. In-document declaration (XML declaration or `<meta>` element within the first 1024 bytes)
 * 4. UTF-8 if the source is valid UTF-8
 * 5. Windows-1252 as fallback
 *
 * @package    Jkphl\Domfactory
 * @subpackage Jkphl\Domfactory\Domain
 */
class Encoding
{
    /**
     * Target encoding
     *
     * @var string
     */
    public const UTF8 = 'UTF-8';
    /**
     * Fallback encoding
     *
     * @var string
     */
    public const FALLBACK = 'Windows-1252';
    /**
     * Number of bytes to scan for an in-document encoding declaration
     *
     * @var int
     */
    public const PRESCAN_LENGTH = 1024;
    /**
     * XML declaration encoding pattern
     *
     * @var string
     */
    public const XML_DECLARATION_PATTERN = '/^(\s*<\?xml\s[^>]*?\bencoding\s*=\s*(["\']))\s*([^"\']+?)\s*(\2)/i';
    /**
     * Meta element charset pattern (matches both `<meta charset>` and `<meta http-equiv="Content-Type">`)
     *
     * @var string
     */
    public const META_CHARSET_PATTERN = '/(<meta\s[^>]*?\bcharset\s*=\s*["\']?\s*)([a-z0-9_:.()+\-]+)/i';
    /**
     * Byte order marks
     *
     * @var string[]
     */
    public const BOMS = [
        "\xEF\xBB\xBF" => 'UTF-8',
        "\xFE\xFF"     => 'UTF-16BE',
        "\xFF\xFE"     => 'UTF-16LE',
    ];
    /**
     * Encoding label aliases (WHATWG Encoding Standard)
     *
     * @var string[]
     */
    public const ALIASES = [
        'ascii'           => self::FALLBACK,
        'us-ascii'        => self::FALLBACK,
        'iso-8859-1'      => self::FALLBACK,
        'iso8859-1'       => self::FALLBACK,
        'iso_8859-1'      => self::FALLBACK,
        'latin1'          => self::FALLBACK,
        'l1'              => self::FALLBACK,
        'cp1252'          => self::FALLBACK,
        'windows-1252'    => self::FALLBACK,
        'x-cp1252'        => self::FALLBACK,
        'utf8'            => self::UTF8,
        'unicode-1-1-utf-8' => self::UTF8,
        'gb2312'          => 'CP936',
        'gbk'             => 'CP936',
        'x-gbk'           => 'CP936',
        'shift_jis'       => 'SJIS-win',
        'x-sjis'          => 'SJIS-win',
        'ks_c_5601-1987'  => 'UHC',
        'euc-kr'          => 'UHC',
    ];
    /**
     * Pseudo encodings supported by mbstring that must not be used for documents
     *
     * @var string[]
     */
    public const PSEUDO_ENCODINGS = [
        'pass',
        'auto',
        'wchar',
        'base64',
        'uuencode',
        'html-entities',
        'quoted-printable',
        '7bit',
        '8bit',
    ];
    /**
     * Supported encodings (lowercase name or alias => mbstring encoding name)
     *
     * @var string[]|null
     */
    protected static $supported = null;

    /**
     * Convert an XML / HTML source to UTF-8
     *
     * The returned source is valid UTF-8 and existing in-document encoding declarations are adjusted accordingly.
     *
     * @param string $source           XML / HTML source
     * @param string|null $encoding    Externally provided character encoding (e.g. from an HTTP header)
     *
     * @return string UTF-8 encoded XML / HTML source
     */
    public static function toUtf8($source, $encoding = null)
    {
        $source         = (string)$source;
        $sourceEncoding = self::detect($source, $encoding);

        // Strip a byte order mark
        foreach (self::BOMS as $bom => $bomEncoding) {
            if (strncmp($source, $bom, strlen($bom)) === 0) {
                $source = (string)substr($source, strlen($bom));
                break;
            }
        }

        // Convert to (valid) UTF-8
        $source = (string)mb_convert_encoding($source, self::UTF8, $sourceEncoding);

        return self::declareUtf8($source);
    }

    /**
     * Determine the character encoding of an XML / HTML source
     *
     * @param string $source           XML / HTML source
     * @param string|null $encoding    Externally provided character encoding (e.g. from an HTTP header)
     *
     * @return string Character encoding (mbstring encoding name)
     */
    public static function detect($source, $encoding = null)
    {
        $source = (string)$source;

        // 1. Byte order mark
        foreach (self::BOMS as $bom => $bomEncoding) {
            if (strncmp($source, $bom, strlen($bom)) === 0) {
                return $bomEncoding;
            }
        }

        // 2. Externally provided encoding
        $encoding = self::normalize($encoding);
        if ($encoding !== null) {
            return $encoding;
        }

        // 3. In-document declaration
        $declared = self::normalize(self::declared($source));
        if ($declared !== null) {
            // An ASCII-readable declaration can't be UTF-16 / UTF-32
            return preg_match('/^UTF-(16|32)/i', $declared) ? self::UTF8 : $declared;
        }

        // 4. UTF-8 or 5. fallback
        return mb_check_encoding($source, self::UTF8) ? self::UTF8 : self::FALLBACK;
    }

    /**
     * Return the in-document encoding declaration of an XML / HTML source
     *
     * @param string $source XML / HTML source
     *
     * @return string|null Declared encoding label
     */
    protected static function declared($source)
    {
        $prescan = substr($source, 0, self::PRESCAN_LENGTH);

        if (preg_match(self::XML_DECLARATION_PATTERN, $prescan, $match)) {
            return $match[3];
        }

        if (preg_match(self::META_CHARSET_PATTERN, $prescan, $match)) {
            return $match[2];
        }

        return null;
    }

    /**
     * Adjust in-document encoding declarations to UTF-8
     *
     * @param string $source UTF-8 encoded XML / HTML source
     *
     * @return string XML / HTML source with adjusted encoding declarations
     */
    protected static function declareUtf8($source)
    {
        // Single byte encodings may grow up to 3 bytes per character when converted to UTF-8
        $prescanLength = self::PRESCAN_LENGTH * 3;
        $prescan       = (string)substr($source, 0, $prescanLength);
        $prescan       = (string)preg_replace(self::XML_DECLARATION_PATTERN, '${1}UTF-8${4}', $prescan, 1, $count);
        if (!$count) {
            $prescan = (string)preg_replace(self::META_CHARSET_PATTERN, '${1}UTF-8', $prescan, 1);
        }

        return $prescan . substr($source, $prescanLength);
    }

    /**
     * Normalize an encoding label to a supported mbstring encoding name
     *
     * @param string|null $label Encoding label
     *
     * @return string|null mbstring encoding name or NULL if unsupported
     */
    protected static function normalize($label)
    {
        $label = strtolower(trim((string)$label, " \t\n\r\f\"'"));
        if ($label === '') {
            return null;
        }

        if (isset(self::ALIASES[$label])) {
            $label = strtolower(self::ALIASES[$label]);
        }

        $supported = self::supported();

        return isset($supported[$label]) ? $supported[$label] : null;
    }

    /**
     * Return the supported encodings
     *
     * @return string[] Supported encodings (lowercase name or alias => mbstring encoding name)
     */
    protected static function supported()
    {
        if (self::$supported === null) {
            self::$supported = [];
            foreach (mb_list_encodings() as $encoding) {
                if (in_array(strtolower($encoding), self::PSEUDO_ENCODINGS, true)) {
                    continue;
                }
                self::$supported[strtolower($encoding)] = $encoding;
                foreach ((mb_encoding_aliases($encoding) ?: []) as $alias) {
                    if (!in_array(strtolower($alias), self::PSEUDO_ENCODINGS, true)) {
                        self::$supported[strtolower($alias)] = $encoding;
                    }
                }
            }
        }

        return self::$supported;
    }
}
