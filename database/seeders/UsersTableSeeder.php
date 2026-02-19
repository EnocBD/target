<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('users')->delete();
        
        \DB::table('users')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'Helder Galeano',
                'email' => 'helder.galeano@gmail.com',
                'phone' => NULL,
                'email_verified_at' => NULL,
                'is_active' => 1,
                'password' => '$2y$12$m1r2BjYfkWIraqgVqZFL6OUc/DSMxQhPODcmO.reoZaALvFgMjDD.',
                'remember_token' => NULL,
                'created_at' => '2026-02-16 18:01:47',
                'updated_at' => '2026-02-16 18:01:47',
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'Enoc Barrientos',
                'email' => 'enoc030693@gmail.com',
                'phone' => '+595981797617',
                'email_verified_at' => NULL,
                'is_active' => 1,
                'password' => '$2y$12$uQ4axO/kIfhQHrYh.Cady.a.bGaDwcgPt1tO88KUMCdJMtVMv/D6q',
                'remember_token' => NULL,
                'created_at' => '2026-02-16 21:38:40',
                'updated_at' => '2026-02-16 21:45:01',
            ),
        ));
        
        
    }
}