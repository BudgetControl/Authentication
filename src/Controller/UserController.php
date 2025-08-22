<?php
namespace Budgetcontrol\Authentication\Controller;

use Budgetcontrol\Library\Model\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Budgetcontrol\Authentication\Service\AwsCognitoService;

class UserController extends Controller {

    /**
     * Get user information including the encryption key.
     *
     * @param Request $request The HTTP request object.
     * @param Response $response The HTTP response object.
     * @param array $args The route parameters.
     * @return Response The HTTP response object.
     */
    public function userInfo(Request $request, Response $response, array $args)
    {
        $uuid = $args['uuid'];
        $user = User::where('uuid', $uuid)->first();
        
        if (!$user) {
            return response(['message' => 'User not found'], 404);
        }

        try {
            // Generate a secure encryption key if not exists
            $parameterName = "/budgetcontrol/{$user->uuid}/encrypt_key";
            
            // Initialize AWS SSM Client
            $ssm = new \Aws\Ssm\SsmClient([
                'version' => 'latest',
                'region'  => $_ENV['AWS_REGION'],
                'credentials' => [
                    'key'    => $_ENV['AWS_COGNITO_ACCESS_KEY_ID'],
                    'secret' => $_ENV['AWS_COGNITO_SECRET_ACCESS_KEY'],
                ]
            ]);

            try {
                // Try to get existing parameter
                $result = $ssm->getParameter([
                    'Name' => $parameterName,
                    'WithDecryption' => true
                ]);
                $encryptKey = $result['Parameter']['Value'];
            } catch (\Aws\Ssm\Exception\SsmException $e) {
                // Parameter doesn't exist, create new one
                $encryptKey = bin2hex(random_bytes(32)); // Generate 256-bit key
                
                // Save to Parameter Store
                $ssm->putParameter([
                    'Name' => $parameterName,
                    'Value' => $encryptKey,
                    'Type' => 'SecureString',
                    'Overwrite' => false
                ]);

                // Save to Cognito as custom attribute
                $cognitoService = new AwsCognitoService();
                $cognitoService->updateUserAttributes($user->email, [
                    'custom:encrypt_key' => $encryptKey
                ]);
            }

            return response([
                'user' => $user,
                'encrypt_key' => $encryptKey
            ], 200);

        } catch (\Exception $e) {
            return response(['message' => 'Error retrieving user information'], 500);
        }
    }

    /**
     * Deletes a user.
     *
     * @param Request $request The HTTP request object.
     * @param Response $response The HTTP response object.
     * @param array $args The route parameters.
     * @return Response The updated HTTP response object.
     */
    public function delete(Request $request, Response $response, array $args)
    {
        $uuid = $args['uuid'];
        $user = User::where('uuid', $uuid)->first();
        if ($user) {
            $user->delete();
            return response(['message' => 'User deleted successfully'], 200);
        }

        return response(['message' => 'User not found'], 404);
    }

}