# SMM Telegram Bot (Paid-to-Click)

Bot Telegram berbasis PHP Native untuk marketplace engagement media sosial (Paid-to-Click). Bot ini menghubungkan **Client** (yang membutuhkan engagement seperti Likes, Follow, Comment) dengan **Worker** (yang mengerjakan tugas untuk mendapatkan imbalan).

## Fitur Utama

### 👥 Peran Pengguna
- **Client**: Membuat campaign, mengatur target jumlah & harga per tugas.
- **Worker**: Mengerjakan tugas yang tersedia, upload bukti screenshot, dan menarik saldo (withdraw).
- **Admin**: Memverifikasi bukti kerja (approve/reject), mengelola top-up & withdraw, serta moderasi user.

### ⚙️ Fungsionalitas
- **Manajemen Campaign**: Mendukung berbagai tipe engagement (View, Like, Comment, Share, Follow) untuk berbagai platform (Instagram, TikTok, dll).
- **Sistem Dompet (Wallet)**: Pencatatan saldo real-time dengan log transaksi lengkap (Deposit, Reward, Withdraw).
- **Verifikasi Bukti**: Worker mengunggah screenshot bukti kerja langsung ke bot untuk diverifikasi manual oleh Admin.
- **Manajemen Akun Medsos**: User dapat mendaftarkan akun media sosial mereka untuk validasi tugas.
- **Top-up System**: Deposit saldo dengan panduan transfer otomatis dan verifikasi bukti pembayaran manual oleh Admin.

## Teknologi
- **Bahasa**: PHP (Native)
- **Database**: MySQL (via PDO)
- **API**: Telegram Bot API (Webhook method)

## Struktur Folder
```
├── config/         # Konfigurasi database & token bot (perlu dibuat)
├── database/       # Skema database & script SQL
├── helpers/        # Fungsi bantuan (utilities)
├── log/            # File log aktivitas (trace, error, dll)
├── reply/          # Logic handler untuk setiap respon bot
├── db.php          # Wrapper koneksi & fungsi CRUD database
├── index.php       # Entry point webhook
└── TelegramBot.php # Class wrapper API Telegram
```

## Development Standards

### Copywriting & UX
Bot ini menggunakan standar copywriting yang konsisten untuk kenyamanan pengguna:
- **Tone**: Profesional, informatif, dan sopan.
- **Format**: Menggunakan HTML Parse Mode.
- **Struktur Pesan**:
  - **Header**: Icon + Judul Tebal (contoh: `💳 <b>Informasi Saldo</b>`)
  - **Body**: Informasi jelas dengan penekanan pada nilai penting (Rp, ID, Kode).
  - **Footer**: Instruksi tambahan dengan teks miring (*italic*).
- **Mata Uang**: Format Rupiah tanpa desimal sen (contoh: `Rp 50.000`).

## Cara Instalasi

### 1. Persiapan Lingkungan
Pastikan server Anda memenuhi syarat berikut:
- PHP 7.4 atau lebih baru (wajib aktifkan ekstensi `pdo_mysql` dan `curl`).
- MySQL atau MariaDB.
- Domain dengan SSL (HTTPS) untuk Webhook Telegram.

### 2. Setup Database
1. Buat database baru di MySQL.
2. Impor file skema database:
   ```bash
   mysql -u username -p nama_database < database/schema.sql
   ```

### 3. Konfigurasi
Buat folder `config` di root direktori, lalu buat dua file di dalamnya:

**`config/config.php`**
```php
<?php
$bot_token = "TOKEN_BOT_TELEGRAM_ANDA";
?>
```

**`config/db-config.php`**
```php
<?php
$host = "localhost";
$db   = "nama_database";
$user = "user_database";
$pass = "password_database";
$charset = "utf8mb4";
?>
```

### 4. Setup Webhook
Arahkan webhook bot Telegram Anda ke file `index.php`:
```
https://api.telegram.org/bot<TOKEN>/setWebhook?url=https://domain-anda.com/smm-bot/index.php
```

## Lisensi

Proyek ini didistribusikan di bawah lisensi **BSD 3-Clause License**.
Lihat file [LICENSE](LICENSE) untuk detail lengkap.
