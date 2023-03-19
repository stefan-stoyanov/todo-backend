<?php
 
namespace App\Controller;
 
use App\Entity\Notes;
use App\Repository\NotesRepository;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;

#[AsController]
class UpdateNoteByUser extends AbstractController
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

    // update existed note or throw execption if not found or user not owner
    public function __invoke(Request $request): Notes
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

        $noteId = $request->get("id");
        $note = $this->noteRepository->find($noteId);
        if($note == null) {
            throw new \DomainException("Can't find note by id");
        }
        if($note->getUser() != $user) {
            throw new \DomainException("User not owner of note");
        }

        $name = $request->get("name");
        $note->setName($name);

        $this->noteRepository->save($note, true);
 
        return $note;
    }
}