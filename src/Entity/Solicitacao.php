<?php

namespace App\Entity;

use App\Repository\SolicitacaoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: SolicitacaoRepository::class)]
class Solicitacao
{
    const STATUS_PENDENTE = 1;
    const STATUS_APROVADOR_RECUSADO = 2;
    const STATUS_APROVADOR_OK = 3;
    const STATUS_ADMINISTRADOR_RECUSADO = 4;
    const STATUS_ADMINISTRADOR_OK = 5;

    const TIPO_ADMINISTRATIVO = 1;
    const TIPO_OPERACIONAL = 2;

    const EMPRESA_ASSESSORIA = 'assessoria';
    const EMPRESA_LOGISTICA = 'logistica';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'solicitacaos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $usuario = null;

    #[ORM\Column(length: 255)]
    private ?string $titulo = null;

    #[ORM\Column(length: 255)]
    private ?string $empresa = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $notaFiscal = null;

    #[ORM\Column]
    private ?string $valor = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $tipo = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $vencimento = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $justificativa = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $recusa = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $status = null;

    #[ORM\ManyToOne(inversedBy: 'aprovador')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $aprovador = null;

    #[ORM\ManyToOne(inversedBy: 'administrador')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $administrador = null;

    #[ORM\ManyToOne(inversedBy: 'recusador')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $recusador = null;

    // NOTE: This is not a mapped field of entity metadata, just a simple property.
    #[Vich\UploadableField(mapping: 'products', fileNameProperty: 'imageName')]
    private ?File $imageFile = null;

    #[ORM\Column(type: 'string')]
    private ?string $imageName = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsuario(): ?User
    {
        return $this->usuario;
    }

    public function setUsuario(?User $usuario): self
    {
        $this->usuario = $usuario;

        return $this;
    }

    public function getTitulo(): ?string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): self
    {
        $this->titulo = $titulo;

        return $this;
    }

    public function getNotaFiscal(): ?string
    {
        return $this->notaFiscal;
    }

    public function setNotaFiscal(string $notaFiscal): self
    {
        $this->notaFiscal = $notaFiscal;

        return $this;
    }

    public function getValor(): ?string
    {
        return $this->valor;
    }

    public function setValor(string $valor): self
    {
        $this->valor = $valor;

        return $this;
    }

    public function getTipo(): ?int
    {
        return $this->tipo;
    }

    public function setTipo(int $tipo): self
    {
        $this->tipo = $tipo;

        return $this;
    }

    public function getVencimento(): ?\DateTimeInterface
    {
        return $this->vencimento;
    }

    public function setVencimento(\DateTimeInterface $vencimento): self
    {
        $this->vencimento = $vencimento;

        return $this;
    }

    public function getJustificativa(): ?string
    {
        return $this->justificativa;
    }

    public function setJustificativa(string $justificativa): self
    {
        $this->justificativa = $justificativa;

        return $this;
    }

    public function getRecusa(): ?string
    {
        return $this->recusa;
    }

    public function setRecusa(string $recusa): self
    {
        $this->recusa = $recusa;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getAprovador(): ?User
    {
        return $this->aprovador;
    }

    public function setAprovador(?User $aprovador): self
    {
        $this->aprovador = $aprovador;

        return $this;
    }

    public function getAdministrador(): ?User
    {
        return $this->administrador;
    }

    public function setAdministrador(?User $administrador): self
    {
        $this->administrador = $administrador;

        return $this;
    }


    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $imageFile
     */
    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageName(?string $imageName): void
    {
        $this->imageName = $imageName;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getEmpresa(): ?string
    {
        return $this->empresa;
    }

    public function setEmpresa(string $empresa): self
    {
        $this->empresa = $empresa;

        return $this;
    }

    public function getRecusador(): ?User
    {
        return $this->recusador;
    }

    public function setRecusador(?User $recusador): self
    {
        $this->recusador = $recusador;

        return $this;
    }
}
