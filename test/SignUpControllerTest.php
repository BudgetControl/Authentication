<?php

namespace Budgetcontrol\Test;

use MLAB\PHPITest\Entity\Json;
use MLAB\PHPITest\Assertions\JsonAssert;
use Budgetcontrol\Authentication\Traits\Crypt;
use Budgetcontrol\Authentication\Traits\AuthFlow;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Budgetcontrol\Authentication\Controller\SignUpController;
use Budgetcontrol\Authentication\Domain\Model\User;
use Budgetcontrol\Authentication\Facade\AwsCognitoClient;
use Illuminate\Support\Facades\Cache;

class SignUpControllerTest extends BaseCase
{
    use AuthFlow, Crypt;

    public function test_signUp_with_valid_data()
    {
        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);

        $name = 'John Doe';
        $email = 'john.doe@example.com';
        $password = 'Password123';

        $request->method('getParsedBody')->willReturn([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password
        ]);

        $controller = new SignUpController();
        $result = $controller->signUp($request, $response, []);

        $this->assertEquals(201, $result->getStatusCode());
        $this->assertArrayHasKey('success', json_decode((string) $result->getBody(), true));
        $user = !empty(User::find(3));
        $this->assertTrue($user);
        
    }

    public function test_confirmToken_with_valid_token()
    {
        Cache::setShouldReturnError(false);

        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);

        $token = 'valid_token';

        $args = ['token' => $token];

        $controller = new SignUpController();
        $result = $controller->confirmToken($request, $response, $args);

        $this->assertEquals(200, $result->getStatusCode());
    }

    public function test_confirmToken_with_invalid_token()
    {
        Cache::setShouldReturnError(true);
        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);

        $token = 'invalid_token';
        $args = ['token' => $token];

        $controller = new SignUpController();
        $result = $controller->confirmToken($request, $response, $args);

        $this->assertEquals(400, $result->getStatusCode());
        $this->assertArrayHasKey('error', json_decode((string) $result->getBody(), true));
    }
}