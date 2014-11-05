<?php namespace Alioygur\Curl;

/**
 * Parses the response from a Curl request into an object containing
 * the response body and an associative array of headers
 *
 * @package curl
 * @author Sean Huber <shuber@huberry.com>
 * @author Ali OYGUR <alioygur@gmail.com>
 **/
class CurlResponse
{
    /**
     * The body of the response without the headers block
     *
     * @var string
     **/
    private $body = '';

    /**
     * An associative array containing the response's headers
     *
     * @var array
     **/
    private $headers = [];

    /**
     * An associative array containing the request's headers
     *
     * @var array
     */
    private $request_headers = [];

    /**
     * @var array
     */
    private $info;

    /**
     * Accepts the result of a curl request as a string
     *
     * @param string $response
     * @param array $options
     */
    public function __construct($response, array $options)
    {
        $this->info = $options;

        # Headers regex
        $pattern = '#HTTP/\d\.\d.*?$.*?\r\n\r\n#ims';

        # Extract headers from response
        preg_match_all($pattern, $response, $matches);
        $headers_string = array_pop($matches[0]);
        $headers = explode("\r\n", str_replace("\r\n\r\n", '', $headers_string));

        # Inlude all received headers in the $headers_string
        while (count($matches[0])) {
            $headers_string = array_pop($matches[0]) . $headers_string;
        }

        # Remove all headers from the response body
        $this->body = str_replace($headers_string, '', $response);

        # Extract the version and status from the first header
        $version_and_status = array_shift($headers);
        preg_match_all('#HTTP/(\d\.\d)\s((\d\d\d)\s((.*?)(?=HTTP)|.*))#', $version_and_status, $matches);
        $this->headers['Http-Version'] = array_pop($matches[1]);
        $this->headers['Status-Code'] = array_pop($matches[3]);
        $this->headers['Status'] = array_pop($matches[2]);

        # Convert headers into an associative array
        foreach ($headers as $header) {
            preg_match('#(.*?)\:\s(.*)#', $header, $matches);
            $this->headers[$matches[1]] = $matches[2];
        }

        # Extract headers from request
        $this->extractHeaderFromRequest();
    }

    /**
     * Returns the specified header or all
     *
     * @param null|string $key the header name (case sensitive)
     * @return array|string|null
     */
    public function headers($key = null)
    {
        if($key) {
            return isset($this->headers[$key])? $this->headers[$key] : null;
        }

        return $this->headers;
    }

    /**
     * Returns the specified request header or all
     *
     * @param null|string $key the header name (case sensitive)
     * @return array|string|null
     */
    public function requestHeaders($key = null)
    {
        if($key) {
            return isset($this->request_headers[$key])? $this->request_headers[$key] : null;
        }

        return $this->request_headers;
    }

    /**
     * Returns the response body
     *
     * @return mixed|string
     */
    public function body()
    {
        return $this->body;
    }

    /**
     * Returns http status phrase
     *
     * @return null|string
     */
    public function status()
    {
        return $this->headers('Status');
    }

    /**
     * Returns http status code
     *
     * @return null|string
     */
    public function statusCode()
    {
        return $this->headers('Status-Code');
    }

    /**
     * Returns content type
     *
     * @return null|string
     */
    public function contentType()
    {
        return $this->headers('Content-Type');
    }

    /**
     * Returns the specified information or all
     *
     * @param null|string $key the options name (case sensitive)
     * @return array|string|null
     */
    public function getInfo($key = null)
    {
        if($key) {
            return isset($this->info[$key])? $this->info[$key] : null;
        }

        return $this->info;
    }
    /**
     * Extract headers from request
     */
    private function extractHeaderFromRequest()
    {
        $headers = explode("\r\n", str_replace("\r\n\r\n", '', $this->getInfo('request_header')));
        $headers = array_filter($headers);

        $firstLineOfHeader = explode(' ', $headers[0], 3);
        if (count($firstLineOfHeader) == 3) {
            $this->request_headers['Method'] = $firstLineOfHeader[0];
            $this->request_headers['Path'] = $firstLineOfHeader[1];
            $this->request_headers['Version'] = $firstLineOfHeader[2];
        }

        foreach ($headers as $header) {
            $pieces = explode(':', $header, 2);

            if (count($pieces) == 2) {
                $this->request_headers[$pieces[0]] = $pieces[1];
            }
        }

        $this->request_headers = array_map('trim', $this->request_headers);
    }

    /**
     * Returns the response body
     *
     * <code>
     * $curl = new Curl;
     * $response = $curl->get('google.com');
     * echo $response;  # => echo $response->body();
     * </code>
     *
     * @return string
     **/
    public function __toString()
    {
        return $this->body;
    }
}