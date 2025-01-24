<?php

namespace Budgetcontrol\Authentication\Facade;

use Illuminate\Support\Facades\Facade;


/**
 * @method static \Budgetcontrol\Connector\Client\WorkspaceClient workspace()
 * @method static \Budgetcontrol\Connector\Client\WalletClient wallet()
 * @method static \Budgetcontrol\Connector\Client\EntryClient entries()
 * @method static \Budgetcontrol\Connector\Client\StatsClient stats()
 * @method static \Budgetcontrol\Connector\Client\BudgetClient budget()
 * @method static \Budgetcontrol\Connector\Client\SavingClient savings()
 * 
 * @see \Budgetcontrol\Connector\Factory\MicroserviceClient
 */
class ConnectorClient extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'bc-connector';
    }
}
