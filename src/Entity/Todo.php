<?php

namespace App\Entity;

use App\Repository\TodoRepository;

use Doctrine\ORM\Mapping as ORM;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\ApiResource;
use App\Controller\CreateTodoByUser;
use App\Controller\DeleteTodoByUser;
use App\Controller\UpdateTodoByUser;
use App\Controller\GetTodosByNoteId;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    paginationItemsPerPage: 20,
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:create']],
    operations: [
        new GetCollection(security: "is_granted('ROLE_USER')", controller: GetTodosByNoteId::class, 
            uriTemplate: "/todos/note/{noteId}", 
            name: "get_todos_by_note_id",
            read: false),
        new Post(security: "is_granted('ROLE_USER')", controller: CreateTodoByUser::class),
        new Get(security: "is_granted('ROLE_USER')"),
        new Patch(security: "is_granted('ROLE_USER')", controller: UpdateTodoByUser::class),
        new Delete(security: "is_granted('ROLE_USER')", controller: DeleteTodoByUser::class),
    ]
)]
#[ORM\Entity(repositoryClass: TodoRepository::class)]
class Todo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:create', 'user:read'])]
    private ?string $text = null;

    #[ORM\Column]
    #[Groups(['user:create', 'user:read'])]
    private ?bool $checked = null;

    #[ORM\ManyToOne(inversedBy: 'todos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Notes $notes = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function isChecked(): ?bool
    {
        return $this->checked;
    }

    public function setChecked(bool $checked): self
    {
        $this->checked = $checked;

        return $this;
    }

    public function getNotes(): ?Notes
    {
        return $this->notes;
    }

    public function setNotes(?Notes $notes): self
    {
        $this->notes = $notes;

        return $this;
    }
}
