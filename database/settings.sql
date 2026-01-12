TRUNCATE smm_settings;

INSERT INTO smm_settings (category, setting_key, setting_value, description) VALUES
('payment', 'dana_number', '0812-3456-7890', 'Nomor DANA untuk topup'),
('payment', 'dana_name', 'Admin SMM', 'Nama pemilik akun DANA'),
('payment', 'shopeepay_number', '0812-3456-7890', 'Nomor ShopeePay untuk topup'),
('payment', 'shopeepay_name', 'Admin SMM', 'Nama pemilik akun ShopeePay'),
('withdraw', 'min_withdraw', '50000', 'Minimum jumlah withdrawal'),
('withdraw', 'admin_fee', '5000', 'Biaya admin withdrawal'),
('campaign', 'min_price_per_task', '100', 'Minimum harga per task (Rp)');
