<?php

namespace Minima\Security;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class NativeSessionTokenStorage implements TokenStorageInterface 
{
    const SESSION_NAMESPACE = '_csrf';

    private $namespace;

    /**
     * Initializes the storage with a session namespace.
     *
     * @param string $namespace The namespace under which the token is stored
     *                          in the session
     */
    public function __construct($namespace = self::SESSION_NAMESPACE)
    {
        $this->namespace = $namespace;
    }

    /**
     * {@inheritdoc}
     */
    public function getToken()
    {
	$this->startSession();

	if(isset($_SESSION[$this->namespace])) {
	  return $_SESSION[$this->namespace];
	}

        return NULL; 
    }

    public function setToken(TokenInterface $token = null)
    {
        $this->startSession();
        $_SESSION[$this->namespace] = $token;
    }

    private function startSession()
    {
	if(headers_sent())
	  return;

        if (PHP_VERSION_ID >= 50400) {
            if (PHP_SESSION_NONE === session_status()) {
                session_start();
            }
        } elseif (!session_id()) {
            session_start();
        }
    }
}
