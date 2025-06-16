<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TransactionsHistory extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $table = 'transactions_histories';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'transaction_type',
        'receiver',
        'amount',
        'status',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

   public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver', 'id');
    }
}
