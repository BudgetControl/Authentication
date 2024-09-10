<?php

use Phinx\Seed\AbstractSeed;
use Budgetcontrol\Library\Model\Workspace;
use Illuminate\Database\Capsule\Manager as DB;

class WorkspaceSeed extends AbstractSeed
{

    public function run(): void
    {
        \Budgetcontrol\Seeds\Resources\Seeds\WorkspaceSeeds::create(
            Workspace::class,
            [
                'name' => 'test',
                'description' => 'test',
                'current' => 1,
                'user_id' => 2,
                'uuid' => '4373a9a3-a482-4d5a-b8fe-c0572be7efe3',
            ]
        );

        DB::table('workspaces_users_mm')->insert([
            'workspace_id' => 2,
            'user_id' => 2,
        ]);

        DB::table('workspace_settings')->insert([
            'workspace_id' => 2,
            'setting' => 'app_configurations',
            'data' => '{"currency_id": 1, "payment_type_id": 1}',
        ]);
    }
}
