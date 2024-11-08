<?php
namespace Budgetcontrol\Authentication\Controller;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Budgetcontrol\Library\Model\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Budgetcontrol\Authentication\Facade\AwsCognitoClient;
use Budgetcontrol\Connector\Factory\Workspace;
use Budgetcontrol\Authentication\Facade\Crypt;
use Budgetcontrol\Authentication\Definitions\Context;
use Illuminate\Support\Facades\Facade;

class ProviderController extends Controller {

    private Context $context;
     
    /**
     * Authenticates the provider.
     *
     * @param Request $request The request object.
     * @param Response $response The response object.
     * @param array $args The arguments passed to the method.
     * @return Response The response object.
     */
    public function authenticateProvider(Request $request, Response $response, array $args)
    {
        $providerName = $args['provider'];
        $queryParams = $request->getQueryParams();

        try {

            $authCognito = Facade::getFacadeApplication()["aws-cognito-client"];
            if($queryParams['mobile'] === 'android' || $queryParams['mobile'] === 'ios') {
                $authCognito = $authCognito->setAppRedirectUri(env('AWS_COGNITO_REDIRECT_DEEPLINK'));
            }

            $provider = $authCognito->provider();
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
     * @return Response The HTTP response object.
     */
    public function providerToken(Request $request, Response $response, array $args)
    {
        $provider = $args['provider'];
        if(!isset($request->getQueryParams()['code'])) {
            return response([
                'success' => false,
                'message' => 'Missing code'
            ], 400);
        }

        try {
            $authResponse = $this->authenticate($request->getQueryParams()['code'],$provider);

        } catch (\Throwable $e) {

            Log::critical($e->getMessage());

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
        $user = User::where('email', Crypt::encrypt($userEmail))->with('workspaces')->first();
        $sub = $content['sub'];
      
        if(!$user) {
            $this->signupFromProvider($content['name'], $userEmail, $sub);
        } else {
            // Update user information sub
            $user->sub = $sub;
            $user->save();
        }

        //retrive user workspaces
        $user = User::where('email', Crypt::encrypt($userEmail))->with('workspaces')->first();

        Cache::put($sub.'refresh_token', $tokens->refresh_token, Carbon::now()->addDays(30));
        Cache::put($sub.'id_token', $tokens->id_token, Carbon::now()->addDays(30));
            
        return [
            'token' => $tokens->access_token,
            'workspaces' => $user->workspaces
        ];
    }

    /**
     * Sign up a user from a provider.
     *
     * @param string $userName The username of the user.
     * @param string $userEmail The email address of the user.
     * @param string $sub The unique identifier of the user from the provider.
     * @return void
     */
    private function signupFromProvider(string $userName, string $userEmail, string $sub): void
    {

        $user = new User();
        $user->email = $userEmail;
        $user->name = $userName;
        $user->uuid = \Ramsey\Uuid\Uuid::uuid4()->toString();
        $user->password = rand(100000, 999999);
        $user->email_verified_at = date('Y-m-d H:i:s');
        $user->sub = $sub;
        $user->save();

        $wsPayload = [
            'name' => "Workspace",
            'description' => "Default workspace",
        ];

        /** @var \Budgetcontrol\Connector\Model\Response $connector */
        $connector = Workspace::init('POST', $wsPayload)->call('/add', $user->id);
        $workspace = $connector->getBody()['workspace'];
        
        Workspace::init('PATCH',[],[])->call('/'.$workspace['uuid'].'/activate', $user->id);
        
        if ($connector->getStatusCode() != 201) {
            Log::critical("Error creating workspace");
            throw new \Exception("Error creating workspace");
        }

    }
}