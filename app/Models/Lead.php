<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'company_name',
        'email',
        'phone',
        'address',
        'notes',
        'status',
        'assigned_to',
    ];

    protected $casts = [
        'assigned_to' => 'integer',
    ];

    public function getAssignedToAttribute($value)
    {
        return $value === 0 || $value === null ? null : (int)$value;
    }

    public function setAssignedToAttribute($value)
    {
        $this->attributes['assigned_to'] = is_numeric($value) && (int)$value > 0 ? (int)$value : null;
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to', 'id');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function customer()
    {
        return $this->hasOne(Customer::class);
    }
}
