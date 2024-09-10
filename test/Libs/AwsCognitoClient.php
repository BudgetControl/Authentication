<?php

namespace Budgetcontrol\Test\Libs;

use malirobot\AwsCognito\Entity\Provider;

class AwsCognitoClient
{

    private int $expToken = 3600;
    private bool $gotErrorRefreshToken = false;
    private bool $gotCognitoException = false;

    public function decodeAccessToken($authToken)
    {
        return [
            'sub' => '1234567890',
            'exp' => $this->expToken,
            'username' => 'testuser',
            'email' => 'foo@bar.com',
            'sub' => '8ef9ce05-0c2b-404b-9530-2056089db8f9',
        ];
    }

    public function refreshAuthentication($username, $refresh_token)
    {

        if ($this->gotErrorRefreshToken) {
            throw new \Exception('Error refreshing token');
        }

        return [
            'AccessToken' => 'new_access_token',
            'RefreshToken' => 'new_refresh_token'
        ];
    }

    public function setUserPassword($username, $password)
    {
        return true;
    }

    public function  authenticate()
    {
        if($this->gotCognitoException) {
            throw new \Exception('Error refreshing token');
        }

        $id_token = 'your_id_token';
        $access_token = 'your_access_token';
        $refresh_token = 'your_refresh_token';

        return [
            'IdToken' => $id_token,
            'AccessToken' => $access_token,
            'RefreshToken' => $refresh_token
        ];
    }

    public function provider()
    {
        return new Provider(
            'client_id', 'client_secret', 'region', 'user_pool_id'
        );
    }

    public function setUserEmailVerified()
    {
        return true;
    }

    public function createUser($username, $password)
    {
        return [
            'User' => [
                'Username' => 'ec417258-a1ce-4da2-9de4-b33ff49d98cc'
            ]
        ];
    }

    public function authenticateProvider()
    {
        return $this->authenticate();
    }

    public function deleteUser($username)
    {
        return true;
    }

    public function setBoolClientSecret()
    {
        return $this;
    }


    /**
     * Set the value of expToken
     *
     * @param int $expToken
     *
     * @return self
     */
    public function setExpToken(int $expToken): self
    {
        $this->expToken = $expToken;

        return $this;
    }


    /**
     * Set the value of gotErrorRefreshToken
     *
     * @param bool $gotErrorRefreshToken
     *
     * @return self
     */
    public function setGotErrorRefreshToken(bool $gotErrorRefreshToken): self
    {
        $this->gotErrorRefreshToken = $gotErrorRefreshToken;

        return $this;
    }


    /**
     * Set the value of gotCognitoException
     *
     * @param bool $gotCognitoException
     *
     * @return self
     */
    public function setGotCognitoException(bool $gotCognitoException): self
    {
        $this->gotCognitoException = $gotCognitoException;

        return $this;
    }
}
