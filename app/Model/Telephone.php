<?php
namespace Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Telephone extends Model
{
    use HasFactory;
    public $timestamps = false;

    public function subscriber()
    {
        return $this->belongsTo(Subscriber::class, 'subscriber');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room');
    }
}