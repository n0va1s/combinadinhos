<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['family_id', 'role', 'code', 'used'])]
class FamilyInvitation extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    public function family()
    {
        return $this->belongsTo(Family::class);
    }
}
