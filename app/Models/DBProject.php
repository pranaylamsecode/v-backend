<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DBProject extends Model
{
    protected $guarded = [];
    //
    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'd_b_project_employee', 'd_b_project_id', 'employee_id');
    }
}
