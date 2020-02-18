<?php

namespace App\Controller;

use App\Entity\Group;
use App\Form\GroupType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/group", name="group_")
 */
class GroupController extends AbstractController
{
    /**
     * @Route("/home", name="home")
     */
    public function home()
    {


        return $this->render(
            'group/home.html.twig',
            [
                'controller_name' => 'GroupController',
            ]
        );
    }

    /**
     * @Route("/create", name="create")
     */
    public function create(Request $request, EntityManagerInterface $entityManager)
    {
        $group = new Group();
        $groupForm = $this->createForm(GroupType::class, $group);

        $groupForm->handleRequest($request);

        if ($groupForm->isSubmitted() && $groupForm->isValid()) {
            $group->setCreator($this->getUser());
            $entityManager->persist($group);
            $entityManager->flush();

            $this->addFlash('success', 'Groupe privé créé avec succès, vous pouvez maintenant inviter des membres.');

            return $this->redirectToRoute('group_create'); //TODO à rediriger vers la page du groupe

        }


        return $this->render(
            'group/create.html.twig',
            [
                'groupFormView' => $groupForm->createView(),
            ]
        );
    }
}
