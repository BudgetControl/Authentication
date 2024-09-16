<?php

use Phinx\Seed\AbstractSeed;
use Budgetcontrol\Library\Model\User;

class UserSeed extends AbstractSeed
{

    public function run(): void
    {
        $dateTime = new DateTime();

        \Budgetcontrol\Seeds\Resources\Seeds\UserSeeds::create(
            User::class,
            [
                "name" => "testuser",
                "email" => "foo@bar.com",
                "password" => "password",
                "created_at" => $dateTime->format('Y-m-d H:i:s'),
                "updated_at" => $dateTime->format('Y-m-d H:i:s'),
                "sub" => "8ef9ce05-0c2b-404b-9530-2056089db8f9",
                "uuid" => \Ramsey\Uuid\Uuid::uuid4()->toString(),
                "email_verified_at" => $dateTime->format('Y-m-d H:i:s'),
                "uuid" => "2f6cd46c-fbef-4d12-be20-61304463fdd8",
            ]
        );
    }
}
