// Import jQuery and Magnific Popup
import $ from 'jquery';
import 'magnific-popup';

// Make sure jQuery is available globally if needed
window.$ = window.jQuery = $;

// Initialize Magnific Popup function
window.initMagnificPopup = function(selector) {
    $(selector).magnificPopup({
        type: 'image',
        gallery: {
            enabled: true,
            navigateByImgClick: true,
            preload: [0, 1],
            tPrev: 'Anterior (Tecla flecha izquierda)',
            tNext: 'Siguiente (Tecla flecha derecha)',
            tCounter: '%curr% de %total%'
        },
        image: {
            tError: '<a href="%url%">La imagen</a> no pudo cargarse.',
            titleSrc: function(item) {
                return item.el.attr('title');
            }
        },
        zoom: {
            enabled: false
        },
        removalDelay: 0,
        mainClass: '',
        closeBtnInside: true,
        closeOnContentClick: true,
        closeOnBgClick: true,
        showCloseBtn: true,
        enableEscapeKey: true,
        modal: false,
        tClose: 'Cerrar (Esc)',
        tLoading: 'Cargando...'
    });
};
