<?php

namespace App\User\Controller;

use App\User\Service\Interface\UserServiceInterface;
use App\User\Service\Interface\JWTTokenServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/auth')]
class OAuthController extends AbstractController
{
    private UserServiceInterface $userService;
    private JWTTokenServiceInterface $jwtTokenService;
    private ValidatorInterface $validator;

    public function __construct(
        UserServiceInterface $userService,
        JWTTokenServiceInterface $jwtTokenService,
        ValidatorInterface $validator
    ) {
        $this->userService = $userService;
        $this->jwtTokenService = $jwtTokenService;
        $this->validator = $validator;
    }

    #[Route('/google', name: 'auth_google', methods: ['POST'])]
    public function googleAuth(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!$data) {
                return new JsonResponse(['error' => 'Invalid JSON'], 400);
            }

            $constraints = new Assert\Collection([
                'token' => [new Assert\NotBlank(), new Assert\Type('string')],
            ]);

            $violations = $this->validator->validate($data, $constraints);
            
            if (count($violations) > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $errors[$violation->getPropertyPath()] = $violation->getMessage();
                }
                return new JsonResponse(['errors' => $errors], 400);
            }

            $oauthResponse = $this->userService->authenticateWithGoogle($data['token']);
            
            return new JsonResponse($oauthResponse->toArray(), 200);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/facebook', name: 'auth_facebook', methods: ['POST'])]
    public function facebookAuth(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!$data) {
                return new JsonResponse(['error' => 'Invalid JSON'], 400);
            }

            $constraints = new Assert\Collection([
                'token' => [new Assert\NotBlank(), new Assert\Type('string')],
            ]);

            $violations = $this->validator->validate($data, $constraints);
            
            if (count($violations) > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $errors[$violation->getPropertyPath()] = $violation->getMessage();
                }
                return new JsonResponse(['errors' => $errors], 400);
            }

            $oauthResponse = $this->userService->authenticateWithFacebook($data['token']);
            
            return new JsonResponse($oauthResponse->toArray(), 200);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/refresh', name: 'auth_refresh', methods: ['POST'])]
    public function refreshToken(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!$data || !isset($data['refresh_token'])) {
                return new JsonResponse(['error' => 'Refresh token is required'], 400);
            }

            $tokenResponse = $this->jwtTokenService->refreshToken($data['refresh_token']);
            
            return new JsonResponse($tokenResponse, 200);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 401);
        }
    }

    #[Route('/logout', name: 'auth_logout', methods: ['POST'])]
    public function logout(Request $request): JsonResponse
    {
        try {
            $token = $request->headers->get('Authorization');
            
            if (!$token || !str_starts_with($token, 'Bearer ')) {
                return new JsonResponse(['error' => 'Invalid token'], 401);
            }

            $token = substr($token, 7);
            $this->jwtTokenService->revokeToken($token);
            
            return new JsonResponse(['message' => 'Successfully logged out'], 200);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 401);
        }
    }
}
