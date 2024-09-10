<?php

namespace Budgetcontrol\Authentication\Facade;

use Illuminate\Support\Facades\Facade;


/**
 * This class represents a facade for interacting with the AWS Cognito client.
 * It extends the base Facade class.
 * @see \Budgetcontrol\Connector\Factory\Workspace
 */
class Workspace extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'workspace';
    }
}
