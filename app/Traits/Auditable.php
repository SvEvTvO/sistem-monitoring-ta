<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    // Fungsi ini akan otomatis dipanggil oleh Laravel setiap kali Model dimuat
    public static function bootAuditable()
    {
        // 1. Rekam saat data DIBUAT
        static::created(function ($model) {
            self::logAudit($model, 'CREATED');
        });

        // 2. Rekam saat data DIUBAH
        static::updated(function ($model) {
            self::logAudit($model, 'UPDATED');
        });

        // 3. Rekam saat data DIHAPUS
        static::deleted(function ($model) {
            self::logAudit($model, 'DELETED');
        });
    }

    protected static function logAudit($model, $action)
    {
        // Jangan rekam jika yang melakukan aksi bukan user login (misal: Seeder / Terminal)
        if (!Auth::check()) return;

        // Ambil data sebelum dan sesudah diubah
        $oldValues = $action !== 'CREATED' ? $model->getOriginal() : null;
        $newValues = $action !== 'DELETED' ? $model->getAttributes() : null;

        // Keamanan: Jangan pernah merekam password ke dalam log!
        if (isset($oldValues['password'])) unset($oldValues['password']);
        if (isset($newValues['password'])) unset($newValues['password']);
        if (isset($oldValues['remember_token'])) unset($oldValues['remember_token']);
        if (isset($newValues['remember_token'])) unset($newValues['remember_token']);

        // Simpan ke database
        AuditLog::create([
            'user_id'     => Auth::id(), // Pelaku
            'action'      => $action,    // CREATED / UPDATED / DELETED
            'entity_type' => class_basename($model), // Nama Tabel/Model (Misal: User, Project)
            'entity_id'   => $model->id,
            'old_values'  => $oldValues, // Data Lama
            'new_values'  => $newValues, // Data Baru
        ]);
    }
}