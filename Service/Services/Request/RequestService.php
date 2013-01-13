<?php
/**
 * Request Service Plugin
 *
 * @package      Molajo
 * @license      MIT
 * @copyright    2013 Amy Stephen. All rights reserved.
 */
namespace Molajo\Service\Services\Request;

use Symfony\Component\HttpFoundation\Request;

defined('MOLAJO') or die;

/**
 * Request Service Plugin
 *
 * @url http://symfony.com/doc/current/resources/http_foundation.html#accessing-request-data
 *
 * @author       Amy Stephen
 * @license      MIT
 * @copyright    2013 Amy Stephen. All rights reserved.
 * @since        1.0
 */
Class RequestService
{
    /**
     * @var \Symfony\Component\HttpFoundation\ParameterBag
     *
     *  public $attributes;
     *  public $request;
     *  public $query;
     *  public $cookies;
     *
     * @var \Symfony\Component\HttpFoundation\ServerBag
     *
     *  public $server;
     *
     * @var \Symfony\Component\HttpFoundation\FileBag
     *
     *  public $files;
     *
     * @var \Symfony\Component\HttpFoundation\HeaderBag
     *
     *  public $headers;
     */
    protected $symfony_request;

    /**
     * Request
     *
     * @var    object  Request
     * @since  1.0
     */
    protected $request = null;

    /**
     * Calling Class
     *
     * @var    string
     * @since  1.0
     */
    protected $calling_class;

    /**
     * Calling Method
     *
     * @var    string
     * @since  1.0
     */
    protected $calling_method;

    /**
     * List of Properties
     *
     * @var    object
     * @since  1.0
     */
    protected $property_array = array(
        'calling_class',
        'calling_method',
        'id',
        'method',
        'mimetype',
        'post_variables',
        'is_secure'
    );

    /**
     * Class Constructor
     *
     * @since   1.0
     */
    public function __construct()
    {
        $trace = debug_backtrace();

        if (isset($trace[1])) {

            $this->set('calling_class', $trace[1]['class']);
            $this->set('calling_method', $trace[1]['function']);
        }

        return;
    }

    /**
     * Initialise
     *
     * return  object  Request
     *
     * @since  1.0
     */
    public function initialise()
    {
        $this->request = new \stdClass();

        $request_class         = '\\Symfony\\Component\\HttpFoundation\\Request';
        $connection            = new $request_class();
        $this->symfony_request = $connection->createFromGlobals();

        $this->setRequest();

        return $this;
    }

    /**
     * Create a request object or set a parameter value
     *
     * @param   string  $key    md5 name uniquely identifying content
     * @param   mixed   $value  Data to be serialized and then saved as cache
     *
     * @return  mixed
     * @since   1.0
     */
    public function set($key, $value)
    {
        $key = strtolower($key);

        if (in_array($key, $this->property_array)) {
            $this->$key = $value;

            return $this->$key;
        }

        $this->request->$key = $value;

        return $this->request->$key;
    }

    /**
     * Retrieve request oboject or value of Key
     *
     * @param   string  $key
     * @param   null    $default
     *
     * @return  bool|mixed
     * @since   1.0
     */
    public function get($key, $default = null)
    {
        $key = strtolower($key);

        if (isset($this->request->$key)) {
            return $this->request->$key;
        }

        if (in_array($key, $this->property_array)) {
            if ($this->$key === null) {
                $this->$key = $default;
            }

            return $this->$key;
        }

        if ($this->request->$key === null) {
            $this->request->$key = $default;
        }

        return $this->request->$key;
    }

    /**
     * Request values for port, scheme, host, method and base URL from Symfony2 HTTP Foundation
     *
     * @return  boolean
     * @since   1.0
     */
    public function setRequest()
    {
        /** http://localhost/molajo/index.php returns 'http' */
        $this->set('scheme', $this->symfony_request->getScheme());

        /** http://localhost/molajo/index.php returns 'http' */
        $this->set('is_secure', $this->symfony_request->isSecure());

        /** http://localhost:99/molajo/index.php returns http:://localhost:99 (non-standard port) */
        $this->set('host', $this->symfony_request->headers->get('host'));

        /** http://localhost/molajo/index.php returns '/molajo' */
        $this->set('base_path', $this->symfony_request->getBasePath());

        /** http://localhost/molajo/index.php returns '/molajo' */
        $this->set('base_url', $this->symfony_request->getBaseURL());

        /** http://localhost/molajo/index.php returns 80 */
        $this->set('port', $this->symfony_request->getPort());

        /** http://localhost/molajo/index.php return http:://localhost */
        /** http://localhost/molajo:88/index.php return http:://localhost:88 */
        $this->set('http_host', $this->symfony_request->getHttpHost());

        /** http://localhost/molajo/index.php returns 80 */
        /** http://site1/admin/articles/ returns /admin/articles/ */
        $this->set('request_uri', $this->symfony_request->getRequestUri());

        /** http://localhost/molajo/index.php returns 80 */
        $this->set('uri', $this->symfony_request->getUri());

        /** POST: Create GET: Read PUT: Update, DELETE: Delete */
        /** Many browsers do not support PUT or DELETE, $_SERVER['REQUEST_METHOD] supplements */
        $this->set('method', $this->symfony_request->getMethod());

        /** http://localhost/molajo/index.php returns 80 */
        $this->set('port', $this->symfony_request->getPort());

        $this->set('path_info', $this->symfony_request->getPathInfo());

        $this->set('query_string', $this->symfony_request->getQueryString());

        if ($this->symfony_request->getQueryString() == '') {
            $tempQueryParameters = array();
        } else {
            $tempQueryParameters = explode('&', $this->get('query_string'));
        }

        $query_parameters = array();
        if (count($tempQueryParameters) > 0) {
            foreach ($tempQueryParameters as $item) {
                $pair                       = explode('=', $item);
                $query_parameters[$pair[0]] = $pair[1];
            }
        }
        $this->set('query_parameters', $query_parameters);

        /** http://localhost/molajo/index.php returns '/molajo/index.php' */
        /** http://site1/admin/articles returns 'site1' */
        $this->set('base_url_path', $this->get('http_host') . $this->get('base_url'));

        /** http://localhost/molajo/index.php returns 'http://molajo/index.php' */
        /** http://site1/admin/articles returns 'http://site1' */
        $this->set(
            'base_url_path_with_scheme',
            $this->get('scheme')
                . '://'
                . $this->get('http_host')
                . $this->get('base_url')
        );

        $this->set('ajax', $this->symfony_request->isXmlHttpRequest());

        $this->set('request_format', $this->symfony_request->getRequestFormat());

        $this->set('mimetype', $this->symfony_request->getMimeType($this->get('request_format')));

        $this->set('format', $this->symfony_request->getFormat($this->get('mimetype')));

        $this->set('content_type', $this->symfony_request->getContentType());

        /** Client */
        $this->set('client_ip', $this->symfony_request->getClientIp());

        /** Server */
        $this->set('user', $this->symfony_request->getUser());

        $this->set('password', $this->symfony_request->getPassword());

        $this->set('document_root', $this->symfony_request->server->get('DOCUMENT_ROOT'));

        $this->set('entry_point', $this->symfony_request->server->get('SCRIPT_FILENAME'));

        /** Post */
        $this->set('post_variables', $this->symfony_request->request->all());

        /** Language */

        return true;
    }
}
