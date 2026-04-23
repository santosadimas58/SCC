<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Teacher extends Model
{
    use HasFactory;
    protected $fillable = [
        'kode_guru',
        'nama',
        'email',
        'no_hp',
        'mata_pelajaran',
        'status',
    ];
}
