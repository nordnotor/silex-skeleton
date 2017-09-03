<?php

namespace App\Providers\Rbac\Tokens;

use App\Application;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class SessionToken
 * @package App\Providers\Rbac\Tokens
 *
 * Use cookie name:
 * _su - security user (db user model id)
 * _sus - security user sign
 *
 * @property string $secretKey
 * @property string $permanent
 */
class SessionToken extends AbstractToken
{
    private $credentials;

    public $secretKey;
    public $permanent;

    public function __construct(array $config)
    {
        $this->secretKey = $config['secret_key'];
        $this->permanent = $config['permanent'];
    }

    public function handle(Request $request): bool
    {
        if (!$request->hasSession()) {
            $request->setSession(new Session());
        }

        $session = $request->getSession();
        $user = $session->get('security.user');

        if (!empty($user) && is_array($user)) {
            $this->credentials = $this->setAttributes($user);
            return true;
        }

        if (!$request->cookies->has('_su')) {
            return false;
        }

        $id = $request->cookies->get('_su');
        $sign = $request->cookies->get('_sus');

        # check sign
        if (sha1($id . $this->secretKey) !== $sign) {
            return false;
        }

        $this->credentials = $id;

        return true;
    }

    public function store(Request $request, Response $response): bool
    {
        $user = $this->getUser();

        if (!$request->hasSession()) {
            $request->setSession(new Session());
        }

        $session = $request->getSession();

        # clear
        if (!isset($user, $user->_id)) {
            $response->headers->clearCookie('_su');
            $response->headers->clearCookie('_sus');
            $session->clear();
            //$session->invalidate(); //to change PHPSID
            return false;
        }

//        if ($user->isDirty()) {
//            $user->save();
//        }

        # from app to session
        $session->set('security.user', $user->getAttributes());

        # from app to cookies
        if ($this->permanent) {
            return true;
        }

        $sign = sha1($user->getKey() . $this->secretKey);
        $response->headers->setCookie(new Cookie('_su', $user->getKey(), new \DateTimeImmutable('+5 year')));
        $response->headers->setCookie(new Cookie('_sus', $sign, new \DateTimeImmutable('+5 year')));

        return true;
    }

    public function getCredentials()
    {
        return $this->credentials;
    }
}