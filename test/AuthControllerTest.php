<?php

namespace Budgetcontrol\Test;

use Budgetcontrol\Authentication\Controller\AuthController;
use Budgetcontrol\Authentication\Domain\Model\User;
use Budgetcontrol\Authentication\Domain\Repository\AuthRepository;
use Budgetcontrol\Authentication\Exception\AuthException;
use Budgetcontrol\Authentication\Facade\AwsCognitoClient;
use Budgetcontrol\Authentication\Traits\Crypt;
use Budgetcontrol\Authentication\Traits\AuthFlow;
use Budgetcontrol\Test\Libs\AwsCognitoClient as LibsAwsCognitoClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use League\Container\Exception\NotFoundException;
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

        $controller= new AuthController();
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

        $controller= new AuthController();
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

        $controller= new AuthController();
        $controller->check($request, $response, $args);

        $this->expectException(AuthException::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage('Token has expired');
    }

    public function test_authUserInfo_with_valid_token_and_id_token()
    {
        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);
        $args = [];

        $this->expectException(NotFoundException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage('User not found');

        $controller= new AuthController();
        $controller->authUserInfo($request, $response, $args);
    }

    public function test_resetPassword_with_valid_token()
    {
        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);
        $args = ['token' => 'valid_token'];

        Validator::expects($this->once())
            ->method('validate')
            ->with([
                'name' => 'required|max:255',
                'email' => 'required|email|max:64|unique:users',
                'password' => 'sometimes|confirmed|min:6|max:64|regex:' . SignUpController::PASSWORD_VALIDATION,
            ]);

        $newPassword = 'new_password';
        $request->method('getParsedBody')->willReturn(['password' => $newPassword]);

        $email = 'user_email';
        $tokenInCache = (object) ['email' => $email];
        Cache::expects($this->once())
            ->method('has')
            ->with($args['token'])
            ->willReturn(true);
        Cache::expects($this->once())
            ->method('get')
            ->with($args['token'])
            ->willReturn($tokenInCache);

        $user = $this->createMock(User::class);
        $user->method('save');
        User::expects($this->once())
            ->method('where')
            ->with('email', $this->encrypt($email))
            ->willReturn($user);

        AwsCognitoClient::expects($this->once())
            ->method('setUserPassword')
            ->with($email, $newPassword, true);

        $result = AuthController::resetPassword($request, $response, $args);

        $this->assertEquals(200, $result->getStatusCode());
    }

    public function test_resetPassword_with_invalid_token()
    {
        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);
        $args = ['token' => 'invalid_token'];

        Cache::expects($this->once())
            ->method('has')
            ->with($args['token'])
            ->willReturn(false);

        $this->expectException(AuthException::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage('Invalid token');

        AuthController::resetPassword($request, $response, $args);
    }

    public function test_sendVerifyEmail_with_existing_user()
    {
        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);
        $args = [];

        $email = 'user_email';
        $request->method('getParsedBody')->willReturn(['email' => $email]);

        $user = $this->createMock(User::class);
        User::expects($this->once())
            ->method('where')
            ->with('email', $this->encrypt($email))
            ->willReturn($user);

        $token = 'verification_token';
        $this->generateToken(['email' => $email], $user->id, 'verify_email')->willReturn($token);

        $mail = $this->createMock(\Budgetcontrol\Authentication\Service\MailService::class);
        $mail->expects($this->once())
            ->method('send_signUpMail')
            ->with($email, $user->name, $token);

        $result = AuthController::sendVerifyEmail($request, $response, $args);

        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals(['message' => 'Email sent'], json_decode($result->getBody(), true));
    }

    public function test_sendVerifyEmail_with_non_existing_user()
    {
        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);
        $args = [];

        $email = 'non_existing_user_email';
        $request->method('getParsedBody')->willReturn(['email' => $email]);

        User::expects($this->once())
            ->method('where')
            ->with('email', $this->encrypt($email))
            ->willReturn(null);

        $result = AuthController::sendVerifyEmail($request, $response, $args);

        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals(['message' => 'Email sent'], json_decode($result->getBody(), true));
    }

    public function test_sendResetPasswordMail_with_existing_user()
    {
        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);
        $args = [];

        $email = 'user_email';
        $request->method('getParsedBody')->willReturn(['email' => $email]);

        $user = $this->createMock(User::class);
        User::expects($this->once())
            ->method('where')
            ->with('email', $this->encrypt($email))
            ->willReturn($user);

        $token = 'reset_password_token';
        $this->generateToken(['email' => $email], $user->id, 'reset_password')->willReturn($token);

        $mail = $this->createMock(\Budgetcontrol\Authentication\Service\MailService::class);
        $mail->expects($this->once())
            ->method('send_resetPassowrdMail')
            ->with($email, $user->name, $token);

        $result = AuthController::sendResetPasswordMail($request, $response, $args);

        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals(['message' => 'Email sent'], json_decode($result->getBody(), true));
    }

    public function test_sendResetPasswordMail_with_non_existing_user()
    {
        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);
        $args = [];

        $email = 'non_existing_user_email';
        $request->method('getParsedBody')->willReturn(['email' => $email]);

        User::expects($this->once())
            ->method('where')
            ->with('email', $this->encrypt($email))
            ->willReturn(null);

        $result = AuthController::sendResetPasswordMail($request, $response, $args);

        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals(['message' => 'Email sent'], json_decode($result->getBody(), true));
    }

    public function test_userInfoByEmail_with_existing_user()
    {
        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);
        $args = ['email' => 'user_email'];

        $email = $args['email'];
        $user = $this->createMock(User::class);
        User::expects($this->once())
            ->method('where')
            ->with('email', $this->encrypt($email))
            ->willReturn($user);

        $result = AuthController::userInfoByEmail($request, $response, $args);

        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals($user->toArray(), json_decode($result->getBody(), true));
    }

    public function test_userInfoByEmail_with_non_existing_user()
    {
        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);
        $args = ['email' => 'non_existing_user_email'];

        User::expects($this->once())
            ->method('where')
            ->with('email', $this->encrypt($args['email']))
            ->willReturn(null);

        $this->expectException(AuthException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage('User not found');

        AuthController::userInfoByEmail($request, $response, $args);
    }
}