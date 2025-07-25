<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Voucher extends Model
{
    /** @use HasFactory<\Database\Factories\VoucherFactory> */
    use HasFactory;
 protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'uuid',
        'user_id',
        'status_id',
        'kode_promo',
        'tipe_promo',
        'start_date',
        'end_date',
        'kouta',
    ];

    protected static function booted()
    {
        static::creating(function ($voucher) {
            if (! $voucher->getKey()) {
                $voucher->{$voucher->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'uuid');
    }

    public function status()
    {
        return $this->belongsTo(StatusProduct::class, 'status_id', 'uuid');
    }
}
