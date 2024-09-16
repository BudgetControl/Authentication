<?php

namespace Budgetcontrol\Test;

use MLAB\PHPITest\Entity\Json;
use MLAB\PHPITest\Assertions\JsonAssert;
use Budgetcontrol\Authentication\Facade\Crypt;
use Budgetcontrol\Authentication\Traits\AuthFlow;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Budgetcontrol\Authentication\Controller\LoginController;
use Budgetcontrol\Authentication\Facade\AwsCognitoClient;


class LoginControllerTest extends BaseCase
{
    use AuthFlow;

    public function test_authenticate_with_valid_credentials()
    {
        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);

        $email = 'foo@bar.com';
        $password = 'password';


        $request->method('getParsedBody')->willReturn(['email' => $email, 'password' => $password]);

        $controller = new LoginController();
        $result = $controller->authenticate($request, $response, []);

        $this->assertEquals(200, $result->getStatusCode());
        
        $jsonresponse = new JsonAssert(
            new Json(json_decode((string) $result->getBody()))
        );
        $jsonresponse->assertJsonIsEqualJsonFile(__DIR__ . '/assertions/authentication.json',
        ['created_at', 'updated_at', 'deleted_at', 'email_verified_at', 'id']);
    }

    public function test_authenticate_with_invalid_credentials()
    {
        AwsCognitoClient::setGotCognitoException(true);

        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);

        $email = 'foo@bar.com';
        $password = 'invalid_password';


        $request->method('getParsedBody')->willReturn(['email' => $email, 'password' => $password]);

        $controller = new LoginController();
        $result = $controller->authenticate($request, $response, []);

        $this->assertEquals(401, $result->getStatusCode());
        
    }

    public function test_logout()
    {
        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);
        $request->method('getHeader')->willReturn(['Bearer token']);

        $controller = new LoginController();
        $result = $controller->logout($request, $response, []);

        $this->assertEquals(200, $result->getStatusCode());
    }

    
}