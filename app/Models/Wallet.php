<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'balance',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
