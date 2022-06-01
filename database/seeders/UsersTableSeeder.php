<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            [
                'first_name'    => 'Name 1 ',
                'last_name'     => 'Surname 1',
                'email'         => 'user1@email.com',
                'phone'         => '+380000000001',
                'password'      => Hash::make('PassworD'),
            ],
        ];

        $user_amount = 10;

        for ($i = 0; $i < $user_amount; $i++) {
            $user_info = [
                'first_name'    => "Name $i",
                'last_name'     => "Surname $i",
                'email'         => "user$i@email.com",
                'phone'         => "+38000000000$i",
                'password'      => "Password$i",
            ];

            $user = User::createUser($user_info);

            for ($j = 0; $j < $user->id; $j++) {
                $company_info = [
                    'user_id'       => $user->id,
                    'title'         => "Company $j Of user $user->id",
                    'phone'         => "+380000000$i$j",
                    'description'   => "Company $j belongs to user: $user->fullName with id: $user->id",
                ];

                Company::create($company_info);
            }
        }
    }
}
