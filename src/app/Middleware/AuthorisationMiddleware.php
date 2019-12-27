<?php

namespace App\Middleware;

use App\Exceptions\NotAuthenticatedException;
use App\Exceptions\NotAuthorizedException;
use Casbin\Enforcer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use \Firebase\JWT\JWT;
use Exception;

class AuthorisationMiddleware implements MiddlewareInterface
{

    /**
     * @var Enforcer
     */
    private $enforcer;

    /**
     * Retrieve the Authorization header from the HTTP request
     *
     * @param ServerRequestInterface $request
     * @return string
     * @throws NotAuthenticatedException
     */
    private function getAuthenticationHeader(ServerRequestInterface $request): string
    {
        $authHeader = $request->getHeader('authorization');
        if (empty($authHeader)) {
            throw new NotAuthenticatedException('Missing authentication token');
        }
        return $authHeader[0];
    }

    /**
     * Retrieve just the JWT from the value passed (strip out "Bearer")
     * @param string $authorizationHeader
     * @return string
     * @throws NotAuthenticatedException
     */
    private function getTokenFromHeader(string $authorizationHeader): string
    {
        if (preg_match('/Bearer\s(\S+)/', $authorizationHeader, $matches)) {
            return $matches[1];
        }
        throw new NotAuthenticatedException('Invalid header');
    }

    /**
     * Decode the JWT and return the payload as an associative array
     * @param string $jwt
     * @return array
     * @throws NotAuthenticatedException
     */
    private function getPayloadFromJWT(string $jwt): array
    {
        try {
            return (array)JWT::decode($jwt, 'password', array('HS256'));
        } catch (Exception $e) {
            throw new NotAuthenticatedException('Token not valid');
        }
    }

    /**
     * Return the user from the payload
     *
     * @param array $payload
     * @return string
     * @throws NotAuthenticatedException
     */
    private function getUserFromPayload(array $payload): string
    {
        if (isset($payload['sub']) && !empty($payload['sub'])) {
            return $payload['sub'];
        }
        throw new NotAuthenticatedException('Invalid payload');
    }

    /**
     * @param ServerRequestInterface $request
     * @return string
     * @throws NotAuthenticatedException
     */
    public function getAuthenticatedUser(ServerRequestInterface $request): string
    {
        $authHeader = $this->getAuthenticationHeader($request);

        $jwt = $this->getTokenFromHeader($authHeader);

        $payload = $this->getPayloadFromJWT($jwt);

        $user = $this->getUserFromPayload($payload);

        return $user;

    }

    /**
     * @param ServerRequestInterface $request
     * @return bool
     * @throws BadHTTPMethodException
     * @throws NotAuthenticatedException
     * @throws \Casbin\Exceptions\CasbinException
     */
    private function isAuthorized(ServerRequestInterface $request): bool
    {
        $subject = $this->getAuthenticatedUser($request);

        $object = ltrim($request->getUri()->getPath(), '/');

        $verbMapping = [
            'GET'       => 'list',
            'POST'      => 'create',
            'PUT'       => 'update',
            'PATCH'     => 'update',
            'DELETE'    => 'delete'
        ];

        $requestMethod = $request->getMethod();

        $action = $verbMapping[$requestMethod];

        $allowed = $this->enforcer->enforce($subject, $object, $action);

        return $allowed;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        if ($this->isAuthorized($request)) {
            return $handler->handle($request);
        }
        throw new NotAuthorizedException('Not authorized');
    }

    /**
     * Fluent setter
     * @param Enforcer $enforcer
     * @return $this
     */
    public function setAuthService(Enforcer $enforcer)
    {
        $this->enforcer = $enforcer;
        return $this;
    }
}