<?php

namespace Budgetcontrol\Test;

use Budgetcontrol\Authentication\Controller\AuthController;
use Budgetcontrol\Authentication\Exception\AuthException;
use Budgetcontrol\Authentication\Facade\AwsCognitoClient;
use Budgetcontrol\Authentication\Traits\Crypt;
use Budgetcontrol\Authentication\Traits\AuthFlow;
use Illuminate\Support\Facades\Cache;
use MLAB\PHPITest\Assertions\JsonAssert;
use MLAB\PHPITest\Entity\Json;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthControllerTest extends BaseCase
{
    use AuthFlow, Crypt;

    public function test_check_with_valid_token()
    {
        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);
        $args = [];

        $authToken = 'valid_token';
        $request->method('getHeader')->willReturn([$authToken]);

        AwsCognitoClient::setExpToken(time() + 3600);

        $controller = new AuthController();
        $result = $controller->check($request, $response, $args);

        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals('Token is valid', json_decode($result->getBody(), true)['message']);
        $this->assertEquals('application/json', $result->getHeaderLine('Content-Type'));
        $this->assertEquals($authToken, $result->getHeaderLine('Authorization'));
    }

    public function test_check_with_expired_token_and_valid_refresh_token()
    {
        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);
        $args = [];

        $authToken = 'expired_token';
        $request->method('getHeader')->willReturn([$authToken]);

        $newAccessToken = 'new_access_token';
        AwsCognitoClient::setExpToken(0);

        $controller = new AuthController();
        $result = $controller->check($request, $response, $args);

        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals('Token is valid', json_decode($result->getBody(), true)['message']);
        $this->assertEquals('application/json', $result->getHeaderLine('Content-Type'));
        $this->assertEquals($newAccessToken, $result->getHeaderLine('Authorization'));
    }

    public function test_check_with_expired_token_and_invalid_refresh_token()
    {
        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);
        $args = [];

        $authToken = 'expired_token';
        $request->method('getHeader')->willReturn([$authToken]);

        AwsCognitoClient::setExpToken(0);
        AwsCognitoClient::setGotErrorRefreshToken(true);

        $this->expectException(AuthException::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage('Token has expired');

        $controller = new AuthController();
        $controller->check($request, $response, $args);
    }

    public function test_authUserInfo_with_valid_token_and_id_token()
    {
        $request = $this->createMock(Request::class);
        $request->method('getHeader')->willReturn([
                '4373a9a3-a482-4d5a-b8fe-c0572be7efe3',
                'Authorization: Bearer valid_token'
            ]
        );

        $response = $this->createMock(Response::class);
        $args = [];

        $controller = new AuthController();
        $response = $controller->authUserInfo($request, $response, $args);
        $jsonresponse = new JsonAssert(
            new Json(json_decode((string) $response->getBody()))
        );
        $jsonresponse->assertJsonStructure(
            json_decode(file_get_contents(__DIR__ . '/assertions/auth-user-info.json'), true)
        );

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_resetPassword_with_valid_token()
    {
        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);
        $args = ['token' => 'valid_token'];

        $newPassword = 'jfieALD92LD@SJEd03k203';
        $request->method('getParsedBody')->willReturn(['password' => $newPassword, 'email' => 'foo@bar.com', 'name' => 'foo']);

        $controller = new AuthController();
        $result = $controller->resetPassword($request, $response, $args);

        $this->assertEquals(200, $result->getStatusCode());
    }

    public function test_resetPassword_with_invalid_token()
    {
        Cache::setShouldReturnError(true);

        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);
        $args = ['token' => 'valid_token'];

        $newPassword = 'jfieALD92LD@SJEd03k203';
        $request->method('getParsedBody')->willReturn(['password' => $newPassword, 'email' => 'foo@bar.com', 'name' => 'foo']);
        
        $this->expectException(AuthException::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage('Invalid token');

        $controller = new AuthController();
        $controller->resetPassword($request, $response, $args);
    }

    public function test_sendVerifyEmail_with_existing_user()
    {
        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);
        $args = [];

        $email = 'foo@bar.com';
        $request->method('getParsedBody')->willReturn(['email' => $email]);
        
        $controller = new AuthController();
        $result = $controller->sendVerifyEmail($request, $response, $args);

        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals(['message' => 'Email sent'], json_decode($result->getBody(), true));
    }

    public function test_sendResetPasswordMail_with_existing_user()
    {
        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);
        $args = [];

        $email = 'foo@bar.com';
        $request->method('getParsedBody')->willReturn(['email' => $email]);
        
        $controller = new AuthController();
        $result = $controller->sendResetPasswordMail($request, $response, $args);

        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals(['message' => 'Email sent'], json_decode($result->getBody(), true));
    }

    public function test_userInfoByEmail_with_existing_user()
    {
        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);
        $args = ['email' => 'foo@bar.com'];

        $email = 'foo@bar.com';
        $request->method('getParsedBody')->willReturn(['email' => $email]);
        
        $controller = new AuthController();
        $response = $controller->userInfoByEmail($request, $response, $args);

        $this->assertEquals(200, $response->getStatusCode());
        $jsonresponse = new JsonAssert(
            new Json(json_decode((string) $response->getBody()))
        );
        $jsonresponse->assertJsonIsEqualJsonFile(__DIR__ . '/assertions/user-info-by-email.json',
        ['created_at', 'updated_at', 'deleted_at', 'email_verified_at']);
    }

}
