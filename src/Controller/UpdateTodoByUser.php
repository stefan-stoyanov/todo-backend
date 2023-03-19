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
class UpdateTodoByUser extends AbstractController
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

    // Update existed Todo for Notes
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

        $todoId = $request->get("id");
        $todo = $this->todoRepository->find($todoId);
        if($todo == null) {
            throw new \DomainException("Can't find todo by id");
        }
        $note = $todo->getNotes();
        if($note == null) {
            throw new \DomainException("Can't find note by todo");
        }
        if($note->getUser() != $user) {
            throw new \DomainException("User not owner of note");
        }

        $todo->setText($request->get("text"));
        $todo->setChecked($request->get("checked"));
        $this->todoRepository->save($todo, true);

        return $todo;
    }
}