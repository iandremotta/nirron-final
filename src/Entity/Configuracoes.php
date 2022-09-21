<?php

namespace App\Entity;

use App\Repository\ConfiguracoesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConfiguracoesRepository::class)]
class Configuracoes
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pathAssessoria = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pathLogistica = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPathAssessoria(): ?string
    {
        return $this->pathAssessoria;
    }

    public function setPathAssessoria(?string $pathAssessoria): self
    {
        $this->pathAssessoria = $pathAssessoria;

        return $this;
    }

    public function getPathLogistica(): ?string
    {
        return $this->pathLogistica;
    }

    public function setPathLogistica(?string $pathLogistica): self
    {
        $this->pathLogistica = $pathLogistica;

        return $this;
    }
}
