<?php

// load de l'autoloder composer
use App\CsrfMiddleware;
use App\InvalidCsrfException;
use App\NoCsrfException;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use function Http\Response\send;

require '../vendor/autoload.php';

/**Redirect Middleware *
 * @param ServerRequestInterface $request
 * @param ResponseInterface $response
 * @param callable $next
 * @return ResponseInterface
 */
$redirectMiddleware = function (ServerRequestInterface $request, ResponseInterface $response, callable $next){
    $path = (string)$request->getUri()->getPath();
    switch ($path){
        case"/contact-form":
            return $response->withHeader('Location', '/form.php')
                ->withStatus(301);
            break;
        default:
            return $next($request, $response);
    }
};

/** CSRF Middleware **/
$csrfMiddleware =  new CsrfMiddleware();

/** APP Middleware *
 * @param ServerRequestInterface $request
 * @param ResponseInterface $response
 * @param callable $next
 * @return ResponseInterface
 */
$app = function (ServerRequestInterface $request, ResponseInterface $response, callable $next){
    $response->getBody()->write('Hello world');
    return $response;
};

/**
 * allows you to create a query from global variables $_GET, $_POST, ..
 */
$request = ServerRequest::fromGlobals();

$dispatcher = new \App\Dispatcher();
$dispatcher->pipe($redirectMiddleware);
$dispatcher->pipe($csrfMiddleware);
$dispatcher->pipe($app);


try {
    send($dispatcher->handle($request));
}catch(NoCsrfException $noCsrfException){
    echo $noCsrfException->getMessage();
}catch (InvalidCsrfException $invalidCsrfException){
    echo $invalidCsrfException->getMessage();
}
