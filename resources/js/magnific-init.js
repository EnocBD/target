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
            enabled: false, // Disabled for single image popup
            navigateByImgClick: true,
            preload: [0, 1]
        },
        image: {
            tError: '<a href="%url%">La imagen</a> no pudo cargarse.',
            titleSrc: function(item) {
                return item.el.attr('title');
            }
        },
        zoom: {
            enabled: true,
            duration: 300
        },
        callbacks: {
            beforeOpen: function() {
                // Add zoom animation
                this.st.mainClass = 'mfp-with-fade';
            }
        }
    });
};
