<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Outlet extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'address_one',
        'address_two',
        'phone_one',
        'phone_two',
        'email',
        'photo',
        'latitude',
        'longitude',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'string', // Ensure UUID is treated as a string
        'user_id' => 'string', // Ensure UUID is treated as a string
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    /**
     * Get the user that owns the outlet.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}