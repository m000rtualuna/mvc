<?php
namespace Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Room extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = ['name', 'number_room', 'type', 'subdivision_id'];

    public function subdivision()
    {
        return $this->belongsTo(Subdivision::class, 'subdivision_id');
    }
}