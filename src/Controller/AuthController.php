<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\AdminRepository;
use App\Repository\UserRepository;
use Firebase\JWT\JWT;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;


class AuthController extends AbstractController
{

    /**
     * @Route("/auth/register", name="register", methods={"POST"})
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     *
     * This method is responsible for user registration
     */
    public function register(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $content = json_decode($request->getContent());

        $password = $content->password;
        $email = $content->email;
        $username = $content->username;
        $user = new User();
        $user->setPassword($encoder->encodePassword($user, $password));
        $user->setEmail($email);
        $user->setUsername($username);
        $user->setRoles(array('ROLE_USER'));
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return $this->json([
            'success' => true
        ]);
    }

    /**
     * @Route("/auth/login", name="login", methods={"POST"})
     * @param Request $request
     * @param UserRepository $userRepository
     * @param UserPasswordEncoderInterface $encoder
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * This method is responsible for user authorization
     */
    public function login(Request $request, UserRepository $userRepository, UserPasswordEncoderInterface $encoder) : JsonResponse
    {
        $content = json_decode($request->getContent());
        $user = $userRepository->findOneBy([
            'email'=> $content->email,
        ]);
        if (!$user || !$encoder->isPasswordValid($user, $content->password)) {
            return $this->json([
                'message' => 'email or password is wrong.',
            ]);
        }

        $payload = [
            "user" => $user->getEmail(),
            "exp"  => (new \DateTime())->modify("+15 minutes")->getTimestamp(),
        ];

        $jwt = JWT::encode($payload, $this->getParameter('jwt_secret'), 'HS256');
        return $this->json([
            'message' => 'success!',
            'token' => sprintf('Bearer %s', $jwt),
            'user' => $user->getEmail()
        ]);
    }

    /**
     * @Route("/auth/user", name="user", methods={"POST"})
     * @param UserInterface $user
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * Get current user
     */
    public function currentUser(Request $request, UserRepository $user) : JsonResponse
    {
        $email = json_decode($request->getContent())->email;

        $currentUser = $user->findOneBy([
           'email' => $email
        ]);

        return $this->json([
            'user' => $currentUser
        ]);
    }
}

