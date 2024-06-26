<?php
namespace Budgetcontrol\Authentication\Controller;

use GuzzleHttp\Client;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Budgetcontrol\Authentication\Facade\Cache;
use Budgetcontrol\Authentication\Traits\Crypt;
use League\Container\Exception\NotFoundException;
use Budgetcontrol\Authentication\Domain\Model\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Budgetcontrol\Authentication\Domain\Entity\Provider;
use Budgetcontrol\Authentication\Facade\AwsCognitoClient;
use malirobot\AwsCognito\Entity\Provider as EntityProvider;

class ProviderController {

    use Crypt;

    /**
     * Authenticates the provider.
     *
     * @param Request $request The request object.
     * @param Response $response The response object.
     * @param array $args The arguments passed to the method.
     * @return void
     */
    public function authenticateProvider(Request $request, Response $response, array $args)
    {
        $providerName = $args['provider'];

        try {
            $provider = AwsCognitoClient::provider();
            $uri = $provider->$providerName(env('COGNITO_GOOGLE_AUTH_URL'));

        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return response([
                'success' => false,
                'message' => "Provider not found"
            ], 400);
        }

        return response([
            'success' => true,
            'uri' => $uri
        ]);
    }

    /**
     * Handles the provider token request.
     *
     * @param Request $request The HTTP request object.
     * @param Response $response The HTTP response object.
     * @param array $args The route parameters.
     * @return void
     */
    public function providerToken(Request $request, Response $response, array $args)
    {
        $provider = $args['provider'];
        if(!$request->getQueryParams()['code']) {
            return response([
                'success' => false,
                'message' => 'Missing code'
            ], 400);
        }

        try {
            $authResponse = $this->authenticate($request->getQueryParams()['code'],$provider);

        } catch (\Throwable $e) {
            return response([
                'success' => false,
                'message' => "Authentication failed"
            ], 401);
        }

        return response([
            'success' => true,
            'message' => 'User authenticated',
            'token' => $authResponse['token'],
            'workspaces' => $authResponse['workspaces']
        ]);
    }

    /**
     * Authenticates the provided code.
     *
     * @param string $code The code to authenticate.
     * @return array The Authentication result and workspace result.
     */
    private function authenticate(string $code, string $providerName): array
    {
        $provider = AwsCognitoClient::provider();
        $params = $provider->getParams($providerName);
        $tokens = AwsCognitoClient::authenticateProvider($code, $params['redirect_uri']);

        // Decode ID Token
        $content = AwsCognitoClient::decodeAccessToken($tokens->id_token);
        $userEmail = $content['email'];
        $user = User::where('email', $this->encrypt($userEmail))->with('workspaces')->first();
        $sub = $content['sub'];

        if(empty($user)) {
            throw new NotFoundException("User not found", 404);
        }
      
        if(!$user) {
            $user = new User();
            $user->email = $userEmail;
            $user->name = $content['name'];
            $user->uuid = \Ramsey\Uuid\Uuid::uuid4()->toString();
            $user->sub = $sub;
            $user->save();
        } else {
            // Update user information sub
            $user->sub = $sub;
            $user->save();
        }

        Cache::put($sub.'refresh_token', $tokens->refresh_token, Carbon::now()->addDays(30));
        Cache::put($sub.'id_token', $tokens->id_token, Carbon::now()->addDays(30));
            
        return [
            'token' => $tokens->access_token,
            'workspaces' => $user->workspaces
        ];
    }
}