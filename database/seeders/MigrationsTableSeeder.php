<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MigrationsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('migrations')->delete();
        
        \DB::table('migrations')->insert(array (
            0 => 
            array (
                'id' => 1,
                'migration' => '2025_09_07_215642_create_blocks_table',
                'batch' => 1,
            ),
            1 => 
            array (
                'id' => 2,
                'migration' => '2025_09_07_215642_create_cache_locks_table',
                'batch' => 1,
            ),
            2 => 
            array (
                'id' => 3,
                'migration' => '2025_09_07_215642_create_cache_table',
                'batch' => 1,
            ),
            3 => 
            array (
                'id' => 4,
                'migration' => '2025_09_07_215642_create_failed_jobs_table',
                'batch' => 1,
            ),
            4 => 
            array (
                'id' => 5,
                'migration' => '2025_09_07_215642_create_form_submissions_table',
                'batch' => 1,
            ),
            5 => 
            array (
                'id' => 6,
                'migration' => '2025_09_07_215642_create_job_batches_table',
                'batch' => 1,
            ),
            6 => 
            array (
                'id' => 7,
                'migration' => '2025_09_07_215642_create_jobs_table',
                'batch' => 1,
            ),
            7 => 
            array (
                'id' => 8,
                'migration' => '2025_09_07_215642_create_model_has_permissions_table',
                'batch' => 1,
            ),
            8 => 
            array (
                'id' => 9,
                'migration' => '2025_09_07_215642_create_model_has_roles_table',
                'batch' => 1,
            ),
            9 => 
            array (
                'id' => 10,
                'migration' => '2025_09_07_215642_create_pages_table',
                'batch' => 1,
            ),
            10 => 
            array (
                'id' => 11,
                'migration' => '2025_09_07_215642_create_password_reset_tokens_table',
                'batch' => 1,
            ),
            11 => 
            array (
                'id' => 12,
                'migration' => '2025_09_07_215642_create_permissions_table',
                'batch' => 1,
            ),
            12 => 
            array (
                'id' => 13,
                'migration' => '2025_09_07_215642_create_role_has_permissions_table',
                'batch' => 1,
            ),
            13 => 
            array (
                'id' => 14,
                'migration' => '2025_09_07_215642_create_roles_table',
                'batch' => 1,
            ),
            14 => 
            array (
                'id' => 15,
                'migration' => '2025_09_07_215642_create_sessions_table',
                'batch' => 1,
            ),
            15 => 
            array (
                'id' => 16,
                'migration' => '2025_09_07_215642_create_settings_table',
                'batch' => 1,
            ),
            16 => 
            array (
                'id' => 17,
                'migration' => '2025_09_07_215642_create_users_table',
                'batch' => 1,
            ),
            17 => 
            array (
                'id' => 18,
                'migration' => '2025_09_07_215645_add_foreign_keys_to_blocks_table',
                'batch' => 1,
            ),
            18 => 
            array (
                'id' => 19,
                'migration' => '2025_09_07_215645_add_foreign_keys_to_model_has_permissions_table',
                'batch' => 1,
            ),
            19 => 
            array (
                'id' => 20,
                'migration' => '2025_09_07_215645_add_foreign_keys_to_model_has_roles_table',
                'batch' => 1,
            ),
            20 => 
            array (
                'id' => 21,
                'migration' => '2025_09_07_215645_add_foreign_keys_to_pages_table',
                'batch' => 1,
            ),
            21 => 
            array (
                'id' => 22,
                'migration' => '2025_09_07_215645_add_foreign_keys_to_role_has_permissions_table',
                'batch' => 1,
            ),
            22 => 
            array (
                'id' => 23,
                'migration' => '2025_09_19_114507_add_styles_to_blocks_table',
                'batch' => 1,
            ),
            23 => 
            array (
                'id' => 24,
                'migration' => '2025_09_26_132858_create_block_previews_table',
                'batch' => 1,
            ),
            24 => 
            array (
                'id' => 25,
                'migration' => '2025_11_16_022106_add_data_preview_to_blocks_table',
                'batch' => 1,
            ),
            25 => 
            array (
                'id' => 26,
                'migration' => '2025_11_16_231949_create_products_table',
                'batch' => 1,
            ),
            26 => 
            array (
                'id' => 27,
                'migration' => '2025_11_16_232027_create_projects_table',
                'batch' => 1,
            ),
            27 => 
            array (
                'id' => 28,
                'migration' => '2025_11_18_174606_create_media_table',
                'batch' => 1,
            ),
            28 => 
            array (
                'id' => 29,
                'migration' => '2025_11_18_174742_simplify_products_table',
                'batch' => 1,
            ),
            29 => 
            array (
                'id' => 30,
                'migration' => '2025_11_18_174757_simplify_projects_table',
                'batch' => 1,
            ),
            30 => 
            array (
                'id' => 31,
                'migration' => '2025_11_18_192912_add_icon_to_products_table',
                'batch' => 1,
            ),
            31 => 
            array (
                'id' => 32,
                'migration' => '2025_11_20_194342_add_excerpt_to_products_table',
                'batch' => 1,
            ),
            32 => 
            array (
                'id' => 33,
                'migration' => '2025_11_24_190650_create_categories_table',
                'batch' => 1,
            ),
            33 => 
            array (
                'id' => 34,
                'migration' => '2025_11_24_190757_add_category_id_to_products_table',
                'batch' => 1,
            ),
            34 => 
            array (
                'id' => 35,
                'migration' => '2025_11_28_103623_create_fatecha_blocks',
                'batch' => 1,
            ),
            35 => 
            array (
                'id' => 36,
                'migration' => '2026_02_13_130638_create_brands_table',
                'batch' => 1,
            ),
            36 => 
            array (
                'id' => 37,
                'migration' => '2026_02_13_130725_add_maquimport_fields_to_products_table',
                'batch' => 1,
            ),
            37 => 
            array (
                'id' => 38,
                'migration' => '2026_02_13_133352_import_settings_from_maquimport_old',
                'batch' => 1,
            ),
            38 => 
            array (
                'id' => 39,
                'migration' => '2026_02_13_182242_add_maquimport_fields_to_categories_table',
                'batch' => 1,
            ),
            39 => 
            array (
                'id' => 40,
                'migration' => '2026_02_14_223443_add_registration_fields_to_users_table',
                'batch' => 1,
            ),
            40 => 
            array (
                'id' => 41,
                'migration' => '2026_02_15_133949_update_menu_position_enum_in_pages_table',
                'batch' => 1,
            ),
            41 => 
            array (
                'id' => 42,
                'migration' => '2026_02_15_204507_update_products_table_for_target_eyewear',
                'batch' => 1,
            ),
            42 => 
            array (
                'id' => 43,
                'migration' => '2026_02_15_204618_add_slug_to_categories_table',
                'batch' => 1,
            ),
            43 => 
            array (
                'id' => 44,
                'migration' => '2026_02_16_172026_remove_icon_from_categories_table',
                'batch' => 2,
            ),
            44 => 
            array (
                'id' => 45,
                'migration' => '2026_02_16_212846_add_is_active_to_users_table',
                'batch' => 3,
            ),
            45 => 
            array (
                'id' => 46,
                'migration' => '2026_02_16_213327_remove_last_name_and_user_type_from_users_table',
                'batch' => 4,
            ),
            46 => 
            array (
                'id' => 47,
                'migration' => '2026_02_16_215616_create_blocks_table',
                'batch' => 0,
            ),
            47 => 
            array (
                'id' => 48,
                'migration' => '2026_02_16_215616_create_brands_table',
                'batch' => 0,
            ),
            48 => 
            array (
                'id' => 49,
                'migration' => '2026_02_16_215616_create_cache_table',
                'batch' => 0,
            ),
            49 => 
            array (
                'id' => 50,
                'migration' => '2026_02_16_215616_create_cache_locks_table',
                'batch' => 0,
            ),
            50 => 
            array (
                'id' => 51,
                'migration' => '2026_02_16_215616_create_categories_table',
                'batch' => 0,
            ),
            51 => 
            array (
                'id' => 52,
                'migration' => '2026_02_16_215616_create_failed_jobs_table',
                'batch' => 0,
            ),
            52 => 
            array (
                'id' => 53,
                'migration' => '2026_02_16_215616_create_form_submissions_table',
                'batch' => 0,
            ),
            53 => 
            array (
                'id' => 54,
                'migration' => '2026_02_16_215616_create_job_batches_table',
                'batch' => 0,
            ),
            54 => 
            array (
                'id' => 55,
                'migration' => '2026_02_16_215616_create_jobs_table',
                'batch' => 0,
            ),
            55 => 
            array (
                'id' => 56,
                'migration' => '2026_02_16_215616_create_media_table',
                'batch' => 0,
            ),
            56 => 
            array (
                'id' => 57,
                'migration' => '2026_02_16_215616_create_model_has_permissions_table',
                'batch' => 0,
            ),
            57 => 
            array (
                'id' => 58,
                'migration' => '2026_02_16_215616_create_model_has_roles_table',
                'batch' => 0,
            ),
            58 => 
            array (
                'id' => 59,
                'migration' => '2026_02_16_215616_create_pages_table',
                'batch' => 0,
            ),
            59 => 
            array (
                'id' => 60,
                'migration' => '2026_02_16_215616_create_password_reset_tokens_table',
                'batch' => 0,
            ),
            60 => 
            array (
                'id' => 61,
                'migration' => '2026_02_16_215616_create_permissions_table',
                'batch' => 0,
            ),
            61 => 
            array (
                'id' => 62,
                'migration' => '2026_02_16_215616_create_products_table',
                'batch' => 0,
            ),
            62 => 
            array (
                'id' => 63,
                'migration' => '2026_02_16_215616_create_role_has_permissions_table',
                'batch' => 0,
            ),
            63 => 
            array (
                'id' => 64,
                'migration' => '2026_02_16_215616_create_roles_table',
                'batch' => 0,
            ),
            64 => 
            array (
                'id' => 65,
                'migration' => '2026_02_16_215616_create_sessions_table',
                'batch' => 0,
            ),
            65 => 
            array (
                'id' => 66,
                'migration' => '2026_02_16_215616_create_settings_table',
                'batch' => 0,
            ),
            66 => 
            array (
                'id' => 67,
                'migration' => '2026_02_16_215616_create_users_table',
                'batch' => 0,
            ),
            67 => 
            array (
                'id' => 68,
                'migration' => '2026_02_16_215619_add_foreign_keys_to_blocks_table',
                'batch' => 0,
            ),
            68 => 
            array (
                'id' => 69,
                'migration' => '2026_02_16_215619_add_foreign_keys_to_model_has_permissions_table',
                'batch' => 0,
            ),
            69 => 
            array (
                'id' => 70,
                'migration' => '2026_02_16_215619_add_foreign_keys_to_model_has_roles_table',
                'batch' => 0,
            ),
            70 => 
            array (
                'id' => 71,
                'migration' => '2026_02_16_215619_add_foreign_keys_to_pages_table',
                'batch' => 0,
            ),
            71 => 
            array (
                'id' => 72,
                'migration' => '2026_02_16_215619_add_foreign_keys_to_products_table',
                'batch' => 0,
            ),
            72 => 
            array (
                'id' => 73,
                'migration' => '2026_02_16_215619_add_foreign_keys_to_role_has_permissions_table',
                'batch' => 0,
            ),
        ));
        
        
    }
}