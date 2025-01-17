<?php
declare (strict_types = 1);
namespace Budgetcontrol\Test\Libs;

class MicroserviceClient {
    
    public function workspace() {
        return $this;
    }

    public function add() {
        return new \Budgetcontrol\Connector\Entities\HttpResponse(201, '', []);
    }
}