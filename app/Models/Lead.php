<?php

namespace App\Models;

use Carbon\Traits\LocalFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Property;
use App\Models\User;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Lead extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'name', 'user_id', 'email', 'phone', 'has_company', 'company_name', 'company_email', 'cui',
        'company_address', 'cnp', 'date_of_birth', 'county', 'city', 'source',
        'priority', 'status', 'assigned_agent_id', 'last_contact', 'notes', 'doc_attachment',
    ];

    public function properties()
    {
        return $this->belongsToMany(Property::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->setDescriptionForEvent(fn(string $eventName) => "{$eventName} lead")
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('lead');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
