<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DaftarTugas extends Model
{
    use HasFactory;

    protected $table = 'daftartugas';

   protected $fillable = [
        'judul',
        'isi',
        'status',
        'deadline',
        'is_important',
        'kategori',
        'user_id',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'is_important' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'is_completed',
        'is_overdue',
        'category_icon',
        'deadline_date_time',
        'status_display'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByKategori($query, $kategori)
    {
        return $query->where('kategori', $kategori);
    }

    public function scopeImportant($query)
    {
        return $query->where('is_important', true);
    }

    public function scopeOverdue($query)
    {
        return $query->where('deadline', '<', now())
                    ->where('status', 'BELUM SELESAI');
    }

    public function scopeUpcoming($query, $days = 7)
    {
        return $query->where('deadline', '>=', now())
                    ->where('deadline', '<=', now()->addDays($days))
                    ->where('status', 'BELUM SELESAI');
    }

    public function scopePending($query)
    {
        return $query->where('deadline', '>=', now())
                    ->where('status', 'BELUM SELESAI');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'SELESAI');
    }

    // Accessors
    public function getIsCompletedAttribute()
    {
        return $this->status === 'SELESAI';
    }

    public function getIsOverdueAttribute()
    {
        return $this->deadline < now() && $this->status === 'BELUM SELESAI';
    }

    public function getCategoryIconAttribute()
    {
        switch (strtolower($this->kategori)) {
            case 'work':
                return '💼';
            case 'study':
                return '📚';
            case 'personal':
                return '👤';
            default:
                return '📋';
        }
    }

    public function getDeadlineDateTimeAttribute()
    {
        return $this->deadline;
    }

    public function getStatusDisplayAttribute()
    {
        if ($this->is_completed) {
            return 'COMPLETED';
        }
        
        if ($this->is_overdue) {
            return 'OVERDUE';
        }
        
        return 'PENDING';
    }

    // Methods
    public function markAsComplete()
    {
        $this->update(['status' => 'SELESAI']);
    }

    public function markAsIncomplete()
    {
        $this->update(['status' => 'BELUM SELESAI']);
    }

    public function isDeadlineToday()
    {
        return $this->deadline->isToday();
    }

    public function daysUntilDeadline()
    {
        if ($this->is_overdue) {
            return -1 * now()->diffInDays($this->deadline);
        }
        
        return now()->diffInDays($this->deadline);
    }
}