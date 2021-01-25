<?php

namespace App\Controller;

use App\Entity\Organizations;
use App\Repository\OrganizationsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;


class ApiController extends AbstractController
{
    /**
     * @Route("/api/home", methods={"GET"})
     * @param OrganizationsRepository $repository
     * @param UserInterface $user
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * This method is responsible for getting the organizational user in home page
     */
    public function home(OrganizationsRepository $repository,UserInterface $user) : JsonResponse
    {
      $allOrg = $repository->findAllUserOrganization($user->getId());

      return $this->json([
          'organization' => $allOrg
      ]);
    }

    /**
     * @Route("/api/create", methods={"POST"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * This method is responsible for create organizationgit
     */
    public function createOrganization(Request $request) : JsonResponse
    {
        $content = json_decode($request->getContent());

        if ($content->userId === null) {
            return $this->json([
               'message' => 'error'
            ]);
        }

        $org = new Organizations();
        $org->setName($content->companyName);
        $org->setAdminId($content->userId);
        $em = $this->getDoctrine()->getManager();
        $em->persist($org);
        $em->flush();

        return $this->json([
           'organization' => $org
        ]);
    }

}
