<?php

namespace App\Models;

use Database\Factories\JobApplicationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['company', 'role', 'location', 'url', 'salary_min', 'status', 'notes', 'applied_on', 'remote'])]
class JobApplication extends Model
{
    /** @use HasFactory<JobApplicationFactory> */
    use HasFactory;

    public const STATUSES = ['saved', 'applied', 'interview', 'offer', 'rejected'];

    protected function casts(): array
    {
        return ['applied_on' => 'date:Y-m-d', 'remote' => 'boolean', 'salary_min' => 'float'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
