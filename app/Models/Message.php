<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;
    public function getCreatedAtAttribute($value): Carbon
    {
        return Carbon::parse($value)->timezone(config('app.timezone'));
    }
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
    ];
}
