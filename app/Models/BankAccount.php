<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BankAccount extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $table = 'bank_accounts';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'balance',
        'credit',
        'account_number',
        'status',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
                $model->account_number = self::generateUniqueAccountNumber();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pixis()
    {
        return $this->hasMany(Pix::class, 'account_id');
    }

    private static function generateUniqueAccountNumber()
    {
        do {
            $number = str_pad(random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
        } while (self::where('account_number', $number)->exists());

        return $number;
    }
}
