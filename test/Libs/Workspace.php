<?php
declare (strict_types = 1);
namespace Budgetcontrol\Test\Libs;

class Workspace {
    
    public static function init(string $method, array $payload, array $header = []) {
        return new \Budgetcontrol\Test\Libs\WorkspaceClient();
    }
}