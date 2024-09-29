<?php

namespace Tests\Xala\EloquentMock;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $guarded = [];

    public $timestamps = false;
}
