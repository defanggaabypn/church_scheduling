// Custom JavaScript for the application

document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide flash messages after 5 seconds
    setTimeout(function() {
        let flashMessage = document.getElementById('msg-flash');
        if(flashMessage) {
            flashMessage.style.transition = 'opacity 1s';
            flashMessage.style.opacity = 0;
            
            setTimeout(function() {
                flashMessage.style.display = 'none';
            }, 1000);
        }
    }, 5000);
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
    
    // Form validation for booking form
    const bookingForm = document.querySelector('form[name="booking-form"]');
    if(bookingForm) {
        bookingForm.addEventListener('submit', function(e) {
            const startTime = document.querySelector('input[name="start_time"]').value;
            const endTime = document.querySelector('input[name="end_time"]').value;
            
            if(startTime >= endTime) {
                e.preventDefault();
                alert('Waktu selesai harus setelah waktu mulai');
            }
        });
    }
    
    // Date range picker for reports
    const dateRangePicker = document.getElementById('date-range-picker');
    if(dateRangePicker) {
        flatpickr(dateRangePicker, {
            mode: "range",
            dateFormat: "Y-m-d"
        });
    }
});

// Function to confirm deletion
function confirmDelete(message) {
    return confirm(message || 'Anda yakin ingin menghapus item ini?');
}

// Function to format date
function formatDate(dateString) {
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', options);
}

// Function to format time
function formatTime(timeString) {
    const time = new Date('2000-01-01T' + timeString);
    return time.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
}

// Function to get activity type name
function getActivityTypeName(activityType) {
    const activityTypes = {
        'pemuda': 'Pemuda',
        'pria': 'Pria',
        'wanita': 'Wanita',
        'sekolah_minggu': 'Sekolah Minggu',
        'rayon': 'Rayon',
        'tk_paud': 'TK & PAUD',
        'doa': 'Ibadah Doa',
        'minggu': 'Ibadah Minggu'
    };
    
    return activityTypes[activityType] || activityType;
}
