<?php

$connector = new \Budgetcontrol\Connector\Factory\MicroserviceClient(
    new \Budgetcontrol\Connector\Entities\MsDomains(
        'http://budgetcontrol-ms-workspace',
        'http://budgetcontrol-ms-wallets',
        'http://budgetcontrol-ms-entries',
        'http://budgetcontrol-ms-stats',
        'http://budgetcontrol-ms-budget',
        'http://budgetcontrol-ms-savings'
    ),
    $logger
);