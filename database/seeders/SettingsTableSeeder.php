<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SettingsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('settings')->delete();
        
        \DB::table('settings')->insert(array (
            0 => 
            array (
                'id' => 1,
                'key' => 'site_name',
                'name' => NULL,
                'value' => 'Target Eyewear',
                'type' => 'text',
                'group' => 'general',
                'description' => NULL,
                'created_at' => '2026-02-15 21:13:08',
                'updated_at' => '2026-02-15 21:13:08',
            ),
            1 => 
            array (
                'id' => 2,
                'key' => 'site_description',
                'name' => NULL,
                'value' => 'Tu visión, nuestra pasión',
                'type' => 'text',
                'group' => 'general',
                'description' => NULL,
                'created_at' => '2026-02-15 21:13:08',
                'updated_at' => '2026-02-15 21:13:08',
            ),
            2 => 
            array (
                'id' => 3,
                'key' => 'site_email',
                'name' => NULL,
                'value' => 'info@target.com.py',
                'type' => 'text',
                'group' => 'general',
                'description' => NULL,
                'created_at' => '2026-02-15 21:13:08',
                'updated_at' => '2026-02-15 21:13:08',
            ),
            3 => 
            array (
                'id' => 4,
                'key' => 'site_phone',
                'name' => NULL,
                'value' => '+595 981 000 000',
                'type' => 'text',
                'group' => 'general',
                'description' => NULL,
                'created_at' => '2026-02-15 21:13:08',
                'updated_at' => '2026-02-15 21:13:08',
            ),
            4 => 
            array (
                'id' => 5,
                'key' => 'site_address',
                'name' => NULL,
                'value' => 'Asunción, Paraguay',
                'type' => 'text',
                'group' => 'general',
                'description' => NULL,
                'created_at' => '2026-02-15 21:13:08',
                'updated_at' => '2026-02-15 21:13:08',
            ),
            5 => 
            array (
                'id' => 6,
                'key' => 'facebook_url',
                'name' => NULL,
                'value' => 'https://facebook.com/targeteyewear',
                'type' => 'text',
                'group' => 'general',
                'description' => NULL,
                'created_at' => '2026-02-15 21:13:08',
                'updated_at' => '2026-02-15 21:13:08',
            ),
            6 => 
            array (
                'id' => 7,
                'key' => 'instagram_url',
                'name' => NULL,
                'value' => 'https://instagram.com/targeteyewear',
                'type' => 'text',
                'group' => 'general',
                'description' => NULL,
                'created_at' => '2026-02-15 21:13:08',
                'updated_at' => '2026-02-15 21:13:08',
            ),
            7 => 
            array (
                'id' => 8,
                'key' => 'whatsapp_number',
                'name' => NULL,
                'value' => '595981000000',
                'type' => 'text',
                'group' => 'general',
                'description' => NULL,
                'created_at' => '2026-02-15 21:13:08',
                'updated_at' => '2026-02-15 21:13:08',
            ),
        ));
        
        
    }
}