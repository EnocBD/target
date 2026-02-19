/**
 * Cart Module - Async Cart Operations
 */

const Cart = {
    apiUrl: window.AppConfig?.apiUrl || '/cart/api',

    /**
     * Make API request
     */
    async request(action, data = {}) {
        try {
            const formData = new FormData();
            formData.append('action', action);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

            Object.keys(data).forEach(key => {
                formData.append(key, data[key]);
            });

            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
            });

            if (!response.ok) {
                throw new Error('Error en la operación');
            }

            return await response.json();
        } catch (error) {
            console.error('Error:', error);
            this.showNotification('Error al procesar la solicitud', 'error');
            return null;
        }
    },

    /**
     * Add item to cart
     */
    async add(productId, qty = 1) {
        const data = await this.request('add', { product_id: productId, qty: qty });

        if (data && data.success) {
            this.updateCartCount(data.count);
            this.showNotification(data.message, 'success');
        }

        return data;
    },

    /**
     * Update cart item quantity
     */
    async update(itemId, qty) {
        const data = await this.request('update', { item_id: itemId, qty: qty });

        if (data && data.success) {
            this.updateCartCount(data.count);
            this.renderCartItems(data.items);
            this.renderCartSummary(data);
            this.showNotification(data.message, 'success');
        }

        return data;
    },

    /**
     * Remove item from cart
     */
    async remove(itemId) {
        const data = await this.request('remove', { item_id: itemId });

        if (data && data.success) {
            this.updateCartCount(data.count);

            if (data.count === 0) {
                window.location.reload();
                return;
            }

            this.renderCartItems(data.items);
            this.renderCartSummary(data);
            this.showNotification(data.message, 'success');
        }

        return data;
    },

    /**
     * Clear cart
     */
    async clear() {
        if (!confirm('¿Está seguro de vaciar el carrito?')) {
            return;
        }

        const data = await this.request('clear');

        if (data && data.success) {
            this.updateCartCount(0);
            window.location.reload();
        }

        return data;
    },

    /**
     * Update cart count in navbar
     */
    updateCartCount(count) {
        document.querySelectorAll('.cart-count').forEach(badge => {
            badge.textContent = count;
        });
    },

    /**
     * Render cart items table
     */
    renderCartItems(items) {
        const tbody = document.querySelector('#cart-items-body');
        if (!tbody) return;

        tbody.innerHTML = items.map(item => `
            <tr>
                <td>
                    <div class="d-flex align-items-center">
                        ${item.image
                            ? `<img src="${item.image}" alt="${item.name}" class="rounded me-3" style="width: 80px; height: 80px; object-fit: cover;">`
                            : `<div class="bg-secondary rounded me-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <i class="fas fa-image text-white fs-4"></i>
                               </div>`
                        }
                        <div>
                            ${item.options.slug
                                ? `<a href="${window.AppConfig?.routes?.products || '/productos'}/${item.options.slug}" class="text-decoration-none">
                                    <h6 class="mb-1">${item.name}</h6>
                                   </a>`
                                : `<h6 class="mb-1">${item.name}</h6>`
                            }
                            ${item.options.brand ? `<small class="text-muted"><i class="fas fa-tag"></i> ${item.options.brand}</small><br>` : ''}
                            ${item.options.sku ? `<small class="text-muted">SKU: ${item.options.sku}</small>` : ''}
                        </div>
                    </div>
                </td>
                <td>
                    <p class="mb-0 fw-bold">Gs. ${this.formatNumber(item.price)}</p>
                    ${item.options.list_price > item.price ? `<small class="text-muted text-decoration-line-through">Gs. ${this.formatNumber(item.options.list_price)}</small>` : ''}
                </td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <input type="number" value="${item.qty}" min="1" max="99"
                               class="form-control form-control-sm cart-qty-input"
                               style="width: 70px;" data-item-id="${item.hash}" required>
                        <button class="btn btn-sm btn-outline-primary cart-update-btn"
                                data-item-id="${item.hash}" title="Actualizar cantidad">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </td>
                <td class="text-end">
                    <p class="mb-0 fw-bold fs-5">Gs. ${this.formatNumber(item.subtotal)}</p>
                </td>
                <td>
                    <button class="btn btn-sm btn-outline-danger cart-remove-btn"
                            data-item-id="${item.hash}" title="Eliminar del carrito">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
        `).join('');

        this.attachEventListeners();
    },

    /**
     * Render cart summary
     */
    renderCartSummary(data) {
        const container = document.querySelector('#cart-summary');
        if (!container) return;

        container.innerHTML = `
            <hr>
            <div class="d-flex justify-content-between mb-2">
                <span>Subtotal:</span>
                <strong>Gs. ${this.formatNumber(data.subTotal)}</strong>
            </div>
            ${data.tax > 0 ? `
                <div class="d-flex justify-content-between mb-2">
                    <span>IVA (10%):</span>
                    <strong>Gs. ${this.formatNumber(data.tax)}</strong>
                </div>
            ` : ''}
            ${data.subTotal != data.total ? `
                <div class="d-flex justify-content-between mb-2 text-success">
                    <span>Descuento:</span>
                    <strong>- Gs. ${this.formatNumber(data.subTotal - (data.total - data.tax))}</strong>
                </div>
            ` : ''}
            <hr>
            <div class="d-flex justify-content-between mb-4">
                <span class="fs-5">Total:</span>
                <strong class="fs-4 text-primary">Gs. ${this.formatNumber(data.total)}</strong>
            </div>
            <a href="${window.AppConfig?.routes?.checkout || '/cart/checkout'}" class="btn btn-success btn-lg w-100">
                <i class="fab fa-whatsapp"></i> Proceder al Checkout
            </a>
            <p class="text-center text-muted small mt-3 mb-0">
                <i class="fas fa-shield-alt"></i> Compra segura garantizada
            </p>
        `;
    },

    /**
     * Attach event listeners
     */
    attachEventListeners() {
        // Update buttons
        document.querySelectorAll('.cart-update-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const itemId = btn.dataset.itemId;
                const input = document.querySelector(`input[data-item-id="${itemId}"]`);
                const qty = input.value;
                this.update(itemId, qty);
            });
        });

        // Remove buttons
        document.querySelectorAll('.cart-remove-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const itemId = btn.dataset.itemId;
                if (confirm('¿Está seguro de eliminar este producto del carrito?')) {
                    this.remove(itemId);
                }
            });
        });
    },

    /**
     * Show notification
     */
    showNotification(message, type = 'success') {
        document.querySelectorAll('.cart-notification').forEach(el => el.remove());

        const notification = document.createElement('div');
        notification.className = `cart-notification alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 3000);
    },

    /**
     * Format number
     */
    formatNumber(number) {
        return new Intl.NumberFormat('es-PY', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(number);
    },

    /**
     * Initialize
     */
    init() {
        // Add to cart forms
        document.querySelectorAll('.add-to-cart-form').forEach(form => {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(form);
                await this.add(formData.get('product_id'), formData.get('qty'));
            });
        });

        // Clear cart forms
        document.querySelectorAll('.clear-cart-form').forEach(form => {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                await this.clear();
            });
        });

        // Cart page buttons
        this.attachEventListeners();
    },
};

// Initialize
document.addEventListener('DOMContentLoaded', () => Cart.init());
window.Cart = Cart;
