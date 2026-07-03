-- Update ENUM untuk kolom action di tbl_admin_logs
-- Menambahkan nilai 'approve_registration' dan 'reject_registration'

ALTER TABLE `tbl_admin_logs` 
MODIFY COLUMN `action` ENUM(
    'create_mahasiswa',
    'update_mahasiswa',
    'delete_mahasiswa',
    'export_data',
    'print_report',
    'update_settings',
    'system_maintenance',
    'approve_registration',
    'reject_registration'
) NOT NULL;
