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
    
    /**
     * Checks if the user agent in the request matches the specified user agent name.
     *
     * @param Request $request The HTTP request object.
     * @param string $userAgentName The name of the user agent to check against.
     * @return bool Returns true if the user agent matches, false otherwise.
     */
    private function checkUserAgent(Request $request, string $userAgentName): bool
    {
        $userAgent = $request->getHeader('User-Agent');

        switch ($userAgentName) {
            case 'android':
                return strpos($userAgent[0], 'Android') !== false;
            case 'ios':
                return strpos($userAgent[0], 'iPhone') !== false || strpos($userAgent[0], 'iPad') !== false;
            default:
                return false;
        }
    }

    /**
     * Checks if a specific header exists in the given request.
     *
     * @param Request $request The HTTP request object.
     * @param string $headerName The name of the header to check for.
     * @return bool Returns true if the header exists, false otherwise.
     */
    protected function existHeader(Request $request, string $headerName): bool 
    {
        return $request->getHeader($headerName) !== null;
    }

    /**
     * Checks if the request is coming from an Android device.
     *
     * @param Request $request The HTTP request object.
     * @return bool Returns true if the request is from an Android device, false otherwise.
     */
    protected function isAndroid(Request $request): bool
    {
        return $this->checkUserAgent($request, 'android');
    }
    
    /**
     * Checks if the request is coming from an iOS device.
     *
     * @param Request $request The HTTP request object.
     * @return bool Returns true if the request is from an iOS device, false otherwise.
     */
    protected function isIos(Request $request): bool
    {
        return $this->checkUserAgent($request, 'ios');
    }


}