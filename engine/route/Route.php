<?php

namespace engine\route;

use engine\http\request\IRequest;
use engine\http\response\Response;
use engine\http\request\ResquestApplicationType;
use engine\http\response\IResponse;
use engine\http\response\NotFoundResponse;
use RuntimeException;

/**
 * singleton Route
 */
class Route
{

    private static $instance;

    private array $routes = array();


    private static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Route();
        }
        return self::$instance;
    }

    protected function add(ResquestApplicationType $type, string $method, string $domain, string $action, $class, string $functionName, ?array $parametersConfig)
    {
        if (isset($this->routes[$type->value][$domain][$action][$method])) {
            throw new RuntimeException("Error Processing adding Route : $type->value, $method, $domain, $action, $class, $functionName,$parametersConfig - already existes", 1);
        } else {
            $this->routes[$type->value][$domain][$action][$method] = (object) array("class" => $class, "function" => $functionName, "parametersConfig" => $parametersConfig);
        }
    }

    protected function get(ResquestApplicationType $type, string $method, string $domain, string $action): ?object
    {
        return $this->routes[$type->value][$domain][$action][$method] ?? null;
    }





    private static function create(ResquestApplicationType $type, string $method, string $domain, string $action, $class, string $functionName, ?array $parametersConfig): void
    {
        self::getInstance()->add($type, $method, $domain, $action, $class, $functionName, $parametersConfig);
    }

    // API for IRoute operations
    public static function page(string $domain, string $action, $class, string $functionName, ?array $parametersConfig): void
    {
        self::create(ResquestApplicationType::WEB, 'GET', $domain, $action, $class, $functionName, $parametersConfig);
    }
    public static function ApiGet(string $domain, string $action, $class, string $functionName, ?array $parametersConfig): void
    {
        self::create(ResquestApplicationType::API, 'GET', $domain, $action, $class, $functionName, $parametersConfig);
    }
    public static function ApiPost(string $domain, string $action, $class, string $functionName, ?array $parametersConfig): void
    {
        self::create(ResquestApplicationType::API, 'POST', $domain, $action, $class, $functionName, $parametersConfig);
    }

    // API for processing Operations
    public static function process(IRequest $request): IResponse
    {
        if ($request->getResquestApplicationType() == null) {
            //show 404;
            //echo "</br>404</br>";
            return new NotFoundResponse();
        }
        $callArray =  self::getInstance()->get($request->getResquestApplicationType(), $request->getMethod(), $request->getDomain(), $request->getAction());
        if ($callArray == null) {
            //show 404 by type;
            return new NotFoundResponse();
        }
        $parsedParameters = self::processConfigs($callArray->parametersConfig, $request);

        return call_user_func_array(array($callArray->class, $callArray->function), $parsedParameters);
    }


    private const indirect = "indirect-";
    private const get = "get-";
    private const post = "post";
    private const files = "files";

    private static function processConfigs(array $config, IRequest $request): array
    {

        return array_map(
            function ($element) use ($request) {
                $ind = str_starts_with($element, self::indirect) ? explode(self::indirect, $element)[1] : null;
                if ($ind) {
                    $ret = $request->getParameters()->indirectParameters[$ind - 1] ?? null;
                } else {
                    $get = str_starts_with($element, self::get) ? explode(self::get, $element)[1] : null;
                    if ($get) {
                        $ret = $request->getParameters()->getParameters->$get;
                    } else {
                        if ($element == self::post) {
                            $ret = $request->getParameters()->postParameters;
                        } else {
                            if ($element == self::files) {
                                $ret = $request->getParameters()->filesParameters;
                            } else {
                                $ret = null;
                            }
                        }
                    }
                }
                return $ret;
            },
            $config
        );
    }
}
