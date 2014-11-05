<?php namespace Alioygur\Curl;

/**
 * A basic CURL wrapper
 *
 * See the README for documentation/examples or http://php.net/curl for more information about the libcurl extension for PHP
 *
 * @package curl
 * @author Sean Huber <shuber@huberry.com>
 * @author Ali OYGUR <alioygur@gmail.com>
 **/
class Curl
{

    /**
     * The file to read and write cookies to for requests
     *
     * @var string
     **/
    private $cookie_file;

    /**
     * An associative array of headers to send along with requests
     *
     * @var array
     **/
    private $headers = [];

    /**
     * An associative array of CURLOPT options to send along with requests
     *
     * @var array
     **/
    private $options = [];

    /**
     * Stores resource handle for the current CURL request
     *
     * @var resource
     **/
    private $request;

    /**
     * Stores the HTTP basic auth credentials
     *
     * @var string
     **/
    private $basic_auth_credentials;

    /**
     * Stores user agent
     * @var string
     */
    private $user_agent;

    /**
     * Initializes a Curl object
     *
     * Sets the $cookie_file to "curl_cookie.txt" in the current directory
     * Also sets the $user_agent to $_SERVER['HTTP_USER_AGENT'] if it exists, 'Curl/PHP '.PHP_VERSION.' (https://github.com/alioygur/curl)' otherwise
     **/
    public function __construct()
    {
        $this->cookie_file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'curl_cookie.txt';
        $this->user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Curl/PHP ' . PHP_VERSION . ' (https://github.com/alioygur/curl)';
    }

    /**
     * Returns an associative array of curl options
     * currently configured.
     *
     * @return array Associative array of curl options
     */
    public function getRequestOptions()
    {
        return curl_getinfo($this->request);
    }

    /**
     * Set the associated CURL options for a request method
     *
     * @param string $method
     * @return void
     * @access protected
     **/
    protected function setRequestMethod($method)
    {
        switch (strtoupper($method)) {
            case 'HEAD':
                curl_setopt($this->request, CURLOPT_NOBODY, true);
                break;
            case 'GET':
                curl_setopt($this->request, CURLOPT_HTTPGET, true);
                break;
            case 'POST':
                curl_setopt($this->request, CURLOPT_POST, true);
                break;
            default:
                curl_setopt($this->request, CURLOPT_CUSTOMREQUEST, $method);
        }
    }

    /**
     * Sets the CURLOPT options for the current request
     *
     * @param string $url
     * @param string $vars
     * @return void
     * @access protected
     **/
    protected function setRequestOptions($url, $vars)
    {
        curl_setopt($this->request, CURLOPT_URL, $url);
        if (!empty($vars)) curl_setopt($this->request, CURLOPT_POSTFIELDS, $vars);

        # Set some default CURL options
        curl_setopt($this->request, CURLINFO_HEADER_OUT, true);
        curl_setopt($this->request, CURLOPT_HEADER, true);
        curl_setopt($this->request, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->request, CURLOPT_USERAGENT, $this->user_agent);
        if ($this->cookie_file) {
            curl_setopt($this->request, CURLOPT_COOKIEFILE, $this->cookie_file);
            curl_setopt($this->request, CURLOPT_COOKIEJAR, $this->cookie_file);
        }
        if ($this->basic_auth_credentials) {
            curl_setopt($this->request, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($this->request, CURLOPT_USERPWD, $this->basic_auth_credentials);
        } else {
            curl_setopt($this->request, CURLOPT_HTTPAUTH, false);
        }
        # Set any custom CURL options
        foreach ($this->options as $option => $value) {
            curl_setopt($this->request, constant($option), $value);
        }
    }

    /**
     * Formats and adds custom headers to the current request
     *
     * @return void
     * @access protected
     **/
    protected function setRequestHeaders()
    {
        $headers = array();
        foreach ($this->headers as $key => $value) {
            $headers[] = $key . ': ' . $value;
        }
        curl_setopt($this->request, CURLOPT_HTTPHEADER, $headers);
    }

    /**
     * Set request header
     *
     * @param $key
     * @param $value
     * @return $this
     */
    public function setHeader($key, $value)
    {
        $this->headers[$key] = $value;

        return $this;
    }

    /**
     * Set curl options
     *
     * @param $key
     * @param $value
     * @return $this
     */
    public function setOption($key, $value)
    {
        $this->options[$key] = $value;

        return $this;
    }

    /**
     * Set the cookie file path
     * @param $file
     * @return $this
     */
    public function setCookieFile($file)
    {
        $this->cookie_file = $file;
        return $this;
    }


    /**
     * Makes an HTTP GET request to the specified $url with an optional array or string of $vars
     *
     * Returns a CurlResponse object if the request was successful, false otherwise
     *
     * @param string $url
     * @param array|string $vars
     * @return CurlResponse
     **/
    public function get($url, $vars = array())
    {
        if (!empty($vars)) {
            $url .= (stripos($url, '?') !== false) ? '&' : '?';
            $url .= (is_string($vars)) ? $vars : http_build_query($vars, '', '&');
        }
        return $this->request('GET', $url);
    }

    /**
     * Makes an HTTP HEAD request to the specified $url with an optional array or string of $vars
     *
     * Returns a CurlResponse object if the request was successful, false otherwise
     *
     * @param string $url
     * @param array|string $vars
     * @return CurlResponse
     **/
    public function head($url, $vars = array())
    {
        return $this->request('HEAD', $url, $vars);
    }

    /**
     * Makes an HTTP POST request to the specified $url with an optional array or string of $vars
     *
     * @param string $url
     * @param array|string $vars
     * @return CurlResponse|boolean
     **/
    public function post($url, $vars = array(), $enctype = NULL)
    {
        return $this->request('POST', $url, $vars, $enctype);
    }

    /**
     * Makes an HTTP PUT request to the specified $url with an optional array or string of $vars
     *
     * Returns a CurlResponse object if the request was successful, false otherwise
     *
     * @param string $url
     * @param array|string $vars
     * @return CurlResponse|boolean
     **/
    public function put($url, $vars = array())
    {
        return $this->request('PUT', $url, $vars);
    }

    /**
     * Makes an HTTP DELETE request to the specified $url with an optional array or string of $vars
     *
     * Returns a CurlResponse object if the request was successful, false otherwise
     *
     * @param string $url
     * @param array|string $vars
     * @return CurlResponse object
     **/
    public function delete($url, $vars = array())
    {
        return $this->request('DELETE', $url, $vars);
    }

    /**
     * Makes an HTTP request of the specified $method to a $url with an optional array or string of $vars
     *
     * Returns a CurlResponse object if the request was successful, false otherwise
     *
     * @param string $method
     * @param string $url
     * @param array|string $vars
     * @param null $enctype
     * @throws CurlException
     * @return CurlResponse|boolean
     */
    public function request($method, $url, $vars = array(), $enctype = null)
    {
        $this->request = curl_init();
        if (is_array($vars) && $enctype != 'multipart/form-data') $vars = http_build_query($vars, '', '&');

        $this->setRequestMethod($method);
        $this->setRequestOptions($url, $vars);
        $this->setRequestHeaders();

        $response = curl_exec($this->request);

        if (!$response) {
            throw new CurlException(curl_error($this->request), curl_errno($this->request));
        }

        $response = new CurlResponse($response, $this->getRequestOptions());

        $this->close();

        return $response;
    }

    /**
     * Sets the user and password for HTTP auth basic authentication method.
     *
     * @param string $username
     * @param string|null $password
     * @return Curl
     */
    public function setBasicAuth($username, $password = null)
    {
        $this->basic_auth_credentials = $username . ':' . $password;
        return $this;
    }


    /**
     * Close the active curl connection
     *
     * @return void
     */
    private function close()
    {
        curl_close($this->request);
    }

    /**
     * @return string
     */
    public function getCookieFile()
    {
        return $this->cookie_file;
    }

}