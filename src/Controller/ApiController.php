<?php

namespace App\Controller;

use App\Entity\Organizations;
use App\Entity\User;
use App\Repository\OrganizationsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;


class ApiController extends AbstractController
{
    /**
     * @Route("/api/home", methods={"GET"})
     */
    public function home(OrganizationsRepository $repository,UserInterface $user)
    {
      $allOrg = $repository->findAllUserOrganization($user->getId());

      return $this->json([
          'organization' => $allOrg
      ]);
    }

    /**
     * @Route("/api/create", methods={"POST"})
     */
    public function createOrganization(Request $request, UserInterface $user)
    {
        $content = json_decode($request->getContent());
        if ($content->name === null) {
            return $this->json([
               'message' => 'error'
            ]);
        }

        $org = new Organizations();
        $org->setName($content->name);
        $org->setAdminId($user->getId());
        $em = $this->getDoctrine()->getManager();
        $em->persist($org);
        $em->flush();

        return $this->json([
           'organization' => $org
        ]);
    }

}
