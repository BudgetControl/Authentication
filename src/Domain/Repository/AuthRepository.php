<?php
namespace Budgetcontrol\Authentication\Domain\Repository;

use Illuminate\Database\Capsule\Manager as DB;
use Budgetcontrol\Authentication\Facade\Crypt;
use Budgetcontrol\Authentication\Domain\Model\WorkspaceSettings;

class AuthRepository {

    public function workspaces(int $userId) {
       return DB::select(
            'select * from workspaces as w 
            inner join workspaces_users_mm as ws on ws.workspace_id = w.id
            where ws.user_id = ?',
            [$userId]
       );
    }

    /**
     * Retrieves the settings for a specific workspace.
     *
     * @param int $workspaceId The ID of the workspace.
     * @return WorkspaceSettings The settings of the specified workspace.
     */
    public function workspace_settings(int $workspaceId): WorkspaceSettings
    {
        $result = DB::select(
            "select uuid, data, name from workspace_settings as wss 
            right join workspaces as ws on wss.workspace_id = ws.id 
            WHERE wss.workspace_id = $workspaceId"
        );

        return new WorkspaceSettings(
            $result[0]->uuid,
            $result[0]->data,
            $result[0]->name
        );
    }

    public function workspace_share_info(int $workspaceId)
    {
        $results = DB::select(
            "select email, name from users as w
            inner join workspaces_users_mm as ws on ws.user_id = w.id
            where ws.workspace_id = $workspaceId;"
        );

        foreach ($results as $key => $result) {
            $result->email = Crypt::decrypt($result->email);
            if($result->email === false) {
                unset($results[$key]);
            }
        }

        return $results;
    }
}