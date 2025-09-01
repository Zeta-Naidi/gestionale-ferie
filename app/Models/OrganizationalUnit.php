<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrganizationalUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'parent_id',
        'level',
        'description',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(OrganizationalUnit::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(OrganizationalUnit::class, 'parent_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'organizational_unit_id');
    }

    public function managers(): HasMany
    {
        return $this->hasMany(User::class, 'organizational_unit_id')->where('role', 'MANAGER');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(User::class, 'organizational_unit_id')->where('role', 'EMPLOYEE');
    }

    // Get all descendants recursively
    public function allChildren()
    {
        return $this->children()->with('allChildren');
    }

    // Get all ancestors recursively
    public function allParents()
    {
        return $this->parent()->with('allParents');
    }
}
