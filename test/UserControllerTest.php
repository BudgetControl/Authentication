<?php

namespace Budgetcontrol\Test;

use Budgetcontrol\Authentication\Controller\AuthController;
use Budgetcontrol\Library\Model\User;
use Budgetcontrol\Authentication\Facade\Crypt;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Budgetcontrol\Authentication\Controller\UserController;
use Budgetcontrol\Authentication\Controller\LoginController;

class UserControllerTest extends BaseCase
{
    public function test_delete_existing_user()
    {
        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);
        $args = ['uuid' => '2f6cd46c-fbef-4d12-be20-61304463fdd8'];

        $controller = new UserController();
        $result = $controller->delete($request, $response, $args);

        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals(['message' => 'User deleted successfully'], json_decode((string) $result->getBody(), true));
        $this->assertNull(User::find(2));
    }

    public function test_get_user_info()
    {

        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);

        $email = 'foo@bar.com';
        $password = 'password';

        $request->method('getParsedBody')->willReturn(['email' => $email, 'password' => $password]);

        $controller = new AuthController();
        $result = $controller->authUserInfo($request, $response, []);

        $this->assertEquals(200, $result->getStatusCode());
        
        $jsonresponse = new JsonAssert(
            new Json(json_decode((string) $result->getBody()))
        );
        $jsonresponse->assertJsonIsEqualJsonFile(__DIR__ . '/assertions/authentication-user-info.json',
        ['created_at', 'updated_at', 'deleted_at', 'email_verified_at', 'id']);
        
    }

}