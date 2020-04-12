<?php

namespace App;
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
     * @var array|\ArrayAccess
     */
    private $session;

    /**
     * @var string
     */
    private $sessionKey;

    /**
     * @var string
     */
    private $csrfKey;

    /**
     * @var int
     */
    private $limit;


    /**
     * Middleware constructor.
     * @param $session
     * @param int $limit
     * @param string $sessionKey
     * @param string $csrfKey
     */
    public function __construct(&$session, int $limit = 50, string &$sessionKey = 'csrf.token', string $csrfKey = '_csrf')


    {
       // SessionManager::sessionStart('Middleware');
        $this->testSession($session);
        $this->session = &$session;
        $this->csrfKey = $csrfKey;
        $this->sessionKey = $sessionKey;
        $this->limit = $limit;
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
            if(!\in_array($params[$this->csrfKey], $this->session[$this->sessionKey] ?? [], true)){
                throw new InvalidCsrfException('invalid csrf token');
            }
            $this->removeToken($params[$this->csrfKey]);
            $response = new Response();
            $response->getBody()->write('Your message has been sent');
            return $response;
        }
         return $handler->handle($request);
    }


    /**
     * @return string
     * @throws \Exception
     */
    public function generateToken(): string
    {
        $token = bin2hex(random_bytes(16));
        $tokens = $this->session[$this->sessionKey] ?? [];
        $tokens[] = $token;
        $this->session[$this->sessionKey] = $this->limitTokens($tokens);

        return $token;
    }

    /**
     *  Remove a token from session.
     * @param string $token
     */
    public function removeToken(string $token): void
    {
        //SessionManager :: destroy ( $this->sessionKey );
        $this->session[$this->sessionKey] = array_filter(
            $this->session[$this->sessionKey] ?? [],
            function ($t) use ($token) {
                return $token !== $t;
            }
        );
    }

    /**
     * Test if the session acts as an array.
     *
     * @param $session
     *
     * @throws \TypeError
     */
    private function testSession($session): void
    {
        if (!\is_array($session) && !$session instanceof \ArrayAccess) {
            throw new \TypeError('session is not an array');
        }
    }

    /**
     * @return string
     */
    public function getSessionKey(): string
    {
        return $this->sessionKey;
    }

    /**
     * @return string
     */
    public function getFormKey(): string
    {
        return $this->csrfKey;
    }

    /**
     * Limit the number of tokens.
     *
     * @param array $tokens
     *
     * @return array
     */
    private function limitTokens(array $tokens): array
    {
        if (\count($tokens) > $this->limit) {
            array_shift($tokens);
        }

        return $tokens;
    }
}