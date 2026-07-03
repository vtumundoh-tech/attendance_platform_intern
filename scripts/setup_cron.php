<?php
/**
 * Penjadwalan cron untuk backup otomatis TIDAK digunakan dalam deployment ini.
 * Backup dilakukan manual dari menu Admin → Maintenance.
 */
http_response_code(410);
header('Content-Type: text/plain; charset=utf-8');
echo "Setup cron dinonaktifkan. Gunakan Maintenance → Backup manual.\n";
exit;
