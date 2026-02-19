<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SessionsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('sessions')->delete();
        
        \DB::table('sessions')->insert(array (
            0 => 
            array (
                'id' => '4n5yIyCqcGuXvttKmNEAzNz944DWfTzJ3naeykt9',
                'user_id' => NULL,
                'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36',
                'payload' => 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoid1JrdGlKNFZ6bmUwVVE5ZDlpblVPVmtsRHZNVWEydEZsY1ZjRXhRQSI7czo4OiJsYXJhY2FydCI7YTozOntzOjg6Imluc3RhbmNlIjtzOjc6ImRlZmF1bHQiO3M6OToiaW5zdGFuY2VzIjthOjE6e2k6MDtzOjc6ImRlZmF1bHQiO31zOjc6ImRlZmF1bHQiO086MjI6Ikx1a2VQT0xPXExhcmFDYXJ0XENhcnQiOjg6e3M6MzoidGF4IjtOO3M6NDoiZmVlcyI7YTowOnt9czo2OiJsb2NhbGUiO3M6MTE6ImVuX1VTLlVURi04IjtzOjg6Imluc3RhbmNlIjtzOjc6ImRlZmF1bHQiO3M6NzoiY291cG9ucyI7YTowOnt9czoxMDoiYXR0cmlidXRlcyI7YTowOnt9czoxNToibXVsdGlwbGVDb3Vwb25zIjtiOjA7czoxMjoiY3VycmVuY3lDb2RlIjtzOjM6IlVTRCI7fX1zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czozNzoiaHR0cDovL2xvY2FsaG9zdC90YXJnZXQvY2FydC9jaGVja291dCI7czo1OiJyb3V0ZSI7czoxMzoiY2FydC5jaGVja291dCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',
                'last_activity' => 1771278510,
            ),
            1 => 
            array (
                'id' => 'SihUFaknZ3VYmKcv79DkNmmnNYHXwFEFBY3j81iJ',
                'user_id' => 1,
                'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36',
                'payload' => 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiemhYYjdsZ0dBYVpSVWVXbDEwUG43OHU5TmlBanBCc3VuamZVZHRWRiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzU6Imh0dHA6Ly9sb2NhbGhvc3QvdGFyZ2V0L2FkbWluL3VzZXJzIjtzOjU6InJvdXRlIjtzOjE3OiJhZG1pbi51c2Vycy5pbmRleCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NDoiYXV0aCI7YToxOntzOjIxOiJwYXNzd29yZF9jb25maXJtZWRfYXQiO2k6MTc3MTI3ODI4Mzt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9',
                'last_activity' => 1771278302,
            ),
        ));
        
        
    }
}