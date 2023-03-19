<?php
 
namespace App\Controller;
 
use App\Entity\Notes;
use App\Entity\Todo;
use App\Repository\NotesRepository;
use App\Repository\TodoRepository;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;

#[AsController]
class CreateTodoByUser extends AbstractController
{
    private NotesRepository $noteRepository;
    private UserRepository $userRepository;
    private TodoRepository $todoRepository;
    private JWTTokenManagerInterface $jwtManager;
    private Security $security;

    public function __construct(NotesRepository $noteRepository, 
                                UserRepository $userRepository,
                                TodoRepository $todoRepository,
                                JWTTokenManagerInterface $jwtManager,
                                Security $security) {
        $this->noteRepository = $noteRepository;
        $this->userRepository = $userRepository;
        $this->todoRepository = $todoRepository;
        $this->jwtManager = $jwtManager;
        $this->security = $security;
    }

    // Create new Todo for Note. Input params: noteId, title, checked. 
    // Verify who owner of this note and thwo exception if not or user absnet.
    // All input data from json request body
    public function __invoke(Request $request): Todo
    {
        $token = $this->security->getToken();
        if (!$token) {
            throw new AuthenticationException('Authentication token not found');
        }
        $payload = $this->jwtManager->decode($token);
        $userEmail = $payload['email'];
        $user = $this->userRepository->findOneByLogin($userEmail);
        if($user == null) {
            throw new \DomainException("Can't find user by login");
        }

        $noteId = $request->get("noteId");
        $note = $this->noteRepository->find($noteId);
        if($note == null) {
            throw new \DomainException("Can't find note by id");
        }
        if($note->getUser() != $user) {
            throw new \DomainException("User not owner of note");
        }

        $text = $request->get("text");
        $checked = $request->get("checked");

        $todo = new Todo();
        $todo->setText($text);
        $todo->setChecked($checked);
        $todo->setNotes($note);

        $this->todoRepository->save($todo, true);

        return $todo;
    }
}