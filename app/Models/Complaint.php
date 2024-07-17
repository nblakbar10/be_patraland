<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    use HasFactory;

    protected $table = "complaints";

    public $fillable = [
        'user_id',
        'home_address',
        'description',
        'handling_asset',
        'complaint_asset',
        'sparepart',
        'handling_description',
        'status',
        'user_handler_id',
    ];

    public function getUpdatedAtAttribute($value)
    {
        return date('d-m-Y H:i:s', strtotime($value));
    }
    public function getCreatedAtAttribute($value)
    {
        return date('d-m-Y H:i:s', strtotime($value));
    }
}
