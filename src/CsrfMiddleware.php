<?php

namespace App;
session_start();
use ArrayAccess;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Del\SessionManager;

class CsrfMiddleware implements MiddlewareInterface
{

    public const HTTP_METHOD = ['POST', 'PUT', 'DELETE'];

    /**
     * @var string
     */
    private $sessionKey;

    /**
     * @var string
     */
    private $csrfKey;


    /**
     * Middleware constructor.
     * @param string $sessionKey
     * @param string $csrfKey
     */
    public function __construct(string $sessionKey = 'csrf.token', string $csrfKey = '_csrf')
    {
        SessionManager::sessionStart('Middleware');
        $this->csrfKey = $csrfKey;
        $this->sessionKey = $sessionKey;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws InvalidCsrfException
     * @throws NoCsrfException
     * @throws \Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if(in_array($request->getMethod(), self::HTTP_METHOD)){
            $params = $request->getParsedBody() ?: [];
            if(!array_key_exists($this->csrfKey, $params)){
                throw new NoCsrfException('_csrf token not found');
            }
            if($params[$this->csrfKey] !== SessionManager::get($this->sessionKey)){
                throw new InvalidCsrfException('invalid csrf token');
            }
            $this->removeToken();
            $response = new Response();
            $response->getBody()->write('Your message has been sent');
            return $response;
        }
         return $handler->handle($request);
    }


    /**
     * @return string
     */
    public function generateToken(): string
    {
        try {
            $token = bin2hex(random_bytes(16));
        } catch (\Exception $e) {
        }
        SessionManager::set($this->sessionKey, $token);
        return $token;
    }


    public function removeToken(): void
    {
        SessionManager :: destroy ( $this->sessionKey );
    }
}