<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAlert extends Model
{
    protected $guarded = [];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // peringatan yang belum ditutup (stok masih di bawah minimum)
    public function scopeOpen($query)
    {
        return $query->whereNull('resolved_at');
    }

    // batasi sesuai hak akses: kasir hanya melihat cabangnya
    public function scopeVisibleTo($query, $user)
    {
        if (!$user->isOwner()) {
            $query->where('branch_id', $user->branch_id);
        }

        return $query;
    }
}
