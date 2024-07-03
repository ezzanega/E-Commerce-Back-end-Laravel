<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class product_tags extends Model
{
    use HasFactory;
    protected $table = 'product_tags';

    protected $fillable = [
        'product_id',
        'tag_id',
    ];


}
