<?php

namespace App\Twig;

use App\Service\AvatarService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AvatarExtension extends AbstractExtension
{
    public function __construct(private readonly AvatarService $avatarService) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('photoPath', [$this->avatarService, 'resolvePhotoPath']),
        ];
    }
}
