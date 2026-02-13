<?php

namespace App\User\Controller;

use App\User\Service\Interface\UserServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/users')]
class UserController extends AbstractController
{
    private UserServiceInterface $userService;
    private ValidatorInterface $validator;

    public function __construct(
        UserServiceInterface $userService,
        ValidatorInterface $validator
    ) {
        $this->userService = $userService;
        $this->validator = $validator;
    }

    #[Route('/me', name: 'user_profile', methods: ['GET'])]
    public function getProfile(): JsonResponse
    {
        try {
            $user = $this->getUser();
            
            if (!$user) {
                return new JsonResponse(['error' => 'User not authenticated'], 401);
            }

            $userData = $this->userService->getUserById($user->getId());
            
            if (!$userData) {
                return new JsonResponse(['error' => 'User not found'], 404);
            }

            return new JsonResponse($userData, 200);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/me', name: 'update_profile', methods: ['PUT'])]
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $user = $this->getUser();
            
            if (!$user) {
                return new JsonResponse(['error' => 'User not authenticated'], 401);
            }

            $data = json_decode($request->getContent(), true);
            
            if (!$data) {
                return new JsonResponse(['error' => 'Invalid JSON'], 400);
            }

            $avatarConstraint = new Assert\Optional([new Assert\Url(), new Assert\Length(['max' => 255])]);
            $rolesConstraint = new Assert\Optional([new Assert\Type('array'), new Assert\All([
                new Assert\Type('string'),
                new Assert\Choice(['ROLE_USER', 'ROLE_ADMIN', 'ROLE_MODERATOR'])
            ])]);

            $constraints = new Assert\Collection([
                'avatar' => $avatarConstraint,
                'roles' => $rolesConstraint
            ]);

            $violations = $this->validator->validate($data, $constraints);
            
            if (count($violations) > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $errors[$violation->getPropertyPath()] = $violation->getMessage();
                }
                return new JsonResponse(['errors' => $errors], 400);
            }

            $updatedUser = $this->userService->updateUserProfile($user->getId(), $data);
            
            return new JsonResponse($updatedUser, 200);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/{id}', name: 'get_user', methods: ['GET'])]
    public function getUserById(int $id): JsonResponse
    {
        try {
            $userData = $this->userService->getUserById($id);
            
            if (!$userData) {
                return new JsonResponse(['error' => 'User not found'], 404);
            }

            unset($userData['roles']);

            return new JsonResponse($userData, 200);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
