<?php

namespace Budgetcontrol\Authentication\Controller\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Budgetcontrol\Authentication\Controller\ProviderController;
use Budgetcontrol\Authentication\Facade\AwsCognitoClient;

class ProviderControllerTest extends TestCase
{
    public function test_authenticateProvider_with_valid_provider()
    {
        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);
        $args = ['provider' => 'google'];

        $controller = new ProviderController();
        $result = $controller->authenticateProvider($request, $response, $args);

        $this->assertEquals(200, $result->getStatusCode());
        $this->assertArrayHasKey('uri', json_decode((string) $result->getBody(),true));

        $uri = json_decode((string) $result->getBody(),true)['uri'];
        $this->assertStringContainsString('AWS_COGNITO_REDIRECT_URI', $uri);
    }

    public function test_authenticateProvider_with_invalid_provider()
    {
        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);
        $args = ['provider' => 'invalid_provider'];

        $controller = new ProviderController();
        $result = $controller->authenticateProvider($request, $response, $args);

        $this->assertEquals(400, $result->getStatusCode());
        $this->assertEquals('Provider not found', json_decode((string) $result->getBody(),true) ['message']);
    }

    public function test_providerToken_with_missing_code()
    {
        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);
        $args = ['provider' => 'google'];
        $queryParams = [];

        $request->method('getQueryParams')->willReturn($queryParams);

        $controller = new ProviderController();
        $result = $controller->providerToken($request, $response, $args);

        $this->assertEquals(400, $result->getStatusCode());
        $this->assertEquals('Missing code', json_decode((string) $result->getBody(),true)['message']);
    }

    public function test_providerToken_with_valid_code()
    {
        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);
        $args = ['provider' => 'google'];
        $queryParams = [
            'code' => 'valid_code'
        ];

        $request->method('getQueryParams')->willReturn($queryParams);

        $controller = new ProviderController();
        $result = $controller->providerToken($request, $response, $args);

        $this->assertEquals(401, $result->getStatusCode());
        $this->assertEquals('Authentication failed',json_decode((string) $result->getBody(),true)['message']);
    }

    public function test_providerUrl_for_mobile_phone()
    {
        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);
        $args = ['provider' => 'google'];
        $queryParam = '?mobile=android';

        $request->method('getQueryParams')->willReturn($queryParam);

        $controller = new ProviderController();
        $result = $controller->authenticateProvider($request, $response, $args);

        $this->assertEquals(200, $result->getStatusCode());
        $this->assertArrayHasKey('uri', json_decode((string) $result->getBody(),true));

        $uri = json_decode((string) $result->getBody(),true)['uri'];
        $this->assertStringContainsString('AWS_COGNITO_REDIRECT_DEEPLINK', $uri);
    }

}