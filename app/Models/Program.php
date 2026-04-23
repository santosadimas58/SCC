<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Program extends Model
{
    use HasFactory;
    protected $fillable = [
        'kode_program',
        'nama_program',
        'deskripsi',
        'jalur',
        'status',
        'user_id',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
