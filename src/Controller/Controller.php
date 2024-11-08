<?php
namespace Budgetcontrol\Authentication\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Controller {

    /**
     * Monitors the incoming request and prepares the response.
     *
     * @param Request $request The incoming HTTP request.
     * @param Response $response The HTTP response to be sent back.
     * @return void
     */
    public function monitor(Request $request, Response $response)
    {
        return response([
            'success' => true,
            'message' => 'Authentication service is up and running'
        ]);
    }
}