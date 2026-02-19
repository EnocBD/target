// Initialize SortableJS with default options
export async function initializeSortable(element, options = {}) {
    const Sortable = await window.loadSortable();
    
    const defaultOptions = {
        animation: 150,
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        dragClass: 'sortable-drag',
        ...options
    };
    
    return new Sortable(element, defaultOptions);
}

// CSS classes for sortable elements (add to your CSS)
export const sortableCSS = `
.sortable-ghost {
    opacity: 0.4;
}

.sortable-chosen {
    transform: scale(1.05);
}

.sortable-drag {
    transform: rotate(5deg);
}

.sortable-handle {
    cursor: grab;
}

.sortable-handle:active {
    cursor: grabbing;
}
`;

// Add CSS if not already added
if (!document.getElementById('sortable-styles')) {
    const style = document.createElement('style');
    style.id = 'sortable-styles';
    style.textContent = sortableCSS;
    document.head.appendChild(style);
}