<?php

/**
 * dom-factory
 *
 * @category Jkphl
 * @package Jkphl\Domfactory
 * @subpackage Jkphl\Domfactory\Tests
 * @author Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @copyright Copyright © 2018 Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/***********************************************************************************
 *  The MIT License (MIT)
 *
 *  Copyright © 2018 Joschi Kuphal <joschi@kuphal.net> / @jkphl
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

namespace Jkphl\Domfactory\Tests\Domain;

use Jkphl\Domfactory\Domain\HtmlParser;
use Jkphl\Domfactory\Tests\AbstractTestBase;

/**
 * HTML parser test
 *
 * @package Jkphl\Rdfalite
 * @subpackage Jkphl\Rdfalite\Tests
 */
class HtmlParserTest extends AbstractTestBase
{
    /**
     * Test the HTML parser
     *
     * @expectedException \Jkphl\Domfactory\Domain\InvalidArgumentException
     * @expectedExceptionCode 1495570167
     */
    public function testHtmlParser()
    {
        $htmlParser = new HtmlParser();
        $dom = $htmlParser->loadHTML(file_get_contents(self::$fixture.'html4.html'));
        $this->assertInstanceOf(\DOMDocument::class, $dom);
        $this->assertEquals('html', $dom->documentElement->localName);

        $htmlParser->loadHTML(file_get_contents(self::$fixture.'html5.html'));
    }
}
