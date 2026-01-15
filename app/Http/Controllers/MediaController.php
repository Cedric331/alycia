<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\Response;

class MediaController extends Controller
{
    /**
     * Return image of a post (blurred or not depending on the parameter)
     */
    public function show(int $mediaId, bool $blurred = true): Response
    {
        $media = Media::findOrFail($mediaId);
        
        if ($media->model_type !== Post::class) {
            abort(404);
        }
        
        if ($blurred) {
            $path = $media->getPath('blurred');
        } else {
            $path = $media->getPath();
        }
        
        if (!file_exists($path)) {
            $path = $media->getPath();
            if (!file_exists($path)) {
                abort(404, 'Image not found');
            }
        }
        
        return response()->file($path, [
            'Content-Type' => $media->mime_type,
            'Content-Disposition' => 'inline',
            'Cache-Control' => 'public, max-age=31536000',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Return blurred image of a post
     */
    public function showBlurred(int $mediaId): Response
    {
        return $this->show($mediaId, true);
    }
    
    /**
     * Return original image of a post
     */
    public function showOriginal(int $mediaId): Response
    {
        return $this->show($mediaId, false);
    }
    
    /**
     * Return thumbnail for admin (via protected route)
     */
    public function showThumb(int $mediaId): Response
    {
        $media = Media::findOrFail($mediaId);
        
        $path = $media->getPath('thumb');
        
        if (!file_exists($path)) {
            $path = $media->getPath();
        }
        
        return response()->file($path, [
            'Content-Type' => $media->mime_type,
        ]);
    }
}

