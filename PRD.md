📄 Product Requirements Document — Telegram PTC Bot / SMM Bot (MVP)

1. Ringkasan Produk

Bot Telegram yang menyediakan platform Paid-to-Click untuk meningkatkan engagement media sosial.

Client membuat campaign (view/like/comment/share/follow) dan membayar.

Worker mengerjakan tugas dan menerima reward.

Admin memverifikasi bukti (screenshot) dan mengelola transaksi.


Verifikasi manual via screenshot, tanpa panel web. Semua dilakukan melalui bot.


---

2. Tujuan Utama Produk

1. Menyediakan cara sederhana untuk membeli engagement (legalitas menjadi tanggung jawab pengguna).


2. Memberi kesempatan user mendapatkan penghasilan dari menyelesaikan tugas.


3. Memastikan transaksi tercatat, transparan, dan mudah dikelola admin.


4. Membuat arsitektur yang dapat di-upgrade (otomasi, panel web, API) tanpa bongkar ulang.




---

3. Persona

Client (Pembeli Engagement)

Ingin menaikkan views/likes/comments/followers.

Menginginkan kontrol jumlah & biaya.

Membutuhkan laporan progress campaign.


Worker (Pengerja)

Mendaftar untuk mengerjakan tugas.

Mengirim screenshot bukti.

Menarik hasil reward.


Admin

Memoderasi tugas & transaksi.

Mencegah fraud.

Menangani top-up/withdraw manual.



---

4. Ruang Lingkup (Scope)

Termasuk (MVP)

Bot Telegram operasional penuh.

Sistem akun dengan role.

Campaign management.

Tugas worker + upload screenshot.

Verifikasi admin manual di bot.

Dompet internal.

Top-up manual + log.

Withdraw manual + log.

Audit log dasar.

Backup database.


Tidak Termasuk (MVP)

Verifikasi otomatis via API platform sosial.

Panel admin web.

Sistem referral.

Auto-withdraw & auto-top-up penuh.

Anti-fraud canggih (hanya dasar).



---

5. User Stories (Inti)

Client

“Saya ingin membuat campaign dan mengatur jumlah serta harga per tugas.”

“Saya ingin melihat progress campaign.”


Worker

“Saya ingin melihat tugas yang tersedia.”

“Saya ingin mengirim screenshot dan dibayar setelah disetujui.”


Admin

“Saya ingin memverifikasi bukti dan mengelola transaksi dengan cepat.”

“Saya ingin melihat riwayat keputusan.”



---

6. Flow Utama

6.1 Registrasi & Role

1. User membuka bot → /start


2. Bot meminta pilih peran: Client atau Worker


3. Admin di-set manual oleh developer di database




---

6.2 Client — Campaign

1. Client → “Buat Campaign”


2. Isi: jenis, link, target, harga per tugas, total target


3. Campaign → status Active


4. Worker mengambil tugas


5. Progress naik sampai target terpenuhi




---

6.3 Worker — Tugas

1. Worker → “Tugas Tersedia”


2. Ambil tugas


3. Kerjakan


4. Upload screenshot


5. Status → Pending Review


6. Admin approve → saldo worker bertambah
Admin reject → tugas kembali ke pool




---

6.4 Top-Up Manual

1. Client → “Top-Up”


2. Kirim bukti transfer


3. Admin Approve


4. Saldo bertambah + log tercatat




---

6.5 Withdraw Manual

1. Worker → “Withdraw”


2. Isi nominal + nomor tujuan


3. Status Pending


4. Admin transfer manual


5. Admin approve → saldo berkurang + log tercatat




---

7. Detail Fitur (Spesifikasi Fungsional)

7.1 Akun & Role

Registrasi melalui bot.

Role disimpan di database:
client | worker | admin

User dapat diblokir (status: active/suspended).



---

7.2 Dompet Internal

Menyimpan saldo.

Setiap perubahan saldo harus melalui transaction record.

Tipe transaksi:

deposit

task_reward

withdraw

adjustment (admin — tercatat)



Status transaksi: pending | approved | rejected | canceled

Tidak boleh ada saldo negatif.


---

7.3 Campaign

Field minimal:

id

client_id

jenis (view/like/comment/share/follow)

link_target

price_per_task

target_total

completed_count

status (active/paused/completed/deleted)


Aturan:

campaign otomatis completed saat target tercapai.

saldo client (wallet) dikurangi saat campaign dibuat/disimpan (di depan).

campaign_balance berkurang saat reward dibayarkan ke worker (setelah admin approve task).



---

7.4 Tugas Worker

Status tugas: available | taken | pending_review | approved | rejected

Aturan:

worker tidak bisa mengambil lebih dari X tugas simultan (batas).

rejected → kembali ke available.


Screenshot disimpan di storage (bukan DB), hanya path yang dicatat.


---

7.5 Verifikasi Admin

Admin dapat:

melihat daftar pending

membuka detail (campaign + screenshot + user)

approve / reject

memberi catatan optional saat reject

keputusan terekam di log



---

7.6 Top-Up Manual

Field:

user_id

nominal

bukti (gambar/file)

status

admin_id (yang memproses)



---

7.7 Withdraw Manual

Field:

user_id

nominal

tujuan (nomor DANA)

status

admin_id


Aturan:

minimal withdraw

fee optional (0 untuk MVP)



---

8. Non-Functional Requirements

Keamanan

tidak ada perubahan saldo tanpa transaksi tercatat

akses admin dibatasi


Reliabilitas

backup database harian


Kinerja

respon bot < 2 detik rata-rata


Auditability

semua tindakan admin tercatat


Scalability

arsitektur siap di-upgrade ke panel web / automasi




---

9. Validasi & Anti-Fraud Dasar

Limit tugas per hari per worker.

Screenshot wajib.

Admin bisa suspend user.

Catatan reason saat reject.

History tugas bisa ditinjau ulang.



---

10. Notifikasi Bot (Contoh)

“Tugas Anda disetujui — saldo bertambah.”

“Tugas ditolak — alasan: …”

“Withdraw berhasil diproses.”

“Top-up disetujui.”



---

11. Data Model (ringkas)

Tabel inti:

users

wallets

wallet_transactions

campaigns

tasks

task_proofs

deposits

withdrawals

audit_logs



---

12. Metrik Minimal

total user aktif

total campaign jalan

total reward dibayar

pending tasks

pending withdraw

fraud/reject rate



---

13. Kriteria Sukses (Acceptance Criteria)

✔ bot berjalan stabil
✔ setiap transaksi memiliki log
✔ tidak ada saldo negatif
✔ campaign berjalan sesuai target
✔ approval admin memicu perubahan saldo otomatis
✔ seluruh alur (buat campaign → withdraw) bisa dijalankan end-to-end


---

14. Rencana Upgrade (di masa depan)

otomatisasi verifikasi via API sosial

panel web admin

sistem referral

auto-top-up DANA 100%

rules anti-fraud lanjutan
