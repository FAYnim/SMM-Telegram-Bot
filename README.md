# SMM Telegram Bot (Paid-to-Click)

Bot Telegram berbasis PHP Native untuk marketplace engagement media sosial (Paid-to-Click). Bot ini menghubungkan **Client** (yang membutuhkan engagement seperti Likes, Follow, Comment) dengan **Worker** (yang mengerjakan tugas untuk mendapatkan imbalan).

## Fitur Utama

### 👥 Peran Pengguna
- **Client**: Membuat campaign, mengatur target jumlah & harga per tugas, topup Saldo Campaign.
- **Worker**: Mengerjakan tugas yang tersedia, upload bukti screenshot, menarik saldo ke E-Wallet, transfer ke Saldo Campaign.
- **Admin**: Memverifikasi bukti kerja (approve/reject), mengelola top-up & withdraw, serta moderasi user.

### ⚙️ Fungsionalitas
- **Manajemen Campaign**: Mendukung berbagai tipe engagement (View, Like, Comment, Share, Follow) untuk berbagai platform (Instagram, TikTok, dll).
- **Sistem Dompet Ganda**:
  - **Saldo Campaign**: Untuk membuat dan menjalankan campaign
  - **Saldo Penghasilan**: Hasil dari menyelesaikan tugas worker
  - Transfer antar saldo (Penghasilan → Campaign)
- **Withdraw System**: Penarikan ke E-Wallet (DANA/OVO/GoPay) atau transfer ke Saldo Campaign.
- **Verifikasi Bukti**: Worker mengunggah screenshot bukti kerja langsung ke bot untuk diverifikasi manual oleh Admin.
- **Manajemen Akun Medsos**: User dapat mendaftarkan akun media sosial mereka untuk validasi tugas (Instagram, TikTok).
- **Top-up System**: Deposit saldo dengan panduan transfer dan verifikasi bukti pembayaran manual oleh Admin.
- **Bantuan & FAQ**: Menu bantuan lengkap dengan panduan cara menggunakan bot.

## Teknologi
- **Bahasa**: PHP (Native)
- **Database**: MySQL (via PDO)
- **API**: Telegram Bot API (Webhook method)

## Struktur Folder
```
config/           # Konfigurasi database & token bot (buat dari -example files)
database/         # Skema database & script SQL
helpers/          # Fungsi bantuan (utilities)
log/              # File log aktivitas (trace, error, dll)
reply/            # Logic handler untuk setiap respon bot
├── start.php              # Menu utama
├── help*.php              # Bantuan & FAQ
├── cek-saldo.php         # Cek saldo campaign
├── withdraw*.php          # Withdraw & transfer
├── topup*.php            # Topup saldo
├── campaign*.php          # Manajemen campaign
├── task*.php             # Tugas worker
├── social*.php           # Akun media sosial
└── admin-*.php           # Panel admin
webhook/          # Webhook testing files
db.php            # Wrapper koneksi & fungsi CRUD database
index.php         # Entry point webhook
TelegramBot.php   # Class wrapper API Telegram
AGENTS.md         # Panduan untuk agentic coding
```

## Penggunaan Bot

### Menu Utama
- **📢 Campaignku**: Lihat, buat, edit, pause, resume campaign Anda
- **💼 Cari Cuan**: Ambil dan kerjakan tugas yang tersedia
- **💰 Saldo Campaign**: Cek saldo untuk campaign, topup, riwayat topup
- **💸 Tarik Dana**: Withdraw ke E-Wallet atau transfer ke Saldo Campaign
- **👤 Akun Medsos**: Kelola akun Instagram dan TikTok Anda
- **ℹ️ Bantuan**: Panduan lengkap dan FAQ

### Alur Worker
1. Daftar akun media sosial di menu Akun Medsos
2. Pilih tugas di menu Cari Cuan
3. Klik "Ambil Tugas"
4. Kerjakan tugas sesuai instruksi (like/follow/comment)
5. Upload screenshot sebagai bukti
6. Tunggu verifikasi admin
7. Saldo Penghasilan bertambah jika disetujui
8. Withdraw atau transfer ke Saldo Campaign

### Alur Client
1. Topup Saldo Campaign
2. Buat campaign baru di menu Campaignku
3. Tentukan jenis, link, reward, dan target
4. Campaign aktif dan dapat dikerjakan worker
5. Monitoring progress secara real-time
6. Pause/resume atau tambah saldo campaign

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

### Kode Style
- Variables: `snake_case` (`$chat_id`, `$user_id`)
- Functions: `snake_case` (`db_read()`, `updateUserPosition()`)
- Classes: `PascalCase` (`TelegramBot`)
- Methods: `camelCase` (`sendMessage()`, `getChatId()`)
- Files: lowercase with hyphens (`cek-saldo.php`, `buat-campaign.php`)

Lihat [AGENTS.md](AGENTS.md) untuk panduan lengkap development.

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
https://api.telegram.org/bot<TOKEN>/setWebhook?url=https://domain-anda.com/index.php
```

## Testing
- Test webhook lokal: Gunakan ngrok atau localtunnel untuk expose `index.php`
- Lihat logs: `tail -f log/debug.log` atau `tail -f log/app.log`
- Database reset: `mysql -u username -p database_name < database/reset.sql`

## Lisensi

Proyek ini didistribusikan di bawah lisensi **BSD 3-Clause License**.
Lihat file [LICENSE](LICENSE) untuk detail lengkap.
