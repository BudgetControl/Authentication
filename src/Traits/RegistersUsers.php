<?php

namespace Budgetcontrol\Authentication\Traits;

    use Faker\Core\Uuid;
use Illuminate\Support\Facades\Log;
use Budgetcontrol\Authentication\Facade\AwsCognitoClient;
use Budgetcontrol\Authentication\Domain\Definitions;

trait RegistersUsers
{
    /**
     * private variable for password policy
     */
    private $passwordPolicy = null;

    /**
     * Passed params
     */
    private $paramUsername = 'email';
    private $paramPassword = 'password';

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Support\Collection $request
     * @return array
     * @throws \Budgetcontrol\Authentication\Exception\InvalidUserFieldException
     */
    public function createCognitoUser(\Illuminate\Support\Collection $request, array $clientMetadata=null, string $groupname=null)
    {
        $email = $request->has('email')?$request['email']:null;
        $username = $email;
        $password = $request->has($this->paramPassword)?$request[$this->paramPassword]:null;

        //Initialize Cognito Attribute array
        $attributes = [];

        //Get the registeration fields
        $userFields = explode(',',env('COGNITO_USER_FIELDS',''));

        //Iterate the fields
        foreach ($userFields as $key => $userField) {
            if ($userField!=null) {
                if ($request->has($userField)) {
                    $attributes[$key] = $request->get($userField);
                } else {
                    Log::error('RegistersUsers:createCognitoUser:InvalidUserFieldException');
                    Log::error("The configured user field {$userField} is not provided in the request.");
                    throw new \Budgetcontrol\Authentication\Exception\InvalidUserFieldException("The configured user field {$userField} is not provided in the request.");
                } //End if
            } //End if
        } //Loop ends

        // create encryption key
        $attributes = [
            Definitions::COGNITO_ATTRIBUTE_EMAIL => $email,
            Definitions::COGNITO_ATTRIBUTE_ENCRYPTED_KEY => generate_secret(),
            Definitions::COGNITO_ATTRIBUTE_EMAIL_VERIFIED => "true",
        ];

        return AwsCognitoClient::createUser($username, $email, $password, $attributes);
    }

} 
