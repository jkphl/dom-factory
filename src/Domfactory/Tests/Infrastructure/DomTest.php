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

namespace Jkphl\Domfactory\Tests\Infrastructure {

    use DOMDocument;
    use Jkphl\Domfactory\Ports\Dom;
    use Jkphl\Domfactory\Ports\RuntimeException;
    use Jkphl\Domfactory\Tests\AbstractTestBase;

    /**
     * DOM factory tests
     *
     * @package    Jkphl\Domfactory
     * @subpackage Jkphl\Domfactory\Tests
     */
    class DomTest extends AbstractTestBase
    {
        /**
         * Test malformed URL
         */
        public function testMalformedUri(): void
        {
            $this->expectException(RuntimeException::class);
            Dom::createFromUri('');
        }

        /**
         * Test stream wrapper
         */
        public function testStreamWrapper(): void
        {
            putenv('MOCK_EXTENSION_LOADED=1');
            $dom = Dom::createFromUri('http://localhost:1349/books.xml');
            $this->assertInstanceOf(DOMDocument::class, $dom);
            $this->assertEquals('catalog', $dom->documentElement->localName);
            putenv('MOCK_EXTENSION_LOADED');
        }

        /**
         * Test that the stream wrapper respects the HTTP Content-Type charset
         */
        public function testStreamWrapperHttpCharset(): void
        {
            putenv('MOCK_EXTENSION_LOADED=1');
            try {
                $dom = Dom::createFromUri('http://localhost:1349/http-charset.php');
                $this->assertEquals("\u{161}\u{10d}", $dom->getElementsByTagName('p')->item(0)->textContent);
            } finally {
                putenv('MOCK_EXTENSION_LOADED');
            }
        }

        /**
         * Test that the stream wrapper throws an exception if the resource can't be retrieved
         */
        public function testStreamWrapperError(): void
        {
            putenv('MOCK_EXTENSION_LOADED=1');
            try {
                $this->expectException(RuntimeException::class);
                Dom::createFromUri('http://localhost:1349/missing.html');
            } finally {
                putenv('MOCK_EXTENSION_LOADED');
            }
        }
    }
}

namespace Jkphl\Domfactory\Ports {

    /**
     * Find out whether an extension is loaded
     *
     * @param string $name The extension name
     *
     * @return boolean The extension is loaded
     */
    function extension_loaded($name)
    {
        return (getenv('MOCK_EXTENSION_LOADED') != 1) ? \extension_loaded($name) : false;
    }
}
