<?php

namespace App\Form\Model;

use DateTime;
use DateTimeInterface;
use App\Entity\Campus;

class FilterSearch
{
    private ?Campus $campus = null;
    private ?bool $organized = null;

    private ?bool $signedUp = null;

    private ?bool $passed = null;

    private ?string $searchTerm = null;

    private ?DateTime $startDate = null;

    private ?DateTime $endDate = null;

    public function __construct()
    {
    }

    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): void
    {
        $this->campus = $campus;
    }

    public function getOrganized(): ?bool
    {
        return $this->organized;
    }

    public function setOrganized(?bool $organized): void
    {
        $this->organized = $organized;
    }

    public function getSignedUp(): ?bool
    {
        return $this->signedUp;
    }

    public function setSignedUp(?bool $signedUp): void
    {
        $this->signedUp = $signedUp;
    }

    public function getPassed(): ?bool
    {
        return $this->passed;
    }

    public function setPassed(?bool $passed): void
    {
        $this->passed = $passed;
    }

    public function getSearchTerm(): ?string
    {
        return $this->searchTerm;
    }

    public function setSearchTerm(?string $searchTerm): void
    {
        $this->searchTerm = $searchTerm;
    }

    public function getStartDate(): ?DateTime
    {
        return $this->startDate;
    }

    public function setStartDate(?DateTime $startDate): void
    {
        $this->startDate = $startDate;
    }

    public function getEndDate(): ?DateTime
    {
        return $this->endDate;
    }

    public function setEndDate(?DateTime $endDate): void
    {
        $this->endDate = $endDate;
    }



    public function __toString(): string
    {
        $campusName = $this->campus ? $this->campus->getName() : 'Aucun campus';
        $organized = $this->organized ? 'Oui' : 'Non';
        $signedUp = $this->signedUp ? 'Oui' : 'Non';
        $passed = $this->passed ? 'Oui' : 'Non';
        $searchTerm = $this->searchTerm ?? 'Aucun terme';
        $startDate = $this->startDate ? $this->startDate->format('Y-m-d') : 'Non défini';
        $endDate = $this->endDate ? $this->endDate->format('Y-m-d') : 'Non défini';

        return sprintf(
            "Filtre de recherche : [Campus: %s, Organisé: %s, Inscrit: %s, Passé: %s, Terme: %s, Début: %s, Fin: %s]",
            $campusName,
            $organized,
            $signedUp,
            $passed,
            $searchTerm,
            $startDate,
            $endDate
        );
    }



}
