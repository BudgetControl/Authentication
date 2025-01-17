<?php

use Budgetcontrol\Connector\Entities\MsDomains;
use Budgetcontrol\Connector\Factory\MicroserviceClient;

$connector = new MicroserviceClient(
    new MsDomains(
        'http://budgetcontrol-ms-workspace',
        'http://budgetcontrol-ms-wallets',
        'http://budgetcontrol-ms-entries',
        'http://budgetcontrol-ms-stats',
        'http://budgetcontrol-ms-budget',
        'http://budgetcontrol-ms-savings'
    ),
    $logger
);