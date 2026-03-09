<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParentalControl extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'daily_limit',
        'blocked_category_ids',
        'notify_on_purchase',
    ];

    protected $casts = [
        'blocked_category_ids' => 'array',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
