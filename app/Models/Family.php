<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['name'])]
class Family extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function missions()
    {
        return $this->hasMany(Mission::class);
    }

    public function rewards()
    {
        return $this->hasMany(Reward::class);
    }
}
