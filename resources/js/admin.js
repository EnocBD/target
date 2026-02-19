import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

// Register Alpine.js plugins
Alpine.plugin(collapse);

// Initialize Alpine.js
window.Alpine = Alpine;

// Import Quill and SortableJS loaders
import { initializeQuillEditor, loadQuillCSS } from './libs/quill-loader.js';
import { initializeSortable } from './libs/sortable-loader.js';

// Make Quill globally available for conditional loading
window.loadQuill = async function() {
    if (!window.Quill) {
        const { default: Quill } = await import('quill');
        window.Quill = Quill;
    }
    return window.Quill;
};

// Make SortableJS globally available for conditional loading  
window.loadSortable = async function() {
    if (!window.Sortable) {
        const { default: Sortable } = await import('sortablejs');
        window.Sortable = Sortable;
    }
    return window.Sortable;
};

// Make loaders globally available
window.initializeQuillEditor = initializeQuillEditor;
window.loadQuillCSS = loadQuillCSS;
window.initializeSortable = initializeSortable;

// Admin specific Alpine components
Alpine.data('sidebar', () => ({
    isOpen: false,
    toggle() {
        this.isOpen = !this.isOpen;
    },
    close() {
        this.isOpen = false;
    }
}));

Alpine.data('dropdown', () => ({
    isOpen: false,
    toggle() {
        this.isOpen = !this.isOpen;
    },
    close() {
        this.isOpen = false;
    }
}));

Alpine.data('alert', () => ({
    show: true,
    close() {
        this.show = false;
    }
}));

Alpine.data('modal', () => ({
    isOpen: false,
    open() {
        this.isOpen = true;
    },
    close() {
        this.isOpen = false;
    }
}));

// Start Alpine
Alpine.start();