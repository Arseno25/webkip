<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'npsn',
        'address',
        'phone',
        'email',
        'principal_name',
        'level',
        'subdistrict_id',
        'latitude',
        'longitude',
    ];

    /**
     * Get the subdistrict that the school belongs to.
     */
    public function subdistrict(): BelongsTo
    {
        return $this->belongsTo(Subdistrict::class);
    }

    /**
     * Get the KIP recipients for the school.
     */
    public function kipRecipients(): HasMany
    {
        return $this->hasMany(KipRecipient::class);
    }
}
