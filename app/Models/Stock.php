<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
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

    public function isLow()
    {
        return $this->quantity <= $this->min_stock;
    }

    public function scopeVisibleTo($query, $user)
    {
        if (!$user->isOwner()) {
            $query->where('branch_id', $user->branch_id);
        }

        return $query;
    }
}
