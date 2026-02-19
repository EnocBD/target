<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('permissions')->delete();
        
        \DB::table('permissions')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'view-dashboard',
                'guard_name' => 'web',
                'created_at' => '2026-02-16 18:08:05',
                'updated_at' => '2026-02-16 18:08:05',
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'view-pages',
                'guard_name' => 'web',
                'created_at' => '2026-02-16 18:08:05',
                'updated_at' => '2026-02-16 18:08:05',
            ),
            2 => 
            array (
                'id' => 3,
                'name' => 'create-pages',
                'guard_name' => 'web',
                'created_at' => '2026-02-16 18:08:05',
                'updated_at' => '2026-02-16 18:08:05',
            ),
            3 => 
            array (
                'id' => 4,
                'name' => 'edit-pages',
                'guard_name' => 'web',
                'created_at' => '2026-02-16 18:08:05',
                'updated_at' => '2026-02-16 18:08:05',
            ),
            4 => 
            array (
                'id' => 5,
                'name' => 'delete-pages',
                'guard_name' => 'web',
                'created_at' => '2026-02-16 18:08:05',
                'updated_at' => '2026-02-16 18:08:05',
            ),
            5 => 
            array (
                'id' => 6,
                'name' => 'view-blocks',
                'guard_name' => 'web',
                'created_at' => '2026-02-16 18:08:05',
                'updated_at' => '2026-02-16 18:08:05',
            ),
            6 => 
            array (
                'id' => 7,
                'name' => 'create-blocks',
                'guard_name' => 'web',
                'created_at' => '2026-02-16 18:08:05',
                'updated_at' => '2026-02-16 18:08:05',
            ),
            7 => 
            array (
                'id' => 8,
                'name' => 'edit-blocks',
                'guard_name' => 'web',
                'created_at' => '2026-02-16 18:08:05',
                'updated_at' => '2026-02-16 18:08:05',
            ),
            8 => 
            array (
                'id' => 9,
                'name' => 'delete-blocks',
                'guard_name' => 'web',
                'created_at' => '2026-02-16 18:08:05',
                'updated_at' => '2026-02-16 18:08:05',
            ),
            9 => 
            array (
                'id' => 10,
                'name' => 'view-products',
                'guard_name' => 'web',
                'created_at' => '2026-02-16 18:08:05',
                'updated_at' => '2026-02-16 18:08:05',
            ),
            10 => 
            array (
                'id' => 11,
                'name' => 'create-products',
                'guard_name' => 'web',
                'created_at' => '2026-02-16 18:08:05',
                'updated_at' => '2026-02-16 18:08:05',
            ),
            11 => 
            array (
                'id' => 12,
                'name' => 'edit-products',
                'guard_name' => 'web',
                'created_at' => '2026-02-16 18:08:05',
                'updated_at' => '2026-02-16 18:08:05',
            ),
            12 => 
            array (
                'id' => 13,
                'name' => 'delete-products',
                'guard_name' => 'web',
                'created_at' => '2026-02-16 18:08:05',
                'updated_at' => '2026-02-16 18:08:05',
            ),
            13 => 
            array (
                'id' => 14,
                'name' => 'view-categories',
                'guard_name' => 'web',
                'created_at' => '2026-02-16 18:08:05',
                'updated_at' => '2026-02-16 18:08:05',
            ),
            14 => 
            array (
                'id' => 15,
                'name' => 'create-categories',
                'guard_name' => 'web',
                'created_at' => '2026-02-16 18:08:05',
                'updated_at' => '2026-02-16 18:08:05',
            ),
            15 => 
            array (
                'id' => 16,
                'name' => 'edit-categories',
                'guard_name' => 'web',
                'created_at' => '2026-02-16 18:08:05',
                'updated_at' => '2026-02-16 18:08:05',
            ),
            16 => 
            array (
                'id' => 17,
                'name' => 'delete-categories',
                'guard_name' => 'web',
                'created_at' => '2026-02-16 18:08:05',
                'updated_at' => '2026-02-16 18:08:05',
            ),
            17 => 
            array (
                'id' => 18,
                'name' => 'view-brands',
                'guard_name' => 'web',
                'created_at' => '2026-02-16 18:08:05',
                'updated_at' => '2026-02-16 18:08:05',
            ),
            18 => 
            array (
                'id' => 19,
                'name' => 'create-brands',
                'guard_name' => 'web',
                'created_at' => '2026-02-16 18:08:05',
                'updated_at' => '2026-02-16 18:08:05',
            ),
            19 => 
            array (
                'id' => 20,
                'name' => 'edit-brands',
                'guard_name' => 'web',
                'created_at' => '2026-02-16 18:08:05',
                'updated_at' => '2026-02-16 18:08:05',
            ),
            20 => 
            array (
                'id' => 21,
                'name' => 'delete-brands',
                'guard_name' => 'web',
                'created_at' => '2026-02-16 18:08:05',
                'updated_at' => '2026-02-16 18:08:05',
            ),
            21 => 
            array (
                'id' => 22,
                'name' => 'view-media',
                'guard_name' => 'web',
                'created_at' => '2026-02-16 18:08:05',
                'updated_at' => '2026-02-16 18:08:05',
            ),
            22 => 
            array (
                'id' => 23,
                'name' => 'create-media',
                'guard_name' => 'web',
                'created_at' => '2026-02-16 18:08:05',
                'updated_at' => '2026-02-16 18:08:05',
            ),
            23 => 
            array (
                'id' => 24,
                'name' => 'edit-media',
                'guard_name' => 'web',
                'created_at' => '2026-02-16 18:08:05',
                'updated_at' => '2026-02-16 18:08:05',
            ),
            24 => 
            array (
                'id' => 25,
                'name' => 'delete-media',
                'guard_name' => 'web',
                'created_at' => '2026-02-16 18:08:05',
                'updated_at' => '2026-02-16 18:08:05',
            ),
            25 => 
            array (
                'id' => 26,
                'name' => 'view-forms',
                'guard_name' => 'web',
                'created_at' => '2026-02-16 18:08:05',
                'updated_at' => '2026-02-16 18:08:05',
            ),
            26 => 
            array (
                'id' => 27,
                'name' => 'delete-forms',
                'guard_name' => 'web',
                'created_at' => '2026-02-16 18:08:05',
                'updated_at' => '2026-02-16 18:08:05',
            ),
            27 => 
            array (
                'id' => 28,
                'name' => 'view-settings',
                'guard_name' => 'web',
                'created_at' => '2026-02-16 18:08:05',
                'updated_at' => '2026-02-16 18:08:05',
            ),
            28 => 
            array (
                'id' => 29,
                'name' => 'edit-settings',
                'guard_name' => 'web',
                'created_at' => '2026-02-16 18:08:05',
                'updated_at' => '2026-02-16 18:08:05',
            ),
            29 => 
            array (
                'id' => 30,
                'name' => 'view-users',
                'guard_name' => 'web',
                'created_at' => '2026-02-16 18:08:05',
                'updated_at' => '2026-02-16 18:08:05',
            ),
            30 => 
            array (
                'id' => 31,
                'name' => 'create-users',
                'guard_name' => 'web',
                'created_at' => '2026-02-16 18:08:05',
                'updated_at' => '2026-02-16 18:08:05',
            ),
            31 => 
            array (
                'id' => 32,
                'name' => 'edit-users',
                'guard_name' => 'web',
                'created_at' => '2026-02-16 18:08:05',
                'updated_at' => '2026-02-16 18:08:05',
            ),
            32 => 
            array (
                'id' => 33,
                'name' => 'delete-users',
                'guard_name' => 'web',
                'created_at' => '2026-02-16 18:08:05',
                'updated_at' => '2026-02-16 18:08:05',
            ),
        ));
        
        
    }
}