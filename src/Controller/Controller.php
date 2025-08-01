<?php
namespace Budgetcontrol\Authentication\Controller;

use PDO;
use PDOException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Controller {

    public function monitor(Request $request, Response $response)
    {
        return response([
            'success' => true,
            'message' => 'Authentication service is up and running'
        ]);
    }

    protected function getAttributeFromCognito(array $userInfo, string $attributeName): ?string
    {
        foreach ($userInfo['UserAttributes'] as $attribute) {
            if ($attribute['Name'] === $attributeName) {
                return $attribute['Value'];
            }
        }
        return null;
    }
}