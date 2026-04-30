<?php

namespace Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Access extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['file_id', 'user_id', 'type'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}