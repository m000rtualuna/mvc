<?php
namespace Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Model\Telephone;

class Subscriber extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = ['name', 'surname', 'patronymic', 'date_of_birth', 'subdivision_id'];

    public static function countBySubdivision($subdivisionId)
    {
        return self::where('subdivision', $subdivisionId)->count();
    }

    public function subdivision()
    {
        return $this->belongsTo(Subdivision::class, 'subdivision_id');
    }

    public function telephone()
    {
        return $this->hasMany(Telephone::class, 'subscriber_id');
    }
}