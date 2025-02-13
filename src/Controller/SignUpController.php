<?php

namespace Budgetcontrol\Authentication\Controller;

use Budgetcontrol\Library\Model\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Illuminate\Support\Facades\Validator;
use Budgetcontrol\Authentication\Traits\RegistersUsers;
use Budgetcontrol\Authentication\Facade\AwsCognitoClient;
use Budgetcontrol\Authentication\Traits\AuthFlow;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Budgetcontrol\Authentication\Facade\Crypt;
use Budgetcontrol\Authentication\Service\MailService;
use Ramsey\Uuid\Uuid;

class SignUpController
{
    use RegistersUsers, AuthFlow;

    const URL_SIGNUP_CONFIRM = '/app/auth/confirm/';
    const PASSWORD_VALIDATION = '/^(?=.*[0-9])(?=.*[!@#$%^&*])(?=.*[A-Z])(?=.*[a-z]).{8,}$/';

    protected $mailService;

    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }

    public function signUp(Request $request, Response $response, array $args)
    {
        $params = $request->getParsedBody();

        if (!$this->validateSignUp($params)) {
            return response(['error' => 'Validation failed'], 400);
        }

        if (User::where('email', Crypt::encrypt($params["email"]))->exists()) {
            Log::info("User already exists");
            return response([
                "success" => false,
                "error" => "User already exists."
            ], 400);
        }

        try {
            $user = $this->createUser($params);
            $token = $this->generateToken($params, $user->id);
            $this->mailService->send_signUpMail($params["email"], $user->name, $token);
        } catch (\Throwable $e) {
            Log::critical($e->getMessage());
            AwsCognitoClient::deleteUser($params["email"]);
            if (isset($user)) {
                User::find($user->id)->delete();
            }
            return response([
                "success" => false,
                "error" => "An error occurred, try again."
            ], 400);
        }

        try {
            AwsCognitoClient::setUserEmailVerified($user->email);
            AwsCognitoClient::setUserPassword($user->email, $user->password, true);
        } catch (\Throwable $e) {
            Log::critical($e->getMessage());
            Cache::forget($token);
            return response(["error" => "Token is not valid or expired"], 400);
        }

        return response([
            "success" => "Registration successful",
            "details" => $user
        ], 201);
    }

    protected function validateSignUp(array $params)
    {
        try {
            Validator::validate($params, [
                'name' => 'required|max:255',
                'email' => 'required|email|max:64|unique:users',
                'password' => 'sometimes|confirmed|min:6|max:64|regex:' . self::PASSWORD_VALIDATION,
            ]);
            return true;
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return false;
        }
    }

    protected function createUser(array $params)
    {
        $data = collect($params)->only('name', 'email', 'password');

        if ($cognito = $this->createCognitoUser($data)) {
            $user = new User();
            $user->name = $params["name"];
            $user->email = $params["email"];
            $user->password = $data['password'];
            $user->sub = $cognito['User']['Username'];
            $user->uuid = Uuid::uuid4()->toString();
            $user->save();

            return $user;
        }

        throw new \Exception("Failed to create Cognito user");
    }

    public function confirmToken(Request $request, Response $response, array $args)
    {
        $token = $args['token'];

        if (empty($token)) {
            return response(["error" => "Invalid token"], 400);
        }

        $user = Cache::get($token);
        if (empty($user)) {
            Log::critical("User not found");
            return response(["error" => "An error occurred"], 400);
        }

        $user = User::find($user->id);
        $user->email_verified_at = date('Y-m-d H:i:s');
        $user->save();

        Cache::forget($token);

        return response([], 200);
    }
}
