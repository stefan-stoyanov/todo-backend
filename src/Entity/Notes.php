<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\NotesRepository;
use App\Controller\CreateNoteByUser;
use App\Controller\UpdateNoteByUser;
use App\Controller\DeleteNoteByUser;
use App\Controller\GetNotesByUser;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Get;

#[ApiResource(
    paginationItemsPerPage: 20,
    operations: [
        new GetCollection(security: "is_granted('ROLE_USER')"),
        new Post(security: "is_granted('ROLE_USER')", controller: CreateNoteByUser::class),
        new Get(security: "is_granted('ROLE_USER')"),
        new GetCollection(security: "is_granted('ROLE_USER')", controller: GetNotesByUser::class, 
            uriTemplate: "/notes/user/{id}", 
            name: "get_notes_by_user",
            read: false,
        ),
        new Patch(security: "is_granted('ROLE_USER')", controller: UpdateNoteByUser::class),
        new Delete(security: "is_granted('ROLE_USER')", controller: DeleteNoteByUser::class),
    ]
)]
#[ORM\Entity(repositoryClass: NotesRepository::class)]
class Notes
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:create', 'user:write', 'user:read'])]
    private ?string $name = null;

    #[Groups(['user:create', 'user:write', 'user:read'])]
    #[ORM\ManyToOne(User::class, inversedBy: "notes")]
    #[ORM\JoinColumn(nullable: false, name: "user_id_id", referencedColumnName: "id")]
    private ?User $user = null;

    #[ORM\OneToMany(mappedBy: 'notes', targetEntity: Todo::class, cascade: ['persist', 'remove'])]
    private Collection $todos;

    public function __construct()
    {
        $this->todos = new ArrayCollection();
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

    public function getUser(): ?user
    {
        return $this->user;
    }

    public function setUser(?user $user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return Collection<int, Todo>
     */
    public function getTodos(): Collection
    {
        return $this->todos;
    }

    public function addTodo(Todo $todo): self
    {
        if (!$this->todos->contains($todo)) {
            $this->todos->add($todo);
            $todo->setNotes($this);
        }

        return $this;
    }

    public function removeTodo(Todo $todo): self
    {
        if ($this->todos->removeElement($todo)) {
            if ($todo->getNotes() === $this) {
                $todo->setNotes(null);
            }
        }

        return $this;
    }
}
