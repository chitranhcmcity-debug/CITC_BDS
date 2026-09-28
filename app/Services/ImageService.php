<?php

namespace App\Services;

use App\Repositories\GalleryRepository;

class ImageService
{
    public function __construct(private ?GalleryRepository $gallery = null, private ?UploadService $upload = null)
    {
        $this->gallery ??= new GalleryRepository;
        $this->upload ??= new UploadService;
    }

    public function attach(int $postId, array $files, int $coverIndex = 0): bool
    {
        foreach (array_values($files) as $i => $file) {
            if (! $this->gallery->add($postId, $file, $i, $i === $coverIndex)) {
                return false;
            }
        }

        return true;
    }

    public function deleteOwned(int $imageId, int $postId): bool
    {
        $file = $this->gallery->deleteOwnedImage($imageId, $postId);
        if (! $file) {
            return false;
        }$this->upload->delete($file);

        return true;
    }
}
