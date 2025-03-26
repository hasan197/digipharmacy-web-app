<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_name',
        'doctor_name',
        'notes',
        'pharmacist_notes',
        'status',
        'discount',
        'additional_fee',
        'total',
        'created_by',
        'validated_by',
        'validated_at'
    ];

    protected $casts = [
        'discount' => 'float',
        'additional_fee' => 'float',
        'total' => 'float',
        'validated_at' => 'datetime'
    ];

    public function items()
    {
        return $this->hasMany(PrescriptionItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function pharmacist()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}
