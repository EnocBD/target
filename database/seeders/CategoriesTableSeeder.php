<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CategoriesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('categories')->delete();
        
        \DB::table('categories')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'Anteojos de sol',
                'slug' => 'sol',
                'excerpt' => 'Colección de Anteojos de sol',
                'description' => 'Descubre nuestra selección de Anteojos de sol de las mejores marcas.',
                'sort_order' => 0,
                'is_active' => 1,
                'created_at' => '2026-02-16 14:30:22',
                'updated_at' => '2026-02-16 14:30:22',
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'Anteojos de receta',
                'slug' => 'receta',
                'excerpt' => 'Colección de Anteojos de receta',
                'description' => 'Descubre nuestra selección de Anteojos de receta de las mejores marcas.',
                'sort_order' => 0,
                'is_active' => 1,
                'created_at' => '2026-02-16 14:30:22',
                'updated_at' => '2026-02-16 14:30:22',
            ),
            2 => 
            array (
                'id' => 3,
                'name' => 'Anteojos deportivos',
                'slug' => 'deportivo',
                'excerpt' => 'Colección de Anteojos deportivos',
                'description' => 'Descubre nuestra selección de Anteojos deportivos de las mejores marcas.',
                'sort_order' => 0,
                'is_active' => 1,
                'created_at' => '2026-02-16 14:30:22',
                'updated_at' => '2026-02-16 14:30:22',
            ),
            3 => 
            array (
                'id' => 4,
                'name' => 'Anteojos infantiles',
                'slug' => 'infantil',
                'excerpt' => 'Colección de Anteojos infantiles',
                'description' => 'Descubre nuestra selección de Anteojos infantiles de las mejores marcas.',
                'sort_order' => 0,
                'is_active' => 1,
                'created_at' => '2026-02-16 14:30:22',
                'updated_at' => '2026-02-16 14:30:22',
            ),
        ));
        
        
    }
}