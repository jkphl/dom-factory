<?php

/**
 * dom-factory
 *
 * @category   Jkphl
 * @package    Jkphl\Domfactory
 * @subpackage Jkphl\Domfactory\Infrastructure
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

namespace Jkphl\Domfactory\Infrastructure;

use DOMDocument;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Jkphl\Domfactory\Domain\Dom as DomainDom;
use Jkphl\Domfactory\Ports\RuntimeException;

/**
 * DOM factory (internal)
 *
 * @package    Jkphl\Domfactory
 * @subpackage Jkphl\Domfactory\Infrastructure
 */
class Dom
{
    /**
     * Default user agent for the stream wrapper strategy
     *
     * @var string
     */
    public const USER_AGENT = 'Mozilla/5.0 (compatible; jkphl/dom-factory; +https://github.com/jkphl/dom-factory)';

    /**
     * Create a DOM document using a HTTP client implementation
     *
     * @param string $url    HTTP / HTTPS URL
     * @param array<string, mixed> $options Connection options
     *
     * @return DOMDocument DOM document
     * @throws RuntimeException If the request wasn't successful
     */
    protected static function createViaHttpClient($url, array $options = [])
    {
        try {
            $clientOptions  = isset($options['client']) ? (array)$options['client'] : [];
            $client         = new Client(array_merge(['timeout' => 10.0], $clientOptions));
            $requestOptions = isset($options['request']) ? (array)$options['request'] : [];
            $response       = $client->request('GET', $url, $requestOptions);

            return self::createFromStringWithEncoding(
                (string)$response->getBody(),
                self::getContentTypeCharset($response->getHeaderLine('Content-Type'))
            );

            // If the request failed
        } catch (GuzzleException $e) {
            throw new RuntimeException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * Create a DOM document from a string
     *
     * @param string $str String
     *
     * @return DOMDocument DOM document
     */
    public static function createFromString($str)
    {
        return self::createFromStringWithEncoding($str);
    }

    /**
     * Create a DOM document from a string with an externally provided character encoding
     *
     * @param string $str             String
     * @param string|null $encoding   Character encoding (e.g. from an HTTP header)
     *
     * @return DOMDocument DOM document
     */
    protected static function createFromStringWithEncoding($str, $encoding = null)
    {
        $dom = new DomainDom(new HtmlParser());

        return $dom->load($str, $encoding);
    }

    /**
     * Create a DOM document via the PHP stream wrapper
     *
     * @param string $url    URL
     * @param array<string, mixed> $options Connection options
     *
     * @return DOMDocument DOM document
     * @throws RuntimeException If the resource can't be retrieved
     */
    protected static function createViaStreamWrapper($url, array $options = [])
    {
        $clientOptions  = isset($options['client']) ? (array)$options['client'] : [];
        $opts           = array_replace_recursive([
            'http' => [
                'method'           => 'GET',
                'protocol_version' => 1.1,
                'user_agent'       => self::USER_AGENT,
                'max_redirects'    => 10,
                'timeout'          => 10.0,
                'header'           => "Accept-language: en\r\n",
            ]
        ], $clientOptions);
        $context        = stream_context_create($opts);
        $requestOptions = isset($options['request']) ? (array)$options['request'] : [];
        stream_context_set_params($context, $requestOptions);

        $handle   = strlen($url) ? @fopen($url, 'rb', false, $context) : false;
        $response = false;
        $metaData = [];
        if ($handle !== false) {
            $response = stream_get_contents($handle);
            $metaData = stream_get_meta_data($handle);
            fclose($handle);
        }

        // If the resource couldn't be retrieved
        if ($response === false) {
            $error = error_get_last();
            throw new RuntimeException(
                sprintf('Failed to retrieve "%s"%s', $url, $error ? ': ' . $error['message'] : '')
            );
        }

        // Determine the character encoding from the last Content-Type header (in case of redirects)
        $encoding = null;
        foreach ((isset($metaData['wrapper_data']) ? (array)$metaData['wrapper_data'] : []) as $header) {
            if (is_string($header) && !strncasecmp($header, 'Content-Type:', 13)) {
                $encoding = self::getContentTypeCharset(substr($header, 13));
            }
        }

        return self::createFromStringWithEncoding($response, $encoding);
    }

    /**
     * Extract the charset parameter of a Content-Type header value
     *
     * @param string $contentType Content-Type header value
     *
     * @return string|null Charset
     */
    protected static function getContentTypeCharset($contentType)
    {
        return preg_match('/;\s*charset\s*=\s*["\']?([^"\';\s]+)/i', (string)$contentType, $match) ?
            $match[1] : null;
    }
}
