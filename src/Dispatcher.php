<?php


namespace App;


use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Dispatcher implements RequestHandlerInterface
{

    /**
     * @var array
     */
    private $middlewares;

    /**
     * @var int
     */
    private $index;

    /**
     * @var Response
     */
    private $response;

    public function __construct()
    {
        $this->middlewares = [];
        $this->index = 0;
        $this->response = new Response();
    }

    /**
     * @param callable|MiddlewareInterface $middleware
     */
    public function pipe($middleware){
        $this->middlewares [] = $middleware;
    }

    /**
     * Permet d'exécuté les middleware récurcivement avec un systeme de dispatcher qui créer les middleware
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if(!$middleware = $this->getMiddleware()){
            return $this->response;
        }
        $this->index++;
        if(is_callable($middleware)) {
            return $middleware($request, $this->response, [$this, 'handle']);
        }elseif($middleware instanceof MiddlewareInterface){
           return  $middleware->process($request, $this);
        }
    }

    /**
     * @return mixed|null
     */
    private function getMiddleware()
    {
        if(isset($this->middlewares[$this->index])){
            return $this->middlewares[$this->index];
        }
        return null;
    }

}