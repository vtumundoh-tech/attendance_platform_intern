#!/bin/bash
# Script untuk setup cron job backup otomatis

echo 'Setup cron job untuk backup otomatis...'

# Backup crontab saat ini
crontab -l > /tmp/crontab_backup 2>/dev/null || true

# Tambahkan entry baru
echo '# Backup dan Cleanup Aplikasi Absensi Mahasiswa Magang
# Jalankan setiap 3 bulan pada tanggal 1 jam 2 pagi
0 2 1 */3 * php C:\laragon\www\valendy_presensi\scripts/backup_and_cleanup.php >> C:\laragon\www\valendy_presensi\scripts/../logs/cron_backup.log 2>&1
' >> /tmp/crontab_backup

# Install crontab baru
crontab /tmp/crontab_backup

# Hapus file temporary
rm /tmp/crontab_backup

echo 'Cron job berhasil disetup!'
echo 'Backup akan dijalankan setiap 3 bulan pada tanggal 1 jam 2 pagi'
