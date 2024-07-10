<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Pickit extends Model
{
    use HasFactory;
    protected $table = 'pickit';
    protected $fillable = [
        'uuid',
        'order_id',
        'service_type',
        'point_id',
        'pickit_price',
        'status',
    ];
}