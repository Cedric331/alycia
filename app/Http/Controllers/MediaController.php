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
     * Servir une image d'un post (floutée ou non selon le paramètre)
     */
    public function show(int $mediaId, bool $blurred = true): Response
    {
        $media = Media::findOrFail($mediaId);
        
        // Vérifier que le média appartient à un Post
        if ($media->model_type !== Post::class) {
            abort(404);
        }
        
        // Obtenir le chemin selon le mode
        if ($blurred) {
            $path = $media->getPath('blurred');
        } else {
            // Pour les images non floutées, on sert l'original
            $path = $media->getPath();
        }
        
        if (!file_exists($path)) {
            // Fallback sur l'original si la conversion n'existe pas
            $path = $media->getPath();
            if (!file_exists($path)) {
                abort(404, 'Image not found');
            }
        }
        
        // Headers anti-téléchargement
        return response()->file($path, [
            'Content-Type' => $media->mime_type,
            'Content-Disposition' => 'inline',
            'Cache-Control' => 'public, max-age=31536000',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
    
    /**
     * Servir une image floutée
     */
    public function showBlurred(int $mediaId): Response
    {
        return $this->show($mediaId, true);
    }
    
    /**
     * Servir une image non floutée (originale)
     */
    public function showOriginal(int $mediaId): Response
    {
        return $this->show($mediaId, false);
    }
    
    /**
     * Servir le thumbnail pour l'admin (via route protégée)
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

