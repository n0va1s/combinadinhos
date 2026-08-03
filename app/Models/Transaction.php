<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['action', 'user_name', 'detail', 'amount'])]
class Transaction extends Model
{
    //
}
