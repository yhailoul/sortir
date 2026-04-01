<?php

namespace App\Service;

use App\Entity\User;

class AvatarService
{
    private array $defaultPhotos = [
        'blue_soft_abstract.png',
        'blue_deep_abstract.png',
        'cyan_mist_abstract.png',
        'teal_soft_abstract.png',
        'green_fresh_abstract.png',
        'green_deep_abstract.png',
        'lime_light_abstract.png',
        'yellow_warm_abstract.png',
        'amber_soft_abstract.png',
        'orange_pop_abstract.png',
        'red_soft_abstract.png',
        'rose_light_abstract.png',
        'pink_soft_abstract.png',
        'purple_mist_abstract.png',
        'violet_deep_abstract.png',
        'indigo_soft_abstract.png',
        'slate_clean_abstract.png',
        'gray_modern_abstract.png',
    ];

    public function randUserPhoto(): string
    {
        return $this->defaultPhotos[array_rand($this->defaultPhotos)];
    }

    public function correctionPhotoProfile(User $user): void
    {
        if (!$user->getPhoto()) {
            $user->setPhoto($this->randUserPhoto());
        }
    }

    public function isDefaultPhoto(string $photo): bool
    {
        return in_array($photo, $this->defaultPhotos, true);
    }

    public function resolvePhotoPath(string $photo): string
    {
        if ($this->isDefaultPhoto($photo)) {
            return 'defaultsPhotosProfile/' . $photo;
        }

        return 'uploads/' . $photo;
    }
}
