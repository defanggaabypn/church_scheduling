# Sistem Manajemen Pemesanan dan Penjadwalan

Sistem manajemen pemesanan dan penjadwalan berbasis web dengan optimasi jadwal menggunakan algoritma genetika.

## Struktur Proyek

├── .htaccess.txt
├── index.php                  # File utama aplikasi
├── pengujian.php              # File untuk pengujian
├── Presenting.sql             # File SQL database
│
├── assets/                    # Aset statis aplikasi
│   ├── css/
│   │   └── style.css
│   ├── img/
│   └── js/
│       └── script.js
│
├── config/                    # Konfigurasi aplikasi
│   ├── config.php             # Konfigurasi umum
│   └── database.php           # Konfigurasi database
│
├── controllers/               # Controller aplikasi
│   ├── AuthController.php     # Kontrol autentikasi
│   ├── BookingController.php  # Kontrol pemesanan
│   ├── DashboardController.php# Kontrol dashboard
│   ├── ScheduleController.php # Kontrol jadwal
│   └── UserController.php     # Kontrol pengguna
│
├── helpers/                   # Helper functions
│   ├── notification_helper.php# Helper notifikasi
│   ├── session_helper.php     # Helper session
│   ├── url_helper.php         # Helper URL
│   └── validation_helper.php  # Helper validasi
│
├── models/                    # Model data
│   ├── Booking.php            # Model pemesanan
│   ├── GeneticAlgorithm.php   # Model algoritma genetika
│   ├── Schedule.php           # Model jadwal
│   └── User.php               # Model pengguna
│
└── views/                     # Tampilan aplikasi
├── index.php              # Halaman utama
│
├── admin/                 # Tampilan admin
│   ├── add_user.php
│   ├── dashboard.php
│   ├── edit_user.php
│   ├── manage_bookings.php
│   └── manage_users.php
│
├── auth/                  # Tampilan autentikasi
│   ├── login.php
│   └── register.php
│
├── booking/               # Tampilan pemesanan
│   ├── create.php
│   ├── list.php
│   └── view.php
│
├── schedule/              # Tampilan jadwal
│   ├── calendar.php
│   └── optimize.php
│
├── templates/             # Template halaman
│   ├── footer.php
│   ├── header.php
│   └── sidebar.php
│
└── user/                  # Tampilan pengguna
└── dashboard.php

## Deskripsi

Aplikasi ini adalah sistem manajemen pemesanan dan penjadwalan yang dilengkapi dengan fitur optimasi jadwal menggunakan algoritma genetika. Sistem ini memiliki dua jenis pengguna utama: admin dan pengguna biasa.

### Fitur Utama

- Autentikasi: Sistem login dan registrasi pengguna
- Manajemen Pengguna: Admin dapat mengelola data pengguna
- Pemesanan: Pengguna dapat membuat, melihat, dan mengelola pemesanan
- Penjadwalan: Sistem penjadwalan dengan tampilan kalender
- Optimasi Jadwal: Menggunakan algoritma genetika untuk mengoptimalkan jadwal

## Algoritma Genetika untuk Optimasi Jadwal

Sistem ini menggunakan algoritma genetika untuk mengoptimalkan jadwal pemesanan dengan menghindari konflik jadwal dan mengalokasikan slot waktu alternatif jika diperlukan.

### Komponen Utama Algoritma

- Populasi: Kumpulan kromosom (solusi potensial) dengan ukuran default 50
- Kromosom: Representasi jadwal dalam bentuk array gen yang mengkodekan:
  - Gen include/exclude → Menentukan apakah booking dimasukkan (1) atau tidak (0)
  - Gen slot waktu → Menentukan slot waktu yang digunakan (0 = waktu asli, 1+ = slot alternatif)
- Fitness Function: Mengevaluasi kualitas jadwal berdasarkan:
  - Jumlah booking yang berhasil dijadwalkan
  - Penalti untuk konflik jadwal
  - Penalti kecil untuk penggunaan slot waktu alternatif
- Seleksi: Tournament selection
- Crossover: 0.8
- Mutasi: 0.2
- Elitisme: 5 kromosom terbaik disimpan setiap generasi

### Fitur Khusus

- Slot waktu alternatif
- Analisis konflik jadwal
- Preferensi slot waktu
- Statistik penggunaan slot waktu
- Visualisasi kromosom

### Parameter Algoritma

- Population Size: 50
- Max Generations: 100
- Crossover Rate: 0.8
- Mutation Rate: 0.2
- Elitism Count: 5

### Proses Optimasi

1. Inisialisasi Populasi
2. Evaluasi Fitness
3. Seleksi
4. Crossover
5. Mutasi
6. Elitisme
7. Iterasi hingga konvergen
8. Hasil terbaik

## Persyaratan Sistem

- PHP 7.4+
- MySQL 5.7+
- Web server (Apache/Nginx)

## Instalasi

    # 1. Clone repo
    git clone https://github.com/defanggaabypn/church_scheduling.git

    # 2. Import database
    mysql -u username -p nama_database < Presenting.sql

    # 3. Konfigurasi koneksi di config/database.php

    # 4. Atur permission
    chmod -R 755 .
    chmod -R 777 assets/img

    # 5. Akses via browser
    http://localhost/church_scheduling

## Struktur Database

### Tabel Utama
- users
- bookings
- schedules
- booking_history

### Skema Tabel Booking

    CREATE TABLE bookings (
      id INT PRIMARY KEY AUTO_INCREMENT,
      user_id INT,
      title VARCHAR(255),
      description TEXT,
      date DATE,
      start_time TIME,
      end_time TIME,
      scheduled_start_time TIME,
      scheduled_end_time TIME,
      status ENUM('pending', 'approved', 'rejected'),
      is_alternative TINYINT(1) DEFAULT 0,
      created_at TIMESTAMP,
      updated_at TIMESTAMP
    );

## Penggunaan

### Admin
- Login sebagai admin
- Kelola pengguna dan pemesanan
- Optimasi jadwal dengan algoritma genetika
- Analisis konflik jadwal

### Pengguna
- Registrasi/login
- Membuat pemesanan
- Melihat/mengelola pemesanan
- Melihat jadwal di kalender

### Optimasi Jadwal
- Buka Schedule > Optimize
- Pilih rentang tanggal
- Klik Optimize Schedule
- Sistem menampilkan jadwal rekomendasi
- Admin dapat menyetujui/menolak

### Visualisasi Hasil
- Kalender jadwal yang dioptimalkan
- Grafik perbandingan sebelum vs sesudah
- Daftar konflik & rekomendasi
- Statistik penggunaan slot waktu

## Pengembangan

Struktur MVC sederhana:
- Models: Logika bisnis
- Views: UI
- Controllers: Alur aplikasi

### Modifikasi Algoritma
- Edit models/GeneticAlgorithm.php
- Sesuaikan parameter & fungsi fitness

## Kontribusi

    # Fork, lalu buat branch baru
    git checkout -b fitur-baru

    # Commit perubahan
    git commit -am "Menambahkan fitur baru"

    # Push branch
    git push origin fitur-baru

    # Lalu buat Pull Request

## Lisensi
MIT License

## Kontak
Nama – onecircle.24@gmail.com
Link Proyek: https://github.com/defanggaabypn/church_scheduling.git
