<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Assignment extends Model
{
    protected $fillable = ['judul', 'deskripsi', 'mata_pelajaran', 'deadline', 'status'];
}
