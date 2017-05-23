<?php

/**
 * dom-factory
 *
 * @category Jkphl
 * @package Jkphl\Domfactory
 * @subpackage Jkphl\Domfactory\Domain
 * @author Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @copyright Copyright © 2017 Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/***********************************************************************************
 *  The MIT License (MIT)
 *
 *  Copyright © 2017 Joschi Kuphal <joschi@kuphal.net> / @jkphl
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
 * DOM factory
 *
 * @package Jkphl\Rdfalite
 * @subpackage Jkphl\Domfactory\Domain
 */
class Dom
{
    /**
     * HTML parser
     *
     * @var HtmlParserInterface
     */
    protected $htmlParser;

    /**
     * DOM factory constructor
     *
     * @param HtmlParserInterface $htmlParser HTML parser
     */
    public function __construct(HtmlParserInterface $htmlParser = null)
    {
        $this->htmlParser = $htmlParser ?: new HtmlParser();
    }

    /**
     * Create a DOM document from a string
     *
     * @param string $source XML/HTML string
     * @return \DOMDocument DOM document
     */
    public function load($source)
    {
        $source = mb_convert_encoding($source, 'HTML-ENTITIES', mb_detect_encoding($source));
        $dom = new \DOMDocument();

        // Try to load the source as standard XML document first, then as HTML document
        if (!$dom->loadXML($source, LIBXML_NOWARNING | LIBXML_NOERROR)) {
            $dom = $this->htmlParser->loadHTML($source);
        }

        return $dom;
    }
}
