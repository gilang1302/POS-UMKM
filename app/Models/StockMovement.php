<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $guarded = [];

    // nama jenis pergerakan yang tampil di layar
    public static $typeLabels = [
        'sale' => 'Penjualan',
        'sale_cancel' => 'Batal penjualan',
        'restock' => 'Barang masuk',
        'transfer_in' => 'Transfer masuk',
        'transfer_out' => 'Transfer keluar',
        'adjustment' => 'Penyesuaian',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function typeLabel()
    {
        return self::$typeLabels[$this->type] ?? $this->type;
    }
}
