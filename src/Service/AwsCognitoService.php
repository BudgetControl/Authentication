<?php

namespace Budgetcontrol\Authentication\Service;

use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;

class AwsCognitoService {
    protected $client;
    protected $config;

    public function __construct()
    {
        $this->client = new CognitoIdentityProviderClient([
            'version' => $_ENV['AWS_COGNITO_VERSION'],
            'region'  => $_ENV['AWS_COGNITO_REGION'],
            'credentials' => [
                'key'    => $_ENV['AWS_COGNITO_ACCESS_KEY_ID'],
                'secret' => $_ENV['AWS_COGNITO_SECRET_ACCESS_KEY'],
            ]
        ]);
    }

    /**
     * Update user attributes in Cognito
     * 
     * @param string $username The username (email) of the user
     * @param array $attributes Array of attributes to update
     * @return bool
     */
    public function updateUserAttributes($username, array $attributes)
    {
        $userAttributes = [];
        foreach ($attributes as $name => $value) {
            $userAttributes[] = [
                'Name'  => $name,
                'Value' => $value
            ];
        }

        try {
            $this->client->adminUpdateUserAttributes([
                'UserPoolId' => $_ENV['AWS_COGNITO_USER_POOL_ID'],
                'Username'   => $username,
                'UserAttributes' => $userAttributes
            ]);
            return true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error updating Cognito user attributes: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user attributes from Cognito
     * 
     * @param string $username The username (email) of the user
     * @return array
     */
    public function getUserAttributes($username)
    {
        try {
            $result = $this->client->adminGetUser([
                'UserPoolId' => $_ENV['AWS_COGNITO_USER_POOL_ID'],
                'Username'   => $username
            ]);

            $attributes = [];
            foreach ($result['UserAttributes'] as $attribute) {
                $attributes[$attribute['Name']] = $attribute['Value'];
            }
            return $attributes;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error getting Cognito user attributes: ' . $e->getMessage());
            return [];
        }
    }
}
