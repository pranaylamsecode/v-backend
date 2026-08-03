<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetFeature extends Model
{
    protected $guarded = [];

    public function asset()
    {
        return $this->belongsTo(ClientAsset::class, 'client_asset_id');
    }

    public function assignedEmployee()
    {
        return $this->belongsTo(Employee::class, 'assigned_employee_id');
    }

    public function task()
    {
        return $this->belongsTo(EmployeeTask::class, 'task_id');
    }
}
