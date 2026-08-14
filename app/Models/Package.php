<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    protected $fillable = [
        'package_name',
        'destination',
        'description',
        'image',
        'price',
        'status',
    ];

    public function inclusions(): HasMany
    {
        return $this->hasMany(Inclusion::class);
    }

    public function tourDates(): HasMany
    {
        return $this->hasMany(TourDate::class);
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(Reminder::class);
    }
}