<?php

namespace Budgetcontrol\Authentication\Facade;

use Illuminate\Support\Facades\Facade;


/**
 * This class represents a facade for interacting with the AWS Cognito client.
 * It extends the base Facade class.
 *
 * @method array createUser($username, $email, $tempPassword, array $attributes = [])
 * @method array authenticate($username, $password)
 * @method array decodeAccessToken($token)
 * @method array refreshAuthentication($username, $refreshToken)
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
