<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

#[Fillable(['family_id', 'description', 'cost'])]
class Reward extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    public function family()
    {
        return $this->belongsTo(Family::class);
    }
}
