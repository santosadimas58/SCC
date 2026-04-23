<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Schedule extends Model
{
    protected $fillable = ['hari', 'jam_mulai', 'jam_selesai', 'mata_pelajaran', 'guru', 'ruangan', 'status'];
}
