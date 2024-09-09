<?php
namespace Budgetcontrol\Test\Libs;

class AwsCognitoClient {

    private int $expToken = 3600;

    public function decodeAccessToken($authToken) {
        return [
            'sub' => '1234567890',
            'exp' => $this->expToken,
            'username' => 'testuser',
            'email' => 'foo@bar.com',
            'sub' => '8ef9ce05-0c2b-404b-9530-2056089db8f9',
        ];
    }

    public function refreshAuthentication($username, $refresh_token) {
        return [
            'AccessToken' => 'new_access_token',
            'RefreshToken' => 'new_refresh_token'
        ];
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
}