<?php

namespace Tests\Xala\EloquentMock\Support;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $guarded = [];

    public $timestamps = false;
}
