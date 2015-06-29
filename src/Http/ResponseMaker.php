<?php

namespace Minima\Http;

use Symfony\Component\HttpFoundation\Response;

class ResponseMaker {
    public function create($message, $statusCode = 200) {
        return new Response($message, $statusCode);
    }
}
