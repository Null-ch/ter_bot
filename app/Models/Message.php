<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Message extends Model
{
    use HasFactory, SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'message',
        'user_tg',
        'chat',
        'performer',
    ];

    protected $table = 'messages';
    protected $guarded = false;

    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }
}
