<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cats extends Model
{
    protected $fillable = ['name', 'ind_id', 'link'];
}
