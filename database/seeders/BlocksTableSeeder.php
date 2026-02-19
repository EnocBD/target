<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class BlocksTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('blocks')->delete();
        
        \DB::table('blocks')->insert(array (
            0 => 
            array (
                'id' => 1,
                'page_id' => 1,
                'parent_id' => NULL,
                'block_type' => 'slides',
                'data' => '{"slides": [{"image": "/storage/blocks/slide-1.jpg", "title": "Descubre tu Estilo", "button_url": "/productos", "button_text": "Ver Productos", "description": "La mejor selección de lentes de sol y vista para ti", "button_color": "primary"}, {"image": "/storage/blocks/slide-2.jpg", "title": "Calidad Premium", "button_url": "/nosotros", "button_text": "Conocenos", "description": "Marcas reconocidas y materiales de primera calidad", "button_color": "success"}, {"image": "/storage/blocks/slide-3.jpg", "title": "Tu Visión es Primero", "button_url": "/contacto", "button_text": "Contactanos", "description": "Tecnología óptica avanzada para tu comodidad", "button_color": "info"}], "caption_bg": "bg-dark bg-opacity-50", "caption_position": ""}',
                'data_preview' => NULL,
                'styles' => NULL,
                'image_path' => NULL,
                'thumbnail_path' => NULL,
                'sort_order' => 1,
                'is_active' => 1,
                'created_at' => '2026-02-15 21:15:07',
                'updated_at' => '2026-02-15 21:15:07',
            ),
            1 => 
            array (
                'id' => 2,
                'page_id' => 1,
                'parent_id' => NULL,
                'block_type' => 'categories',
                'data' => '{"title": "Nuestras Categorías", "subtitle": "Encuentra el estilo perfecto para ti"}',
                'data_preview' => NULL,
                'styles' => NULL,
                'image_path' => NULL,
                'thumbnail_path' => NULL,
                'sort_order' => 2,
                'is_active' => 1,
                'created_at' => '2026-02-15 21:15:07',
                'updated_at' => '2026-02-15 21:15:07',
            ),
            2 => 
            array (
                'id' => 3,
                'page_id' => 1,
                'parent_id' => NULL,
                'block_type' => 'featured_products',
                'data' => '{"title": "Productos Destacados", "subtitle": "Nuestra selección de lentes más vendidos", "show_all_button": true}',
                'data_preview' => NULL,
                'styles' => NULL,
                'image_path' => NULL,
                'thumbnail_path' => NULL,
                'sort_order' => 3,
                'is_active' => 1,
                'created_at' => '2026-02-15 21:15:07',
                'updated_at' => '2026-02-15 21:15:07',
            ),
            3 => 
            array (
                'id' => 4,
                'page_id' => 2,
                'parent_id' => NULL,
                'block_type' => 'text',
                'data' => '{"title": "Sobre Target Eyewear", "content": "<h2>Nuestra Historia</h2><p>En Target Eyewear, nos dedicamos a ofrecer la mejor selección de lentes de sol, vista y contacto en Paraguay.</p>", "subtitle": "Tu visión, nuestra pasión desde 2020", "text_align": "text-center", "content_class": "fs-5", "background_class": "bg-light"}',
                'data_preview' => NULL,
                'styles' => NULL,
                'image_path' => NULL,
                'thumbnail_path' => NULL,
                'sort_order' => 1,
                'is_active' => 1,
                'created_at' => '2026-02-15 21:15:07',
                'updated_at' => '2026-02-15 21:15:07',
            ),
            4 => 
            array (
                'id' => 5,
                'page_id' => 3,
                'parent_id' => NULL,
                'block_type' => 'products',
                'data' => '{"title": "Nuestros Productos", "subtitle": "Explora nuestra colección completa de lentes"}',
                'data_preview' => NULL,
                'styles' => NULL,
                'image_path' => NULL,
                'thumbnail_path' => NULL,
                'sort_order' => 1,
                'is_active' => 1,
                'created_at' => '2026-02-15 21:15:07',
                'updated_at' => '2026-02-15 21:15:07',
            ),
            5 => 
            array (
                'id' => 6,
                'page_id' => 4,
                'parent_id' => NULL,
                'block_type' => 'text',
                'data' => '{"title": "Contáctanos", "content": "<p>¿Tienes alguna pregunta sobre nuestros productos? No dudes en contactarnos.</p>", "subtitle": "Estamos aquí para ayudarte", "text_align": "text-center", "content_class": "fs-5", "background_class": ""}',
                'data_preview' => NULL,
                'styles' => NULL,
                'image_path' => NULL,
                'thumbnail_path' => NULL,
                'sort_order' => 1,
                'is_active' => 1,
                'created_at' => '2026-02-15 21:15:07',
                'updated_at' => '2026-02-15 21:15:07',
            ),
            6 => 
            array (
                'id' => 7,
                'page_id' => 4,
                'parent_id' => NULL,
                'block_type' => 'contact_info',
                'data' => '{"phone": "+595 981 000 000", "title": "Información de Contacto", "address": "Asunción, Paraguay", "facebook": "https://facebook.com/targeteyewear", "instagram": "https://instagram.com/targeteyewear"}',
                'data_preview' => NULL,
                'styles' => NULL,
                'image_path' => NULL,
                'thumbnail_path' => NULL,
                'sort_order' => 2,
                'is_active' => 1,
                'created_at' => '2026-02-15 21:15:07',
                'updated_at' => '2026-02-15 21:15:07',
            ),
            7 => 
            array (
                'id' => 8,
                'page_id' => 4,
                'parent_id' => NULL,
                'block_type' => 'contact_form',
                'data' => '{"title": "Envíanos un Mensaje", "button_text": "Enviar Mensaje", "email_recipient": "info@target.com.py", "success_message": "¡Gracias por contactarnos!"}',
                'data_preview' => NULL,
                'styles' => NULL,
                'image_path' => NULL,
                'thumbnail_path' => NULL,
                'sort_order' => 3,
                'is_active' => 1,
                'created_at' => '2026-02-15 21:15:07',
                'updated_at' => '2026-02-15 21:15:07',
            ),
            8 => 
            array (
                'id' => 9,
                'page_id' => 5,
                'parent_id' => NULL,
                'block_type' => 'text',
                'data' => '{"title": "Términos y Condiciones", "content": "<h2>Términos y Condiciones</h2><p>Bienvenido a Target Eyewear.</p><h2>Política de Privacidad</h2>", "subtitle": "Política de Privacidad", "text_align": "text-start", "content_class": "fs-6", "background_class": ""}',
                'data_preview' => NULL,
                'styles' => NULL,
                'image_path' => NULL,
                'thumbnail_path' => NULL,
                'sort_order' => 1,
                'is_active' => 1,
                'created_at' => '2026-02-15 21:15:07',
                'updated_at' => '2026-02-15 21:15:07',
            ),
        ));
        
        
    }
}