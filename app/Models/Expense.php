<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'pocket_id',
        'amount',
        'notes'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pocket()
    {
        return $this->belongsTo(UserPocket::class, 'pocket_id');
    }
}
