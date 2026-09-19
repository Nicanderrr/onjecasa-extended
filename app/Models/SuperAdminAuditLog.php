<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuperAdminAuditLog extends Model
{
    protected $table = 'superadmin_audit_logs';

    protected $fillable = [
        'user_id',
        'action',
        'subject_type',
        'subject_id',
        'properties',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
