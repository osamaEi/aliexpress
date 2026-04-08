<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingVideo extends Model
{
    protected $fillable = [
        'title', 'title_ar',
        'description', 'description_ar',
        'type', 'video_path', 'youtube_url',
        'thumbnail', 'visibility',
        'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    /** Scope: only active */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Scope: visible to a given user type */
    public function scopeVisibleTo($query, string $userType)
    {
        return $query->where(function ($q) use ($userType) {
            $q->where('visibility', 'all')
              ->orWhere('visibility', $userType . 's'); // sellers / distributors
        });
    }

    /** Extract YouTube video ID from any YouTube URL */
    public function getYoutubeIdAttribute(): ?string
    {
        if (!$this->youtube_url) return null;

        preg_match(
            '/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/',
            $this->youtube_url,
            $matches
        );

        return $matches[1] ?? null;
    }

    /** Get embed URL */
    public function getEmbedUrlAttribute(): ?string
    {
        if ($this->type === 'youtube' && $this->youtube_id) {
            return 'https://www.youtube.com/embed/' . $this->youtube_id;
        }
        return null;
    }

    /** Get thumbnail URL — YouTube default if no custom thumbnail */
    public function getThumbnailUrlAttribute(): ?string
    {
        if ($this->thumbnail) {
            return asset('storage/' . $this->thumbnail);
        }
        if ($this->type === 'youtube' && $this->youtube_id) {
            return 'https://img.youtube.com/vi/' . $this->youtube_id . '/mqdefault.jpg';
        }
        return null;
    }

    /** Localized title */
    public function localizedTitle(): string
    {
        return (app()->getLocale() === 'ar' && $this->title_ar) ? $this->title_ar : $this->title;
    }

    /** Localized description */
    public function localizedDescription(): ?string
    {
        return (app()->getLocale() === 'ar' && $this->description_ar) ? $this->description_ar : $this->description;
    }
}
