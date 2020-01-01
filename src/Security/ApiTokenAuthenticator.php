<?php

namespace App\Security;

use App\Repository\ApiTokenRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class ApiTokenAuthenticator extends AbstractGuardAuthenticator
{
    private $apiTokenRepo;
    private $userRepo;
    private $passwordEncoder;

    public function __construct(ApiTokenRepository $apiTokenRepo, UserRepository $userRepo, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->apiTokenRepo = $apiTokenRepo;
        $this->userRepo = $userRepo;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function supports(Request $request)
    {
        // look for header "Authorization: Bearer <token>" or login authentication
        return ($request->headers->has('Authorization')
            && 0 === strpos($request->headers->get('Authorization'), 'Bearer '))
            || (strpos($request->headers->get('referer'), '/login') !== false && $request->isMethod('POST'));
    }

    public function getCredentials(Request $request)
    {
        $authorizationHeader = $request->headers->get('Authorization');
        if (!$authorizationHeader) {
            return json_decode($request->getContent(), true); // return login credentials
        }

        // skip beyond "Bearer and return token"
        return substr($authorizationHeader, 7);
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        if (is_array($credentials)) {
            //TODO: check if email and password are valid before checking db
            $user = $this->userRepo->findOneBy([
                'email' => $credentials['email']
            ]);
            if (!$user instanceof UserInterface) {
                throw new CustomUserMessageAuthenticationException(
                    'Invalid User Mail'
                );
            }

            $pass = $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
            if (!$pass){
                throw new CustomUserMessageAuthenticationException(
                    'Invalid User Password'
                );
            }
            return $user;
        }

        $token = $this->apiTokenRepo->findOneBy([
            'token' => $credentials
        ]);

        if (!$token) {
            throw new CustomUserMessageAuthenticationException(
                'Invalid API Token'
            );
        }

        if ($token->isExpired()) {
            throw new CustomUserMessageAuthenticationException(
                'Token expired'
            );
        }

        return $token->getUser();
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new JsonResponse([
            'message' => $exception->getMessageKey()
        ], 401);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // allow the authentication to continue
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        throw new \Exception('Not used: entry_point from other authentication is used');
    }

    public function supportsRememberMe()
    {
        return false;
    }
}
