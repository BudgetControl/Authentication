<?php
namespace Budgetcontrol\Authentication\Domain\Repository;

use Illuminate\Database\Capsule\Manager as DB;
use Budgetcontrol\Authentication\Facade\Crypt;

class AuthRepository {

    public function workspaces(int $userId) {
       return DB::select(
            'select * from workspaces as w 
            inner join workspaces_users_mm as ws on ws.workspace_id = w.id
            where ws.user_id = ?',
            [$userId]
       );
    }

    public function workspace_settings(int $workspaceId)
    {
        return DB::select(
            "select * from workspace_settings where workspace_id = $workspaceId"
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