<?php
namespace Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Subdivision extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = ['name', 'type'];

    public function subscribers()
    {
        return $this->hasMany(Subscriber::class, 'subdivision_id');
    }
}