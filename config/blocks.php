<?php

return [
    'layouts' => [
        // Target Eyewear Blocks
        'slides' => [
            'name' => 'Slides - Target Eyewear',
            'description' => 'Carrusel de imágenes con título, descripción y botón',
            'fields' => [
                'slides' => [
                    'type' => 'repeater',
                    'label' => 'Slides',
                    'min_items' => 1,
                    'max_items' => 5,
                    'fields' => [
                        'image' => [
                            'type' => 'image_upload',
                            'label' => 'Imagen del Slide (Desktop)',
                            'required' => true,
                            'accept' => 'image/*',
                            'max_size' => '5MB',
                        ],
                        'image_mobile' => [
                            'type' => 'image_upload',
                            'label' => 'Imagen del Slide (Móvil)',
                            'required' => false,
                            'accept' => 'image/*',
                            'max_size' => '5MB',
                        ],
                        'title' => [
                            'type' => 'text',
                            'label' => 'Título',
                            'required' => false,
                        ],
                        'description' => [
                            'type' => 'textarea',
                            'label' => 'Descripción',
                            'required' => false,
                        ],
                        'button_text' => [
                            'type' => 'text',
                            'label' => 'Texto del Botón',
                            'required' => false,
                        ],
                        'button_url' => [
                            'type' => 'text',
                            'label' => 'URL del Botón',
                            'required' => false,
                        ],
                        'button_color' => [
                            'type' => 'select',
                            'label' => 'Color del Botón',
                            'required' => false,
                            'default' => 'primary',
                            'options' => [
                                'primary' => 'Primario (Azul)',
                                'secondary' => 'Secundario (Gris)',
                                'success' => 'Éxito (Verde)',
                                'danger' => 'Peligro (Rojo)',
                                'warning' => 'Advertencia (Amarillo)',
                                'info' => 'Info (Cian)',
                                'light' => 'Claro',
                                'dark' => 'Oscuro',
                            ],
                        ],
                    ],
                ],
                'caption_bg' => [
                    'type' => 'select',
                    'label' => 'Fondo del Texto',
                    'required' => false,
                    'default' => 'bg-dark bg-opacity-50',
                    'options' => [
                        'bg-dark bg-opacity-50' => 'Oscuro transparente',
                        'bg-light bg-opacity-75' => 'Claro transparente',
                        'bg-primary bg-opacity-75' => 'Primario transparente',
                        '' => 'Sin fondo',
                    ],
                ],
                'caption_position' => [
                    'type' => 'select',
                    'label' => 'Posición del Texto',
                    'required' => false,
                    'default' => '',
                    'options' => [
                        '' => 'Centro',
                        'text-start' => 'Izquierda',
                        'text-end' => 'Derecha',
                    ],
                ],
            ],
        ],

        'categories' => [
            'name' => 'Categorías - Target Eyewear',
            'description' => 'Grid de categorías con iconos y enlaces',
            'fields' => [
                'title' => [
                    'type' => 'text',
                    'label' => 'Título',
                    'required' => false,
                    'default' => 'Nuestras Categorías',
                ],
                'subtitle' => [
                    'type' => 'text',
                    'label' => 'Subtítulo',
                    'required' => false,
                ],
            ],
        ],

        'featured_products' => [
            'name' => 'Productos Destacados - Target Eyewear',
            'description' => 'Grid de productos destacados con botón de agregar al carrito',
            'fields' => [
                'title' => [
                    'type' => 'text',
                    'label' => 'Título',
                    'required' => false,
                    'default' => 'Productos Destacados',
                ],
                'subtitle' => [
                    'type' => 'text',
                    'label' => 'Subtítulo',
                    'required' => false,
                ],
                'show_all_button' => [
                    'type' => 'checkbox',
                    'label' => 'Mostrar botón "Ver todos"',
                    'required' => false,
                    'default' => true,
                ],
            ],
        ],

        'products' => [
            'name' => 'Catálogo de Productos - Target Eyewear',
            'description' => 'Catálogo completo con filtros por categoría, marca y precio',
            'fields' => [
                'title' => [
                    'type' => 'text',
                    'label' => 'Título',
                    'required' => false,
                    'default' => 'Nuestros Productos',
                ],
                'subtitle' => [
                    'type' => 'text',
                    'label' => 'Subtítulo',
                    'required' => false,
                ],
            ],
        ],

        'text' => [
            'name' => 'Texto Enriquecido - Target Eyewear',
            'description' => 'Bloque de texto con editor WYSIWYG',
            'fields' => [
                'title' => [
                    'type' => 'text',
                    'label' => 'Título',
                    'required' => false,
                ],
                'subtitle' => [
                    'type' => 'text',
                    'label' => 'Subtítulo',
                    'required' => false,
                ],
                'content' => [
                    'type' => 'editor',
                    'label' => 'Contenido',
                    'required' => false,
                ],
                'background_class' => [
                    'type' => 'select',
                    'label' => 'Fondo de la Sección',
                    'required' => false,
                    'default' => '',
                    'options' => [
                        '' => 'Blanco',
                        'bg-light' => 'Gris claro',
                        'bg-dark text-white' => 'Oscuro',
                        'bg-primary text-white' => 'Primario',
                    ],
                ],
                'text_align' => [
                    'type' => 'select',
                    'label' => 'Alineación del Texto',
                    'required' => false,
                    'default' => 'text-center',
                    'options' => [
                        'text-center' => 'Centro',
                        'text-start' => 'Izquierda',
                        'text-end' => 'Derecha',
                    ],
                ],
                'content_class' => [
                    'type' => 'text',
                    'label' => 'Clases CSS Adicionales (opcional)',
                    'required' => false,
                    'placeholder' => 'ej: fs-5 fw-bold',
                ],
                'button_text' => [
                    'type' => 'text',
                    'label' => 'Texto del Botón (opcional)',
                    'required' => false,
                ],
                'button_url' => [
                    'type' => 'text',
                    'label' => 'URL del Botón',
                    'required' => false,
                ],
                'button_color' => [
                    'type' => 'select',
                    'label' => 'Color del Botón',
                    'required' => false,
                    'default' => 'primary',
                    'options' => [
                        'primary' => 'Primario (Azul)',
                        'secondary' => 'Secundario (Gris)',
                        'success' => 'Éxito (Verde)',
                        'danger' => 'Peligro (Rojo)',
                        'outline-primary' => 'Primario outline',
                        'outline-secondary' => 'Secundario outline',
                    ],
                ],
            ],
        ],

        'contact_info' => [
            'name' => 'Información de Contacto - Target Eyewear',
            'description' => 'Muestra teléfono, dirección y redes sociales',
            'fields' => [
                'title' => [
                    'type' => 'text',
                    'label' => 'Título',
                    'required' => false,
                    'default' => 'Información de Contacto',
                ],
                'phone' => [
                    'type' => 'text',
                    'label' => 'Teléfono',
                    'required' => false,
                    'placeholder' => '+595 981 000 000',
                ],
                'address' => [
                    'type' => 'textarea',
                    'label' => 'Dirección',
                    'required' => false,
                ],
                'maps' => [
                    'type' => 'text',
                    'label' => 'URL de Google Maps (opcional)',
                    'required' => false,
                    'placeholder' => 'https://maps.google.com/...',
                ],
                'facebook' => [
                    'type' => 'text',
                    'label' => 'Facebook URL',
                    'required' => false,
                    'placeholder' => 'https://facebook.com/targeteyewear',
                ],
                'instagram' => [
                    'type' => 'text',
                    'label' => 'Instagram URL',
                    'required' => false,
                    'placeholder' => 'https://instagram.com/targeteyewear',
                ],
                'youtube' => [
                    'type' => 'text',
                    'label' => 'YouTube URL',
                    'required' => false,
                ],
            ],
        ],

        'contact_form' => [
            'name' => 'Formulario de Contacto - Target Eyewear',
            'description' => 'Formulario con mapa de Leaflet',
            'fields' => [
                'title' => [
                    'type' => 'text',
                    'label' => 'Título',
                    'required' => false,
                    'default' => 'Envíanos un Mensaje',
                ],
                'description' => [
                    'type' => 'textarea',
                    'label' => 'Descripción',
                    'required' => false,
                ],
                'button_text' => [
                    'type' => 'text',
                    'label' => 'Texto del Botón',
                    'required' => false,
                    'default' => 'Enviar Mensaje',
                ],
                'producto' => [
                    'type' => 'text',
                    'label' => 'Producto (opcional, se agrega como campo oculto)',
                    'required' => false,
                ],
                'subject' => [
                    'type' => 'text',
                    'label' => 'Asunto (opcional, pre-llena el campo)',
                    'required' => false,
                ],
                'email_recipient' => [
                    'type' => 'text',
                    'label' => 'Email Destinatario (campo oculto)',
                    'required' => false,
                ],
                'email_subject' => [
                    'type' => 'text',
                    'label' => 'Asunto del Email (campo oculto)',
                    'required' => false,
                ],
                'success_message' => [
                    'type' => 'textarea',
                    'label' => 'Mensaje de Éxito',
                    'required' => false,
                ],
                'lat' => [
                    'type' => 'text',
                    'label' => 'Latitud del Mapa',
                    'required' => false,
                    'default' => '-25.301015',
                ],
                'lng' => [
                    'type' => 'text',
                    'label' => 'Longitud del Mapa',
                    'required' => false,
                    'default' => '-57.631030',
                ],
            ],
        ],
    ],
];
