## Curl

A basic CURL wrapper for PHP (see http://php.net/curl for more information about the libcurl extension for PHP)

### Installation
This library is available via [Composer](https://getcomposer.org/)

```json
{
    "require": {
        "alioygur/curl": "~1.0"
    }
}
```

## Usage

### Simple example of usage

Simply initialize and usage the `Curl` class like so:

```php
<?php
use Alioygur\Curl\Curl;

$curl = new Curl();

$response = $curl
    ->setOption('CURLOPT_FOLLOW_REDIRECTS', false)
    ->setHeader('User-Agent', 'My Name is Heisenberg!')
    ->get('http://example.com');
```

### Performing a Request

The Curl object supports 5 types of requests: HEAD, GET, POST, PUT, and DELETE. You must specify a url to request and optionally specify an associative array or string of variables to send along with it.

```php
$response = $curl->head($url, $vars = []);
$response = $curl->get($url, $vars = []); # The Curl object will append the array of $vars to the $url as a query string
$response = $curl->post($url, $vars = []);
$response = $curl->put($url, $vars = []);
$response = $curl->delete($url, $vars = []);
```

To use a custom request methods, you can call the `request` method:

```php
$response = $curl->request('YOUR_CUSTOM_REQUEST_TYPE', $url, $vars = []);
```

All of the built in request methods like `put` and `get` simply wrap the `request` method. For example, the `post` method is implemented like:

```php
function post($url, $vars = []) {
    return $this->request('POST', $url, $vars);
}
```

Examples:

```php
$response = $curl->get('google.com?q=test');

# The Curl object will append '&some_variable=some_value' to the url
$response = $curl->get('google.com?q=test', array('some_variable' => 'some_value'));

$response = $curl->post('test.com/posts', array('title' => 'Test', 'body' => 'This is a test'));
```

All requests return a CurlResponse object (see below) or false if an error occurred. You can access the error string with the `$curl->error()` method.


### The CurlResponse Object

A normal CURL request will return the headers and the body in one response string.

```php
# Response Headers -------------------------------------------------------------------------

# Get the response body
echo $response->body(); # A string containing everything in the response except for the headers

# Get the response headers
print_r($response->headers()); # An associative array containing the response headers

# Pick one from response headers
echo $response->headers('Content-Type'); # text/html 

# You can also use those methods 
$response->status(); # 200 OK
$response->statusCode(); # 200
$response->ContentType(); # text/html

# Request Headers --------------------------------------------------------------------------

# Get the request headers
$response->requestHeaders(); # An associative array containing the request headers

# Pick one from request headers 
echo $response->requestHeaders('Version'); # HTTP/1.1

# Curl Information -------------------------------------------------------------------------
Get information regarding a specific transfer. See, http://php.net/manual/en/function.curl-getinfo.php

# Get all
$response->getInfo(); # An associative array containing the curl information

# Pick one
$response->getInfo('total_time'); # 0.14257
``` 
	
The CurlResponse class defines the magic [__toString()](http://php.net/__toString) method which will return the response body, so `echo $response` is the same as `echo $response->body`


### Cookie Sessions

By default, cookies will be stored in a file called `curl_cookie.txt`. You can change this file's name by setting it like this

```php
$curl->setCookieFile('some_other_filename');
```

This allows you to maintain a session across requests

### Setting Custom Headers

You can set custom headers to send with the request

```php
$curl->setheader('SOME_KEY', 'some value');

# you can also method chaining
$response = $curl->setHeader('Content-Type', 'application/json')
     ->setHeader('User-Agent', 'Mozilla/5.0 (X11; Linux...')
     ->get('http://example.com');
```

### Setting Custom CURL request options

By default, the `Curl` object will follow redirects. You can disable this by setting:

```php
$curl->setOptions('CURLOPT_FOLLOW_REDIRECTS', false);
```

You can set/override many different options for CURL requests (see the [curl_setopt() documentation](http://php.net/curl_setopt) for a list of them)

### Auth
Sets the user and password for HTTP auth basic authentication method.

```php
$curl->setAuth('username', 'password');
```

## Contact

Problems, comments, and suggestions all welcome: [alioygur@gmail.com](mailto:alioygur@gmail.com)