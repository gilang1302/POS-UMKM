<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $guarded = [];

    protected $casts = [
        'paid_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public static $statusLabels = [
        'paid' => 'Lunas',
        'pending' => 'Menunggu bayar',
        'cancelled' => 'Dibatalkan',
        'expired' => 'Kedaluwarsa',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(TransactionDetail::class);
    }

    // hanya transaksi yang sudah lunas yang dihitung sebagai omset
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    // kasir cuma boleh melihat transaksi cabangnya sendiri
    public function scopeVisibleTo($query, $user)
    {
        if (!$user->isOwner()) {
            $query->where('branch_id', $user->branch_id);
        }

        return $query;
    }

    // kelas warna lencana di tampilan
    public function badgeClass()
    {
        $map = ['paid' => 'badge-success', 'pending' => 'badge-warning', 'cancelled' => 'badge-secondary', 'expired' => 'badge-secondary'];

        return $map[$this->status] ?? 'badge-secondary';
    }

    public function statusLabel()
    {
        return self::$statusLabels[$this->status] ?? $this->status;
    }
}
