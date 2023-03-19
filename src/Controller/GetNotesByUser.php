<?php
 
namespace App\Controller;
 
use App\Entity\Notes;
use App\Repository\NotesRepository;
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
class GetNotesByUser extends AbstractController
{
    private NotesRepository $noteRepository;
    private UserRepository $userRepository;
    private JWTTokenManagerInterface $jwtManager;
    private Security $security;

    public function __construct(NotesRepository $noteRepository, 
                                UserRepository $userRepository,
                                JWTTokenManagerInterface $jwtManager,
                                Security $security) {
        $this->noteRepository = $noteRepository;
        $this->userRepository = $userRepository;
        $this->jwtManager = $jwtManager;
        $this->security = $security;
    }

    // Get page of notes by user id in url or throw exception if user not found.
    // Example url: http://localhost:8000/api/notes/user/1
    public function __invoke(Request $request): Response
    {
        $user = $this->security->getUser();
        $userId = $request->attributes->get('id');
        $user = $this->userRepository->find($userId);
        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }
        $notes = $this->noteRepository->findBy(['user' => $user]);
        $notes = new ArrayCollection($notes);
        
        $jsonData = [];
        foreach ($notes as $note) {
            $jsonData[] = [
                'id' => $note->getId(),
                'name' => $note->getName(),
            ];
        }
        return new Response(json_encode($jsonData), 200, ['Content-Type' => 'application/json']);
    }

}