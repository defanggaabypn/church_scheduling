# Aplikasi Penjadwalan Penggunaan Gedung Gereja Baptis Syalom Karor

Sistem manajemen penjadwalan penggunaan gedung gereja dengan optimasi jadwal menggunakan algoritma genetika.

## Struktur Proyek
```
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
```

## Deskripsi

Aplikasi Penjadwalan Penggunaan Gedung Gereja Baptis Syalom Karor adalah sistem berbasis web yang dikembangkan untuk mempermudah proses penjadwalan penggunaan gedung gereja. Dengan menerapkan algoritma genetika, aplikasi ini dapat mengoptimalkan jadwal kegiatan untuk meminimalkan konflik dan memastikan penggunaan gedung yang efisien.

### Fitur Utama

- **Autentikasi**: Sistem login dan registrasi pengguna
- **Manajemen Pengguna**: Admin dapat mengelola data pengguna
- **Booking Kegiatan**: Jemaat dapat membuat, melihat, dan mengelola pemesanan penggunaan gedung
- **Penjadwalan**: Sistem penjadwalan dengan tampilan kalender
- **Optimasi Jadwal**: Menggunakan algoritma genetika untuk mengoptimalkan jadwal
- **Notifikasi**: Pemberitahuan untuk status booking

## Ketentuan Penggunaan Gedung Gereja

### 1. Permintaan Kegiatan
Permintaan kegiatan tidak dapat diajukan untuk ibadah dan kegiatan yang sudah memiliki jadwal tetap setiap minggunya, yaitu:

- **Kegiatan Belajar Mengajar TK & PAUD**  
  Hari: Senin – Jumat  
  Pukul: 08.00 – 12.00 WITA

- **Ibadah Doa**  
  Hari: Sabtu  
  Pukul: 18.00 WITA – Selesai

- **Ibadah Minggu Pagi**  
  Hari: Minggu  
  Pukul: 09.30 WITA – Selesai

### 2. Pengajuan Permintaan
Permintaan kegiatan wajib diajukan minimal 1 minggu sebelum kegiatan berlangsung.

### 3. Jadwal Bertabrakan
Jika ada jadwal yang bertabrakan, gedung gereja akan digunakan untuk kegiatan yang pertama kali mengajukan permohonan penggunaan gedung.

## Algoritma Genetika untuk Optimasi Jadwal

Sistem ini menggunakan algoritma genetika untuk mengoptimalkan jadwal pemesanan dengan menghindari konflik jadwal dan mengalokasikan slot waktu alternatif jika diperlukan.

### Komponen Utama Algoritma

- **Populasi**: Kumpulan kromosom (solusi potensial) dengan ukuran default 50
- **Kromosom**: Representasi jadwal dalam bentuk array gen yang mengkodekan:
  - Gen include/exclude → Menentukan apakah booking dimasukkan (1) atau tidak (0)
  - Gen slot waktu → Menentukan slot waktu yang digunakan (0 = waktu asli, 1+ = slot alternatif)
- **Fitness Function**: Mengevaluasi kualitas jadwal berdasarkan:
  - Jumlah booking yang berhasil dijadwalkan
  - Penalti untuk konflik jadwal
  - Penalti kecil untuk penggunaan slot waktu alternatif
- **Seleksi**: Tournament selection
- **Crossover**: 0.8
- **Mutasi**: 0.2
- **Elitisme**: 5 kromosom terbaik disimpan setiap generasi

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

```bash
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
```

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
