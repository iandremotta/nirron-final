<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    const SOLICITANTE = 'ROLE_SOLICITANTE';
    const APROVADOR_ADMINISTRATIVO = 'ROLE_APROVADOR_ADMINISTRATIVO';
    const APROVADOR_OPERACIONAL = 'ROLE_APROVADOR_OPERACIONAL';
    const ADMINISTRADOR_ASSESSORIA = 'ROLE_ADMINISTRADOR_ASSESSORIA';
    const ADMINISTRADOR_LOGISTICA = 'ROLE_ADMINISTRADOR_LOGISTICA';
    const FINANCEIRO_ASSESSORIA = 'ROLE_FINANCEIRO_ASSESSORIA';
    const FINANCEIRO_LOGISTICA = 'ROLE_FINANCEIRO_LOGISTICA';
    const SUPER_USUARIO = 'ROLE_SUPER';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $username = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $nome = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'usuario', targetEntity: Solicitacao::class)]
    private Collection $solicitacaos;

    #[ORM\OneToMany(mappedBy: 'aprovador', targetEntity: Solicitacao::class)]
    private Collection $aprovador;

    #[ORM\OneToMany(mappedBy: 'administrador', targetEntity: Solicitacao::class)]
    private Collection $administrador;

    #[ORM\OneToMany(mappedBy: 'recusador', targetEntity: Solicitacao::class)]
    private Collection $recusador;

    #[ORM\Column]
    private ?bool $isActive;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable('now');
        $this->isActive = true;
        $this->solicitacaos = new ArrayCollection();
        $this->aprovador = new ArrayCollection();
        $this->administrador = new ArrayCollection();
        $this->recusador = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        // $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function isEnabled()
    {
        return $this->isActive;
    }

    public function getNome(): ?string
    {
        return $this->nome;
    }

    public function setNome(string $nome): self
    {
        $this->nome = $nome;

        return $this;
    }

    /**
     * @return Collection<int, Solicitacao>
     */
    public function getSolicitacaos(): Collection
    {
        return $this->solicitacaos;
    }

    public function addSolicitacao(Solicitacao $solicitacao): self
    {
        if (!$this->solicitacaos->contains($solicitacao)) {
            $this->solicitacaos->add($solicitacao);
            $solicitacao->setUsuario($this);
        }

        return $this;
    }

    public function removeSolicitacao(Solicitacao $solicitacao): self
    {
        if ($this->solicitacaos->removeElement($solicitacao)) {
            // set the owning side to null (unless already changed)
            if ($solicitacao->getUsuario() === $this) {
                $solicitacao->setUsuario(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function isIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return Collection<int, Solicitacao>
     */
    public function getAprovador(): Collection
    {
        return $this->aprovador;
    }

    public function addAprovador(Solicitacao $aprovador): self
    {
        if (!$this->aprovador->contains($aprovador)) {
            $this->aprovador->add($aprovador);
            $aprovador->setAprovador($this);
        }

        return $this;
    }

    public function removeAprovador(Solicitacao $aprovador): self
    {
        if ($this->aprovador->removeElement($aprovador)) {
            // set the owning side to null (unless already changed)
            if ($aprovador->getAprovador() === $this) {
                $aprovador->setAprovador(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Solicitacao>
     */
    public function getAdministrador(): Collection
    {
        return $this->administrador;
    }

    public function addAdministrador(Solicitacao $administrador): self
    {
        if (!$this->administrador->contains($administrador)) {
            $this->administrador->add($administrador);
            $administrador->setAdministrador($this);
        }

        return $this;
    }

    public function removeAdministrador(Solicitacao $administrador): self
    {
        if ($this->administrador->removeElement($administrador)) {
            // set the owning side to null (unless already changed)
            if ($administrador->getAdministrador() === $this) {
                $administrador->setAdministrador(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Solicitacao>
     */
    public function getRecusador(): Collection
    {
        return $this->recusador;
    }

    public function addRecusador(Solicitacao $recusador): self
    {
        if (!$this->recusador->contains($recusador)) {
            $this->recusador->add($recusador);
            $recusador->setRecusador($this);
        }

        return $this;
    }

    public function removeRecusador(Solicitacao $recusador): self
    {
        if ($this->recusador->removeElement($recusador)) {
            // set the owning side to null (unless already changed)
            if ($recusador->getRecusador() === $this) {
                $recusador->setRecusador(null);
            }
        }

        return $this;
    }
}
