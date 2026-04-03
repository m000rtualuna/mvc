<?php
namespace Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Subscriber extends Model
{
    use HasFactory;
    public $timestamps = false;

    public static function countBySubdivision($subdivisionId)
    {
        return self::where('subdivision', $subdivisionId)->count();
    }

    public static function countByRoom($roomId)
    {
        return self::where('room', $roomId)->count();
    }
}