<?php

namespace AmirHossein5\LaravelIpLogger\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ip extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $primaryKey = 'ip';
    protected $table = 'ip_details';
    const CREATED_AT = 'visited_at';
    const UPDATED_AT = null;


    protected $fillable = [
        'ip',
        'continent',
        'country',
        'security',
        'timezone',
        'internetProvider',
        'visited_at',
        'seen'
    ];

    protected $casts = [
        'security' => 'array'
    ];
}
