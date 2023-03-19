<?php
 
namespace App\Controller;

use ApiPlatform\OpenApi\Model\Response;
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
class DeleteTodoByUser extends AbstractController
{
    private NotesRepository $noteRepository;
    private TodoRepository $todoRepository;
    private UserRepository $userRepository;
    private JWTTokenManagerInterface $jwtManager;
    private Security $security;

    public function __construct(NotesRepository $noteRepository, 
                                TodoRepository $todoRepository,
                                UserRepository $userRepository,
                                JWTTokenManagerInterface $jwtManager,
                                Security $security) {
        $this->noteRepository = $noteRepository;
        $this->todoRepository = $todoRepository;
        $this->userRepository = $userRepository;
        $this->jwtManager = $jwtManager;
        $this->security = $security;
    }

    // delete existed todo or throw execption if not found or notes not owner or user not woner note
    public function __invoke(Request $request): Response
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


        $todoId = $request->get('id');
        if(!is_numeric($todoId)) {
            throw new \DomainException("Todo id is not int");
        }
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

        //remove todo from note and save to database
        $note->removeTodo($todo);
        $this->todoRepository->remove($todo, true);
        $this->noteRepository->save($note, true);
        
        return new Response();
    }
}