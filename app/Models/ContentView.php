<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentView extends Model
{
    public const POST = 'post';
    public const PRISON_RULE = 'prison_rule';

    public $timestamps = false;

    protected $fillable = ['content_type', 'content_id', 'viewed_at'];

    protected $casts = ['viewed_at' => 'datetime'];
}
