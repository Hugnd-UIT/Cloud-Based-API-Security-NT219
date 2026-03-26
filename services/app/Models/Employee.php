<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $primaryKey = 'MANV';
    
    protected $keyType = 'string';
    
    public $incrementing = false;

    protected $fillable = [
        'SDT',
        'CCCD',
        'MANV',
        'EMAIL',
        'HOTEN',
        'CHUCVU',
        'NGAYSINH',
        'GIOITINH',
        'TRANGTHAI',
        'NGAYVAOLAM'
    ];

    public function salaries() {
        return $this->hasMany(Payroll::class, 'MANV', 'MANV')
                    ->orderBy('NAM', 'desc')
                    ->orderBy('THANG', 'desc'); 
    }
    
    public function payrolls(){
        return $this->hasMany(Payroll::class, 'MANV', 'MANV');
    }
}