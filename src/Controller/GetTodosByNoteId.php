<?php
 
namespace App\Controller;
 
use App\Entity\Notes;
use App\Repository\NotesRepository;
use App\Repository\TodoRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints\Collection;

#[AsController]
class GetTodosByNoteId extends AbstractController
{
    private NotesRepository $noteRepository;
    private TodoRepository $todoRepository;
    private JWTTokenManagerInterface $jwtManager;
    private Security $security;

    public function __construct(NotesRepository $noteRepository, 
                                TodoRepository $todoRepository,
                                JWTTokenManagerInterface $jwtManager,
                                Security $security) {
        $this->noteRepository = $noteRepository;
        $this->todoRepository = $todoRepository;
        $this->jwtManager = $jwtManager;
        $this->security = $security;
    }

    // Get page of todos by note id.
    // Verify if note exsit and any user can excist todo by note id.
    // If note not found throw exception.
    // Example url: http://localhost:8000/api/todos/note/{noteId}
    public function __invoke(Request $request): Response
    {
        $noteId = $request->attributes->get('noteId');
        $note = $this->noteRepository->find($noteId);
        if (!$note) {
            throw new NotFoundHttpException('Note not found');
        }
        $todos = $this->todoRepository->getTodosByNoteId($noteId, 0);
        
        $jsonData = [];
        foreach ($todos as $todo) {
            $jsonData[] = [
                'id' => $todo->getId(),
                'text' => $todo->getText(),
                'checked' => $todo->isChecked()
            ];
        }
        return new Response(json_encode($jsonData), 200, ['Content-Type' => 'application/json']);
    }
}