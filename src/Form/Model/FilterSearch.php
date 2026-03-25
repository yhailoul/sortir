<?php

namespace App\Form\Model;

class FilterSearch
{
    private ?bool $organized = null;

    private ?bool $signedUp = null;

    private ?bool $passed = null;

    public function __construct()
    {
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



}
