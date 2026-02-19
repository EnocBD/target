// Quill CSS loader for conditional loading
export async function loadQuillCSS() {
    if (!document.querySelector('link[href*="quill"]')) {
        const { default: quillCSS } = await import('quill/dist/quill.snow.css?url');
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = quillCSS;
        document.head.appendChild(link);
    }
}

// Initialize Quill with image upload handler
export async function initializeQuillEditor(selector, options = {}) {
    // Load Quill CSS first
    await loadQuillCSS();
    
    // Load Quill JS
    const Quill = await window.loadQuill();
    
    const defaultOptions = {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['link', 'image'],
                ['clean']
            ]
        },
        placeholder: 'Escribir contenido...',
        ...options
    };
    
    const editor = new Quill(selector, defaultOptions);
    
    // Add custom image handler if upload URL is provided
    if (options.uploadUrl) {
        const toolbar = editor.getModule('toolbar');
        toolbar.addHandler('image', function() {
            const input = document.createElement('input');
            input.setAttribute('type', 'file');
            input.setAttribute('accept', 'image/*');
            input.click();

            input.onchange = async function() {
                const file = input.files[0];
                if (file) {
                    await uploadQuillImage(file, editor, options.uploadUrl);
                }
            };
        });
    }
    
    return editor;
}

// Upload image for Quill editor
async function uploadQuillImage(file, quill, uploadUrl) {
    const formData = new FormData();
    formData.append('image', file);

    try {
        // Show loading state
        const range = quill.getSelection(true);
        quill.insertText(range.index, '[Subiendo imagen...]', 'user');
        quill.setSelection(range.index + '[Subiendo imagen...]'.length);

        const response = await fetch(uploadUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        const result = await response.json();

        // Remove loading text
        quill.deleteText(range.index, '[Subiendo imagen...]'.length);

        if (result.success) {
            // Insert the image
            quill.insertEmbed(range.index, 'image', result.url, 'user');
            quill.setSelection(range.index + 1);
        } else {
            alert('Error al subir la imagen: ' + (result.message || 'Error desconocido'));
        }
    } catch (error) {
        console.error('Error uploading image:', error);
        
        // Remove loading text on error
        const currentRange = quill.getSelection();
        if (currentRange) {
            const text = quill.getText(currentRange.index - '[Subiendo imagen...]'.length, '[Subiendo imagen...]'.length);
            if (text === '[Subiendo imagen...]') {
                quill.deleteText(currentRange.index - '[Subiendo imagen...]'.length, '[Subiendo imagen...]'.length);
            }
        }
        
        alert('Error al subir la imagen');
    }
}