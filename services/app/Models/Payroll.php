<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    protected $fillable = [
        'NAM',
        'MANV',
        'THANG',
        'TIENPHAT',
        'TIENTHUONG',
        'SONGAYCONG',
        'TIENLUONGCB',
        'TIENLUONGTL'
    ];

    public function employee() {
        return $this->belongsTo(Employee::class, 'MANV', 'MANV');
    }
}
