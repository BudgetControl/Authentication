<?php

namespace Budgetcontrol\Authentication\Controller;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Psr\Http\Message\ResponseInterface as Response;
use Budgetcontrol\Library\Model\User;
use Psr\Http\Message\ServerRequestInterface as Request;
use Budgetcontrol\Authentication\Exception\AuthException;
use Budgetcontrol\Authentication\Facade\AwsCognitoClient;
use Budgetcontrol\Authentication\Facade\Crypt;
use Illuminate\Support\Facades\Log;

class LoginController
{
    public function authenticate(Request $request, Response $response, array $args)
    {
        $user = $request->getParsedBody()['email'];
        $password = $request->getParsedBody()['password'];

        try {
            $userAuth = AwsCognitoClient::setBoolClientSecret()->authenticate($user, $password);

            Log::debug('User authentication attempt', [
                'user' => $user,
                'auth_result' => $userAuth
            ]);

            // decode auth token
            $decodedToken = AwsCognitoClient::decodeAccessToken($userAuth['AccessToken']);
            $sub = $decodedToken['sub'];

            \Illuminate\Support\Facades\Log::debug('Decoded token: ' . json_encode($decodedToken));

            if (!empty($userAuth['error'])) {
                Log::error('Authentication error: ' . $userAuth['error'], [
                    'user' => $user,
                    'error' => $userAuth['error']
                ]);
                return response([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }
        } catch (\Throwable $e) {
            Log::critical('Authentication error: ' . $e->getMessage(), [
                'user' => $user,
                'exception' => $e
            ]);
            return response([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }
        $cryptedMail = Crypt::encrypt($user);
        $user = User::where('email', $cryptedMail)->with('workspaces')->first();

        if(is_null($user)) {
            Log::info('User not found in database');
            return response([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $user->sub = $sub;
        $user->save();

        // put refresh token in cache
        $refreshToken = $userAuth['RefreshToken'];
        $idToken = $userAuth['IdToken'];

        Cache::put($sub.'refresh_token', $refreshToken, Carbon::now()->addDays(30));
        Cache::put($sub.'id_token', $idToken, Carbon::now()->addDays(30));
        
        return response([
            'success' => true,
            'message' => 'User authenticated',
            'refresh_token' => $refreshToken,
            'id_token' => $idToken,
            'token' => $userAuth['AccessToken'],
            'workspaces' => $user->workspaces
        ]);

    }

    public function logout(Request $request, Response $response, array $args)
    {
        $authToken = $request->getHeader('Authorization')
            ? $request->getHeader('Authorization')[0]
            : null;
        
        if(!$authToken) {
            throw new AuthException('Missing Authorization header', 401);
        }
        
        $authToken = str_replace('Bearer ', '', $authToken);
        $decodedToken = AwsCognitoClient::decodeAccessToken($authToken);
        
        Cache::forget($decodedToken['sub'].'refresh_token');
        Cache::forget($decodedToken['sub'].'id_token');
        Cache::forget($decodedToken['sub'].'user_info');
        
        return response([
            'success' => true,
            'message' => 'User logged out'
        ]);
        
    }
}
