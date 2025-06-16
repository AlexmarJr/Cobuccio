<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PixFavorites extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $table = 'pix_favorites';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'pix_type',
        'pix_key',
        'name',
        'cpf',
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
        return $this->belongsTo(User::class, 'id', 'user_id');
    }
}
