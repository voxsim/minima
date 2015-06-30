<?php

namespace Minima\Http;

use Minima\Http\Response;

class ResponseMaker {
    public function create($message, $statusCode = 200) {
        return new Response($message, $statusCode);
    }
}
