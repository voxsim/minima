<?php

namespace Minima\Auth;

use Minima\Http\Request;

class Authentication
{
    public function attempt(Request $request) {
        $username = $request->server->get('PHP_AUTH_USER', false);
        $password = $request->server->get('PHP_AUTH_PW');

        if ('Simon' === $username && 'password' === $password) {
            $request->getSession()->set('user', array('username' => $username));
            return true;
        }

        return false;
    }

    public function check(Request $request) {
        return $request->getSession()->get('user') != null;
    }
}
