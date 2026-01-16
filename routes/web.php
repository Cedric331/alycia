<?php

use App\Http\Controllers\MediaController;
use App\Models\Profile;
use App\Models\Post;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Routes pour servir les images (sécurisées)
Route::get('/media/blurred/{mediaId}', [MediaController::class, 'showBlurred'])
    ->name('media.blurred')
    ->where('mediaId', '[0-9]+');

Route::get('/media/original/{mediaId}', [MediaController::class, 'showOriginal'])
    ->name('media.original')
    ->where('mediaId', '[0-9]+');

Route::get('/', function () {
    $profile = Profile::first();

    // Si le profil n'existe pas, rediriger vers 404
    if (!$profile) {
        return abort(404);
    }
    
    // Charger les médias du profil (avatar et banner sont publics)
    $bannerMedia = $profile->getFirstMedia('banner');
    $avatarMedia = $profile->getFirstMedia('avatar');
    $logoMedia = $profile->getFirstMedia('logo');
    $certificationMedia = $profile->getFirstMedia('certification');
    
    $bannerUrl = $bannerMedia ? $bannerMedia->getUrl() : null;
    $avatarUrl = $avatarMedia ? $avatarMedia->getUrl() : null;
    $logoUrl = $logoMedia ? $logoMedia->getUrl() : null;
    $certificationUrl = $certificationMedia ? $certificationMedia->getUrl() : null;

    // Charger les posts visibles
    $posts = Post::visible()
        ->ordered()
        ->get()
        ->map(function ($post) {
            return [
                'id' => $post->id,
                'content' => $post->content,
                'type' => $post->type,
                'duration' => $post->duration,
                'likes_count' => $post->likes_count,
                'is_visible' => $post->is_visible,
                'is_blurred' => $post->is_blurred,
                'is_live' => $post->is_live,
                'created_at' => $post->created_at,
                'media' => $post->getMedia('media')->map(function ($media) use ($post) {
                    $routeName = $post->is_blurred ? 'media.blurred' : 'media.original';
                    return [
                        'id' => $media->id,
                        'url' => route($routeName, ['mediaId' => $media->id]),
                        'type' => $media->mime_type,
                    ];
                })->toArray(),
            ];
        });

    return Inertia::render('Welcome', [
        'profile' => [
            'id' => $profile->id,
            'name' => $profile->name,
            'biography' => $profile->biography,
            'is_online' => $profile->is_online,
            'is_within_online_hours' => $profile->isWithinOnlineHours(now(), 'Europe/Paris'),
            'photos_count' => $profile->photos_count,
            'videos_count' => $profile->videos_count,
            'likes_count' => $profile->likes_count,
            'action_label' => $profile->action_label,
            'banner_url' => $bannerUrl,
            'avatar_url' => $avatarUrl,
            'logo_url' => $logoUrl,
            'certification_url' => $certificationUrl,
            'script_url' => $profile->script_url,
        ],
        'posts' => $posts,
    ]);
})->name('home');

// Route dashboard commentée - pas d'authentification publique
// Route::get('dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
