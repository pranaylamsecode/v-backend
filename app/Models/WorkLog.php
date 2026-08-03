<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkLog extends Model
{
    protected $fillable = [
        'employee_id',
        'd_b_project_id',
        'employee_task_id',
        'date',
        'hours',
        'description'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function project()
    {
        return $this->belongsTo(DBProject::class, 'd_b_project_id');
    }

    public function task()
    {
        return $this->belongsTo(EmployeeTask::class, 'employee_task_id');
    }
}
