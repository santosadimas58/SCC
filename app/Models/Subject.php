<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Subject extends Model
{
    protected $fillable = ['kode_mapel', 'nama_mapel', 'kategori', 'sks', 'status'];
}
