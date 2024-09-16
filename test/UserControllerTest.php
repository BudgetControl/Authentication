<?php

namespace Budgetcontrol\Test;

use Budgetcontrol\Authentication\Controller\UserController;
use Budgetcontrol\Authentication\Domain\Model\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Budgetcontrol\Authentication\Facade\Crypt;

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

}