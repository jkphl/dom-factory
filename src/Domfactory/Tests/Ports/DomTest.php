<?php

/**
 * dom-factory
 *
 * @category Jkphl
 * @package Jkphl\Domfactory
 * @subpackage Jkphl\Domfactory\Tests
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

namespace Jkphl\Domfactory\Tests\Ports;

use Jkphl\Domfactory\Ports\Dom;
use Jkphl\Domfactory\Tests\AbstractTestBase;

/**
 * DOM factory tests
 *
 * @package Jkphl\Domfactory
 * @subpackage Jkphl\Domfactory\Tests
 */
class DomTest extends AbstractTestBase
{
    /**
     * Test parsing an XML string
     */
    public function testXmlString()
    {
        $dom = Dom::createFromString(file_get_contents(self::$fixture.'books.xml'));
        $this->assertInstanceOf(\DOMDocument::class, $dom);
        $this->assertEquals('catalog', $dom->documentElement->localName);
    }

    /**
     * Test parsing an XML file
     *
     * @expectedException \Jkphl\Domfactory\Ports\InvalidArgumentException
     * @expectedExceptionCode 1495569580
     */
    public function testXmlFile()
    {
        $dom = Dom::createFromFile(self::$fixture.'books.xml');
        $this->assertInstanceOf(\DOMDocument::class, $dom);
        $this->assertEquals('catalog', $dom->documentElement->localName);

        Dom::createFromFile('invalid');
    }

    /**
     * Test parsing an XML string via HTTP
     */
    public function testXmlUri()
    {
        $dom = Dom::createFromUri('http://localhost:9000/books.xml');
        $this->assertInstanceOf(\DOMDocument::class, $dom);
        $this->assertEquals('catalog', $dom->documentElement->localName);
    }

    /**
     * Test parsing an HTML5 string
     */
    public function testHtml5String()
    {
        $dom = Dom::createFromString(file_get_contents(self::$fixture.'html5.html'));
        $this->assertInstanceOf(\DOMDocument::class, $dom);
        $this->assertEquals('html', $dom->documentElement->localName);
        $this->assertEquals('http://www.w3.org/1999/xhtml', $dom->documentElement->namespaceURI);
        $this->assertTrue($dom->documentElement->isDefaultNamespace('http://www.w3.org/1999/xhtml'));
    }

    /**
     * Test parsing an HTML4 string with additional namespaces
     */
    public function testHtml4StringNamespaces()
    {
        $dom = Dom::createFromString(file_get_contents(self::$fixture.'html4.html'));
        $this->assertInstanceOf(\DOMDocument::class, $dom);
        $this->assertEquals('html', $dom->documentElement->localName);
        $this->assertEquals('http://www.w3.org/1999/xhtml', $dom->documentElement->namespaceURI);
        $this->assertTrue($dom->documentElement->isDefaultNamespace('http://www.w3.org/1999/xhtml'));

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('html', 'http://www.w3.org/1999/xhtml');
        $xpath->registerNamespace('test', 'http://example.com/test-ns');
        $htmlElement = $xpath->query('/html:html');
        $this->assertEquals(1, count($htmlElement));
        $this->assertEquals(1, count($xpath->query('namespace::*', $htmlElement->item(0))));
    }

    /**
     * Test parsing an HTML file with inline SVG
     */
    public function testHtmlSvgFile()
    {
        $dom = Dom::createFromFile(self::$fixture.'html+svg.html');
        $this->assertInstanceOf(\DOMDocument::class, $dom);
        $this->assertEquals('html', $dom->documentElement->localName);

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('html', 'http://www.w3.org/1999/xhtml');
        $xpath->registerNamespace('svg', 'http://www.w3.org/2000/svg');
        $this->assertEquals(1, count($xpath->query('/html:html')));
        $this->assertEquals(1, count($xpath->query('//svg')));
        $this->assertEquals(1, count($xpath->query('//svg:svg')));
    }

    /**
     * Test parsing an HTML file with inline MathML
     */
    public function testHtmlMathMLFile()
    {
        $dom = Dom::createFromFile(self::$fixture.'html+mathml.html');
        $this->assertInstanceOf(\DOMDocument::class, $dom);
        $this->assertEquals('html', $dom->documentElement->localName);
        $this->assertEquals('http://www.w3.org/1999/xhtml', $dom->documentElement->namespaceURI);
        $this->assertTrue($dom->documentElement->isDefaultNamespace('http://www.w3.org/1999/xhtml'));

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('html', 'http://www.w3.org/1999/xhtml');
        $xpath->registerNamespace('mathml', 'http://www.w3.org/1998/Math/MathML');
        $this->assertEquals(1, count($xpath->query('/html:html')));
        $this->assertEquals(1, count($xpath->query('/mathml:math')));
    }
}
