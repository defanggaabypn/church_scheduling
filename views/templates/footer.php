    </div><!-- /.container -->
    
    <footer class="bg-dark text-white mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6 mb-3 mb-md-0">
                    <h5 class="mb-2">Gereja Baptis Syalom Karor</h5>
                    <p class="mb-0">Aplikasi Penjadwalan Penggunaan Gedung Gereja dengan Algoritma Genetika</p>
                </div>
                <div class="col-md-6 text-md-right">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> - Gereja Baptis Syalom Karor</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom JS -->
    <script src="<?php echo $assets_path; ?>js/script.js"></script>
    
    <script>
    // Perbaikan responsivitas untuk dropdown pada perangkat mobile
    $(document).ready(function() {
        if (window.innerWidth < 992) {
            $('.dropdown-toggle').on('click', function(e) {
                e.preventDefault();
                $(this).next('.dropdown-menu').slideToggle();
            });
        }
        
        // Menambahkan kelas responsif ke tombol kalender jika ada
        if (typeof FullCalendar !== 'undefined' && document.getElementById('calendar')) {
            setTimeout(function() {
                var buttons = document.querySelectorAll('.fc-button');
                buttons.forEach(function(button) {
                    button.classList.add('btn-sm');
                });
            }, 100);
        }
    });
    </script>
</body>
</html>
