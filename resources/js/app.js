import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    // Custom Toast Colors Configuration
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
        },
        // Custom styling for different toast types
        iconColor: 'white',
        customClass: {
            popup: 'colored-toast',
        }
    });

    // Add custom styles for colored toasts
    const style = document.createElement('style');
    document.head.appendChild(style);
    style.textContent = `
        .colored-toast {
            transform: translateY(1rem);
            transition: all 0.5s ease-in-out !important;
        }
        
        .colored-toast.swal2-icon-success {
            background: linear-gradient(to right, #047857, #10b981) !important;
            box-shadow: 0 8px 16px rgba(16, 185, 129, 0.2);
        }
        
        .colored-toast.swal2-icon-error {
            background: linear-gradient(to right, #dc2626, #ef4444) !important;
            box-shadow: 0 8px 16px rgba(239, 68, 68, 0.2);
        }
        
        .colored-toast.swal2-icon-warning {
            background: linear-gradient(to right, #d97706, #fbbf24) !important;
            box-shadow: 0 8px 16px rgba(251, 191, 36, 0.2);
        }
        
        .colored-toast.swal2-icon-info {
            background: linear-gradient(to right, #0284c7, #38bdf8) !important;
            box-shadow: 0 8px 16px rgba(56, 189, 248, 0.2);
        }
        
        .colored-toast.swal2-icon-question {
            background: linear-gradient(to right, #6366f1, #818cf8) !important;
            box-shadow: 0 8px 16px rgba(99, 102, 241, 0.2);
        }
        
        .colored-toast .swal2-title {
            color: white !important;
            font-size: 0.875rem !important;
            font-weight: 500 !important;
        }
        
        .colored-toast .swal2-close {
            color: white !important;
        }
        
        .colored-toast .swal2-html-container {
            color: white !important;
        }
        
        .toast-progress {
            background: rgba(255, 255, 255, 0.3) !important;
        }
        
        @keyframes swal2-toast-show {
            0% {
                transform: translateY(1rem);
                opacity: 0;
            }
            100% {
                transform: translateY(0);
                opacity: 1;
            }
        }
    `;

    // Listen for Livewire dispatched 'swal:toast' events
    Livewire.on('swal:toast', (data) => {
        let eventData = data;
        if (Array.isArray(data) && data.length > 0) {
            eventData = data[0];
        }

        Toast.fire({
            icon: eventData.type,
            title: eventData.title
        });
    });
});

// Ensure Alpine.js is started if you're using it with Livewire
// import Alpine from 'alpinejs';
// window.Alpine = Alpine;
// Alpine.start();
