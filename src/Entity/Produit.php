<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?bool $inStock = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageURL = null;

    #[ORM\ManyToOne(inversedBy: 'produits')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entreprise $entreprise = null;

    #[ORM\ManyToOne(inversedBy: 'produits')]
    #[ORM\JoinColumn(nullable: false)]
    private ?SousCategorie $subCategorie = null;

    #[ORM\ManyToMany(targetEntity: Appellation::class, inversedBy: 'produits')]
    private Collection $hasAppellation;

    public function __construct()
    {
        $this->hasAppellation = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function isInStock(): ?bool
    {
        return $this->inStock;
    }

    public function setInStock(bool $inStock): self
    {
        $this->inStock = $inStock;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getImageURL(): ?string
    {
        return $this->imageURL;
    }

    public function setImageURL(?string $imageURL): self
    {
        $this->imageURL = $imageURL;

        return $this;
    }

    public function getEntreprise(): ?Entreprise
    {
        return $this->entreprise;
    }

    public function setEntreprise(?Entreprise $entreprise): self
    {
        $this->entreprise = $entreprise;

        return $this;
    }

    public function getSubCategorie(): ?SousCategorie
    {
        return $this->subCategorie;
    }

    public function setSubCategorie(?SousCategorie $subCategorie): self
    {
        $this->subCategorie = $subCategorie;

        return $this;
    }

    /**
     * @return Collection<int, Appellation>
     */
    public function getHasAppellation(): Collection
    {
        return $this->hasAppellation;
    }

    public function addHasAppellation(Appellation $hasAppellation): self
    {
        if (!$this->hasAppellation->contains($hasAppellation)) {
            $this->hasAppellation->add($hasAppellation);
        }

        return $this;
    }

    public function removeHasAppellation(Appellation $hasAppellation): self
    {
        $this->hasAppellation->removeElement($hasAppellation);

        return $this;
    }
}
