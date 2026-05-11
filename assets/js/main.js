/**
 * AliStack Library Management System - Main Javascript
 * Handles DataTables, SweetAlert2, Toastr, AOS, and real-time polling
 */

document.addEventListener('DOMContentLoaded', function() {
    // 1. Initialize AOS Animation Library
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 600,
            once: true,
            offset: 50,
            easing: 'ease-in-out-cubic'
        });
    }

    // 2. Initialize DataTables
    if ($.fn.DataTable) {
        $('.data-table').DataTable({
            pageLength: 10,
            responsive: true,
            language: {
                search: "",
                searchPlaceholder: "Search records...",
                lengthMenu: "Show _MENU_ entries"
            },
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            drawCallback: function() {
                $('.dataTables_paginate > .pagination').addClass('pagination-sm');
            }
        });
    }

    // 3. Setup Toastr Options
    if (typeof toastr !== 'undefined') {
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "4000",
            "extendedTimeOut": "1000",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };
    }

    // 4. Sidebar Toggle Logic
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('main-content');
    const toggleBtn = document.getElementById('sidebarToggle');
    const overlay = document.getElementById('sidebarOverlay');

    function toggleSidebar() {
        sidebar.classList.toggle('active');
        if (window.innerWidth <= 992) {
            overlay.classList.toggle('active');
            document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
        }
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', toggleSidebar);
    }

    if (overlay) {
        overlay.addEventListener('click', toggleSidebar);
    }

    // Handle window resize for sidebar
    window.addEventListener('resize', function() {
        if (window.innerWidth > 992) {
            if (sidebar && sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
            }
            if (overlay && overlay.classList.contains('active')) {
                overlay.classList.remove('active');
            }
            document.body.style.overflow = '';
        }
    });

    // 5. File Upload Preview (Profile Image)
    const profileInput = document.querySelector('input[name="profile_image"]');
    if (profileInput) {
        profileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 5 * 1024 * 1024) {
                    Swal.fire({ icon: 'error', title: 'File too large', text: 'Maximum file size is 5MB' });
                    this.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewArea = document.querySelector('.card-lib-photo');
                    if (previewArea) {
                        previewArea.innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover" alt="Profile">`;
                    }
                }
                reader.readAsDataURL(file);
            }
        });
    }
});

// SweetAlert2 Delete Confirmation Wrapper
function confirmDelete(formId, itemName) {
    Swal.fire({
        title: 'Are you sure?',
        html: `You are about to delete <strong>${itemName}</strong>.<br>This action cannot be undone!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-trash me-2"></i>Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(formId).submit();
        }
    });
}
