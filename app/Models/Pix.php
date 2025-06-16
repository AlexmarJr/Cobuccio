<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Pix extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $table = 'pixes';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'account_id',
        'type',
        'key',
        'status'
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

    public function account()
    {
        return $this->belongsTo(BankAccount::class, 'account_id');
    }
}
