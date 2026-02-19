<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PagesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('pages')->delete();
        
        \DB::table('pages')->insert(array (
            0 => 
            array (
                'id' => 1,
                'title' => 'Inicio',
                'content' => NULL,
                'slug' => '/',
                'meta_title' => NULL,
                'meta_description' => NULL,
                'sort_order' => 1,
                'is_active' => 1,
                'parent_id' => NULL,
                'menu_position' => 'both',
                'created_at' => '2026-02-15 21:13:08',
                'updated_at' => '2026-02-15 21:13:08',
            ),
            1 => 
            array (
                'id' => 2,
                'title' => 'Nosotros',
                'content' => NULL,
                'slug' => 'nosotros',
                'meta_title' => NULL,
                'meta_description' => NULL,
                'sort_order' => 2,
                'is_active' => 1,
                'parent_id' => NULL,
                'menu_position' => 'both',
                'created_at' => '2026-02-15 21:15:07',
                'updated_at' => '2026-02-15 21:15:07',
            ),
            2 => 
            array (
                'id' => 3,
                'title' => 'Productos',
                'content' => NULL,
                'slug' => 'productos',
                'meta_title' => NULL,
                'meta_description' => NULL,
                'sort_order' => 3,
                'is_active' => 1,
                'parent_id' => NULL,
                'menu_position' => 'both',
                'created_at' => '2026-02-15 21:15:07',
                'updated_at' => '2026-02-15 21:15:07',
            ),
            3 => 
            array (
                'id' => 4,
                'title' => 'Contacto',
                'content' => NULL,
                'slug' => 'contacto',
                'meta_title' => NULL,
                'meta_description' => NULL,
                'sort_order' => 4,
                'is_active' => 1,
                'parent_id' => NULL,
                'menu_position' => 'both',
                'created_at' => '2026-02-15 21:15:07',
                'updated_at' => '2026-02-15 21:15:07',
            ),
            4 => 
            array (
                'id' => 5,
                'title' => 'Términos y Condiciones',
                'content' => NULL,
                'slug' => 'terminos',
                'meta_title' => NULL,
                'meta_description' => NULL,
                'sort_order' => 5,
                'is_active' => 1,
                'parent_id' => NULL,
                'menu_position' => 'footer',
                'created_at' => '2026-02-15 21:15:07',
                'updated_at' => '2026-02-15 21:15:07',
            ),
        ));
        
        
    }
}