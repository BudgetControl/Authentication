<?php

namespace Budgetcontrol\Authentication\Facade;

use Illuminate\Support\Facades\Facade;


/**
 * This class represents a facade for interacting with the AWS Cognito client.
 * It extends the base Facade class.
 * 
 * @method static \Aws\CognitoIdentityProvider\CognitoIdentityProviderClient provider()
 * @method static \Aws\CognitoIdentityProvider\CognitoIdentityProviderClient client()
 * @method static \Aws\CognitoIdentityProvider\CognitoIdentityProviderClient setAppClientId(string $appClientId)
 * @method static \Aws\CognitoIdentityProvider\CognitoIdentityProviderClient setAppClientSecret(string $appClientSecret)
 * @method static \Aws\CognitoIdentityProvider\CognitoIdentityProviderClient setRegion(string $region)
 * @method static \Aws\CognitoIdentityProvider\CognitoIdentityProviderClient setUserPoolId(string $userPoolId)
 * @method static \Aws\CognitoIdentityProvider\CognitoIdentityProviderClient setAppName(string $appName)
 * @method static \Aws\CognitoIdentityProvider\CognitoIdentityProviderClient setAppRedirectUri(string $appRedirectUri)
 * @method static \Aws\CognitoIdentityProvider\CognitoIdentityProviderClient cognitoSecretHash(string $username)
 * @method static \Aws\CognitoIdentityProvider\CognitoIdentityProviderClient verifyAccessToken(string $accessToken)
 * 
 * 
 * @see \malirobot\AwsCognito\CognitoClient
 */
class AwsCognitoClient extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'aws-cognito-client';
    }
}
