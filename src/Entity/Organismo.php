<?php

namespace App\Entity;

use App\Repository\OrganismoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrganismoRepository::class)]
class Organismo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\Column(length: 60)]
    private ?string $Nombre = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $Domicilio = null;

    #[ORM\Column(nullable: true)]
    private ?int $Telefono = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->Nombre;
    }

    public function setNombre(string $Nombre): self
    {
        $this->Nombre = $Nombre;

        return $this;
    }

    public function getDomicilio(): ?string
    {
        return $this->Domicilio;
    }

    public function setDomicilio(?string $Domicilio): self
    {
        $this->Domicilio = $Domicilio;

        return $this;
    }

    public function getTelefono(): ?int
    {
        return $this->Telefono;
    }

    public function setTelefono(?int $Telefono): self
    {
        $this->Telefono = $Telefono;

        return $this;
    }
}
