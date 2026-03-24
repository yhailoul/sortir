<?php

namespace App\Entity;

use App\Repository\CampusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CampusRepository::class)]
class Campus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'campus')]
    private Collection $participants;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getparticipants(): Collection
    {
        return $this->participants;
    }

    public function addparticipants(User $participants): static
    {
        if (!$this->participants->contains($participants)) {
            $this->participants->add($participants);
            $participants->setCampus($this);
        }

        return $this;
    }

    public function removeparticipants(User $participants): static
    {
        if ($this->participants->removeElement($participants)) {
            // set the owning side to null (unless already changed)
            if ($participants->getCampus() === $this) {
                $participants->setCampus(null);
            }
        }

        return $this;
    }
}
