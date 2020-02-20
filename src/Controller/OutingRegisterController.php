<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Outing;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/outing", name="outing_", requirements={"id": "\d+"})
 */
class OutingRegisterController extends AbstractController
{
    /**
     * @Route("/register/{id}", name="register", requirements={"id": "\d+"})
     */
    public function register(EntityManagerInterface $entityManager, $id)
    {
        $user = $this->getUser();

        $outingRepository = $entityManager->getRepository(Outing::class);
        $outing = $outingRepository->find($id);


        if ($outing->getStatusDisplayAndActions($user)['registerable']) {
            if ($user->getUsername() === 'Balrog' && $outing->getName() === 'Moria') {
                return $this->redirectToRoute('easter_egg');
            } else {
                $entityManager->persist($outing);
                $entityManager->flush();

                $this->addFlash('success', 'Vous avez bien été inscrit à cette sortie.');
            }
            $outing->addParticipant($user);


        } else {
            $this->addFlash('warning', 'Vous ne pouvez pas vous inscrire à cette sortie.');
        }


        return $this->redirectToRoute('outing_home');
    }

    /**
     * @Route("/remove/{id}", name="remove", requirements={"id": "\d+"})
     */
    public function remove(EntityManagerInterface $entityManager, $id)
    {
        $user = $this->getUser();

        $outingRepository = $entityManager->getRepository(Outing::class);
        $outing = $outingRepository->find($id);

        if ($outing->getStatusDisplayAndActions($user)['unregisterable']) {
            $outing->removeParticipant($user);

            $entityManager->persist($outing);
            $entityManager->flush();
            $this->addFlash('success', 'Vous avez bien été désinscrit de cette sortie.');

        } else {
            $this->addFlash('warning', 'Vous ne pouvez pas vous désinscrire de cette sortie.');
        }

        return $this->redirectToRoute('outing_home');
    }
}
