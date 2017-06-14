<?php

/**
 * dom-factory
 *
 * @category Jkphl
 * @package Jkphl\Domfactory
 * @subpackage Jkphl\Domfactory\Infrastructure
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

namespace Jkphl\Domfactory\Infrastructure;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Jkphl\Domfactory\Domain\Dom as DomainDom;
use Jkphl\Domfactory\Ports\RuntimeException;

/**
 * DOM factory (internal)
 *
 * @package Jkphl\Domfactory
 * @subpackage Jkphl\Domfactory\Infrastructure
 */
class Dom
{
    /**
     * Create a DOM document using a HTTP client implementation
     *
     * @param string $url HTTP / HTTPS URL
     * @param array $options Connection options
     * @return \DOMDocument DOM document
     * @throws RuntimeException If the request wasn't successful
     * @throws RuntimeException If a runtime exception occurred
     */
    protected static function createViaHttpClient($url, array $options = [])
    {
        try {
            $clientOptions = isset($options['client']) ? (array)$options['client'] : [];
            $client = new Client(array_merge(['timeout' => 10.0], $clientOptions));
            $requestOptions = isset($options['request']) ? (array)$options['request'] : [];
            $response = $client->get($url, $requestOptions);
            return self::createFromString(strval($response->getBody()));

            // If a runtime exception occurred
        } catch (RequestException $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Create a DOM document from a string
     *
     * @param string $str String
     * @return \DOMDocument DOM document
     */
    public static function createFromString($str)
    {
        $dom = new DomainDom(new HtmlParser());
        return $dom->load($str);
    }

    /**
     * Create a DOM document via the PHP stream wrapper
     *
     * @param string $url URL
     * @param array $options Connection options
     * @return \DOMDocument DOM document
     */
    protected static function createViaStreamWrapper($url, array $options = [])
    {
        $clientOptions = isset($options['client']) ? (array)$options['client'] : [];
        $opts = array_merge_recursive([
            'http' => [
                'method' => 'GET',
                'protocol_version' => 1.1,
                'user_agent' => 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_3; en-US) AppleWebKit/534.3 (KHTML, like Gecko) Chrome/6.0.466.4 Safari/534.3',
                'max_redirects' => 10,
                'timeout' => 10.0,
                'header' => "Accept-language: en\r\n",
            ]
        ], $clientOptions);
        $context = stream_context_create($opts);
        $requestOptions = isset($options['request']) ? (array)$options['request'] : [];
        stream_context_set_params($context, $requestOptions);
        $response = @file_get_contents($url, false, $context);
        return self::createFromString($response);
    }
}
