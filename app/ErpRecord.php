<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ErpRecord extends Model
{
    protected $fillable = [
        'module',
        'title',
        'description',
        'status',
        'amount',
        'record_date',
        'assigned_user_id',
        'created_by',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'record_date' => 'date',
        'metadata' => 'array',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }
}
