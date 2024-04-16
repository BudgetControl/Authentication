<?php

namespace Budgetcontrol\Authtentication\Traits;

    use Illuminate\Support\Facades\Log;
use Budgetcontrol\Authtentication\Facade\AwsCognitoClient;

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
     * @return \Illuminate\Http\Response
     * @throws InvalidUserFieldException
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
                    throw new \Budgetcontrol\Authtentication\Exception\InvalidUserFieldException("The configured user field {$userField} is not provided in the request.");
                } //End if
            } //End if
        } //Loop ends

        return AwsCognitoClient::createUser($username, $password, $password, [
            'email' => $email,
            'email_verified' => 'true',
        ]);
    }

} 
