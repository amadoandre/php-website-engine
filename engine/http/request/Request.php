<?php

namespace engine\http\request;

use engine\utils\Sanitizer;

use function PHPSTORM_META\type;

class Request implements IRequest
{
    // contains the URL of the request
    protected string $url;

    // contains the request method of the request : GET | POST
    protected string $method;

    protected string $domain;

    protected string $action;

    // contains the applicationtype of request
    protected ?ResquestApplicationType $applicationType;

    // contains the lang / indirect / get / post / cookie parameters
    protected object $parameters;


    /*
     * Prevent this class from being called 'non-ally'
     */
    protected function __construct(string $url, string $method, ?ResquestApplicationType $applicationType, string $domain, string  $action, object $parameters)
    {
        $this->url = $url;
        $this->method = $method;
        $this->applicationType = $applicationType;
        $this->parameters = $parameters;
        $this->domain = $domain;
        $this->action = $action;
    }

    /*
     * Returns the Request object, so it can be used as a dependency
     */
    public static function capture(): IRequest
    {

        $url = Sanitizer::sanitizeURL(rtrim(substr($_SERVER["REQUEST_URI"], 1), '/'));
        $method = $_SERVER['REQUEST_METHOD'];
        $segments = array_diff(explode('/', $url), array(''));
        $applicationType = ResquestApplicationType::tryfrom($segments[0] ?? 'web');
        $parameters = (object) array();

        if ($applicationType == ResquestApplicationType::WEB || $applicationType == ResquestApplicationType::API) {
            $domain = $segments[1] ?? 'main';
            $action = $segments[2] ?? 'get';
            $parameters->indirectParameters = array_values(array_diff(array_slice($segments, 3), array('')));
        }else{
            $domain = '';
            $action = '';
        }

        $parameters->getParameters = self::filterParameters(filter_input_array(INPUT_GET));
        $parameters->postParameters = self::filterParameters(filter_input_array(INPUT_POST));
        $parameters->filesParameters = self::filterParameters($_FILES);
        $parameters->cookieParameters = self::filterParameters(filter_input_array(INPUT_COOKIE));

        return new Request($url, $method, $applicationType, $domain, $action, $parameters);
    }
    private static function filterParameters($param)
    {
         $ret = null;
        if (!is_null($param)) {
            foreach ($param as $parameter => $value) {
                if (!isset($ret)) {
                    $ret = (object) array();
                }

                $ret->{$parameter} = $value;
            }
        }
        return $ret;
    }

    public function getResquestApplicationType(): ?ResquestApplicationType
    {
        return $this->applicationType;
    }
    public function getDomain(): string
    {
        return $this->domain;
    }
    public function getAction(): string
    {
        return $this->action;
    }
    public function getParameters(): object
    {
        return $this->parameters;
    }
    public function getMethod(): string
    {
        return $this->method;
    }
}
