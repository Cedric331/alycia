<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Profile extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'name',
        'biography',
        'is_online',
        'photos_count',
        'videos_count',
        'likes_count',
        'action_label',
    ];

    protected $casts = [
        'is_online' => 'boolean',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('banner')
            ->useDisk('public')
            ->singleFile();

        $this->addMediaCollection('avatar')
            ->useDisk('public')
            ->singleFile();
    }

    /**
     * Get the default profile (singleton pattern)
     */
    public static function getDefault(): self
    {
        return static::firstOrCreate(
            ['id' => 1],
            [
                'name' => 'Alycia',
                'biography' => "C'est ici où je post le 🔞",
                'is_online' => false,
                'photos_count' => 0,
                'videos_count' => 0,
                'likes_count' => 0,
                'action_label' => "S'abonner au VIP d'alycia",
            ]
        );
    }
}

