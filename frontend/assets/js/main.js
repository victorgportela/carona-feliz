// Configurações globais
const API_BASE_URL = 'http://localhost:8000/api';

// Utility functions
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR');
}

function formatTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
}

function formatPrice(price) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(price);
}

// Loading spinner
function showLoading(element) {
    element.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i><br>Carregando...</div>';
}

function hideLoading() {
    document.querySelectorAll('.loading').forEach(el => el.remove());
}

// Toast notifications
function showToast(message, type = 'success') {
    const toastContainer = document.getElementById('toast-container') || createToastContainer();
    
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    toastContainer.appendChild(toast);
    
    const bsToast = new bootstrap.Toast(toast, { delay: 5000 });
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', () => toast.remove());
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = '1055';
    document.body.appendChild(container);
    return container;
}

// Modal helpers
function showConfirmModal(title, message, onConfirm) {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.innerHTML = `
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">${title}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    ${message}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirm-btn">Confirmar</button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    
    modal.querySelector('#confirm-btn').addEventListener('click', () => {
        bsModal.hide();
        onConfirm();
    });
    
    modal.addEventListener('hidden.bs.modal', () => modal.remove());
}

// Image preview
function previewImages(input, previewContainer) {
    const files = Array.from(input.files);
    previewContainer.innerHTML = '';
    
    files.forEach((file, index) => {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.createElement('div');
                preview.className = 'col-md-3 mb-3';
                preview.innerHTML = `
                    <div class="position-relative">
                        <img src="${e.target.result}" class="img-fluid rounded" style="height: 120px; width: 100%; object-fit: cover;">
                        <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1" onclick="removeImage(this, ${index})">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
                previewContainer.appendChild(preview);
            };
            reader.readAsDataURL(file);
        }
    });
}

function removeImage(button, index) {
    const input = document.querySelector('input[type="file"]');
    const dt = new DataTransfer();
    
    Array.from(input.files).forEach((file, i) => {
        if (i !== index) {
            dt.items.add(file);
        }
    });
    
    input.files = dt.files;
    button.closest('.col-md-3').remove();
}

// Form validation
function validateForm(form) {
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
        
        // Email validation
        if (input.type === 'email' && input.value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(input.value)) {
                input.classList.add('is-invalid');
                isValid = false;
            }
        }
    });
    
    return isValid;
}

// Auto-hide alerts
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            if (alert.parentNode) {
                alert.classList.add('fade');
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 150);
            }
        }, 5000);
    });
});

// Image error handling
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('img[data-fallback]').forEach(img => {
        img.addEventListener('error', function() {
            this.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA4MCA2MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjgwIiBoZWlnaHQ9IjYwIiBmaWxsPSIjRjVGNUY1Ii8+CjxwYXRoIGQ9Ik00MCAzMEM0My4zMTM3IDMwIDQ2IDI3LjMxMzcgNDYgMjRDNDYgMjAuNjg2MyA0My4zMTM3IDE4IDQwIDE4QzM2LjY4NjMgMTggMzQgMjAuNjg2MyAzNCAyNEMzNCAyNy4zMTM3IDM2LjY4NjMgMzAgNDAgMzBaIiBmaWxsPSIjOTg5OTlBIi8+CjxwYXRoIGQ9Ik0yOCA0Mkg1MkM1My4xMDQ2IDQyIDU0IDQxLjEwNDYgNTQgNDBWMzZDNTQgMzQuODk1NCA1My4xMDQ2IDM0IDUyIDM0SDI4QzI2Ljg5NTQgMzQgMjYgMzQuODk1NCAyNiAzNlY0MEMyNiA0MS4xMDQ2IDI2Ljg5NTQgNDIgMjggNDJaIiBmaWxsPSIjOTg5OTlBIi8+Cjx0ZXh0IHg9IjQwIiB5PSI1MCIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXNpemU9IjEwIiBmaWxsPSIjOTg5OTlBIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIj5TZW0gaW1hZ2VtPC90ZXh0Pgo8L3N2Zz4K';
            this.alt = 'Imagem não disponível';
        });
    });
}); 