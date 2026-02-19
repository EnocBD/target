<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class BrandsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('brands')->delete();
        
        \DB::table('brands')->insert(array (
            0 => 
            array (
                'id' => 1,
                'code' => 'RAY',
                'name' => 'Ray-Ban',
                'slug' => 'ray-ban',
                'description' => 'Ray-Ban - Eyewear Collection',
                'image' => NULL,
                'is_active' => 1,
                'sort_order' => 0,
                'created_at' => '2026-02-16 14:30:22',
                'updated_at' => '2026-02-16 14:30:22',
            ),
            1 => 
            array (
                'id' => 2,
                'code' => 'OAK',
                'name' => 'Oakley',
                'slug' => 'oakley',
                'description' => 'Oakley - Eyewear Collection',
                'image' => NULL,
                'is_active' => 1,
                'sort_order' => 1,
                'created_at' => '2026-02-16 14:30:22',
                'updated_at' => '2026-02-16 14:30:22',
            ),
            2 => 
            array (
                'id' => 3,
                'code' => 'VOG',
                'name' => 'Vogue',
                'slug' => 'vogue',
                'description' => 'Vogue - Eyewear Collection',
                'image' => NULL,
                'is_active' => 1,
                'sort_order' => 2,
                'created_at' => '2026-02-16 14:30:22',
                'updated_at' => '2026-02-16 14:30:22',
            ),
            3 => 
            array (
                'id' => 4,
                'code' => 'TAR',
                'name' => 'Target',
                'slug' => 'target',
                'description' => 'Target - Eyewear Collection',
                'image' => NULL,
                'is_active' => 1,
                'sort_order' => 3,
                'created_at' => '2026-02-16 14:30:22',
                'updated_at' => '2026-02-16 14:30:22',
            ),
            4 => 
            array (
                'id' => 5,
                'code' => 'EMP',
                'name' => 'Emporio Armani',
                'slug' => 'emporio-armani',
                'description' => 'Emporio Armani - Eyewear Collection',
                'image' => NULL,
                'is_active' => 1,
                'sort_order' => 4,
                'created_at' => '2026-02-16 14:30:22',
                'updated_at' => '2026-02-16 14:30:22',
            ),
            5 => 
            array (
                'id' => 6,
                'code' => 'PRA',
                'name' => 'Prada',
                'slug' => 'prada',
                'description' => 'Prada - Eyewear Collection',
                'image' => NULL,
                'is_active' => 1,
                'sort_order' => 5,
                'created_at' => '2026-02-16 14:30:22',
                'updated_at' => '2026-02-16 14:30:22',
            ),
            6 => 
            array (
                'id' => 7,
                'code' => 'GUC',
                'name' => 'Gucci',
                'slug' => 'gucci',
                'description' => 'Gucci - Eyewear Collection',
                'image' => NULL,
                'is_active' => 1,
                'sort_order' => 6,
                'created_at' => '2026-02-16 14:30:22',
                'updated_at' => '2026-02-16 14:30:22',
            ),
            7 => 
            array (
                'id' => 8,
                'code' => 'DIO',
                'name' => 'Dior',
                'slug' => 'dior',
                'description' => 'Dior - Eyewear Collection',
                'image' => NULL,
                'is_active' => 1,
                'sort_order' => 7,
                'created_at' => '2026-02-16 14:30:22',
                'updated_at' => '2026-02-16 14:30:22',
            ),
            8 => 
            array (
                'id' => 9,
                'code' => 'CHA',
                'name' => 'Chanel',
                'slug' => 'chanel',
                'description' => 'Chanel - Eyewear Collection',
                'image' => NULL,
                'is_active' => 1,
                'sort_order' => 8,
                'created_at' => '2026-02-16 14:30:22',
                'updated_at' => '2026-02-16 14:30:22',
            ),
            9 => 
            array (
                'id' => 10,
                'code' => 'TOM',
                'name' => 'Tom Ford',
                'slug' => 'tom-ford',
                'description' => 'Tom Ford - Eyewear Collection',
                'image' => NULL,
                'is_active' => 1,
                'sort_order' => 9,
                'created_at' => '2026-02-16 14:30:22',
                'updated_at' => '2026-02-16 14:30:22',
            ),
        ));
        
        
    }
}