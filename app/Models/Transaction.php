<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\User;

#[Fillable(['action', 'user_name', 'detail', 'amount', 'status', 'user_id'])]
class Transaction extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $casts = ['status' => TransactionStatus::class];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
