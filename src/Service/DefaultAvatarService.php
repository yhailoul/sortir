<?php

namespace App\Service;

use App\Entity\User;

class DefaultAvatarService
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

    public function randDefaultPhoto(User $user): void
    {
        if (!$user->getPhoto()) {
            $user->setPhoto($this->defaultPhotos[array_rand($this->defaultPhotos)]);
        }
    }
}
