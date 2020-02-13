<?php

namespace App\Controller;

use App\Entity\Establishment;
use App\Entity\Outing;
use App\Entity\Status;
use App\Entity\User;
use App\Form\OutingHomeType;
use App\Form\OutingType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/outing", name="outing_")
 */
class OutingController extends AbstractController
{
    /**
     * @Route("/home", name="home")
     */
    public function home(Request $request, EntityManagerInterface $entityManager)
    {
        $homeOutingForm = $this->createForm(OutingHomeType::class);
        $homeOutingForm->handleRequest($request);

        $outings = null;

        if ($homeOutingForm->isSubmitted() && $homeOutingForm->isValid()) {
            $outingRepository = $entityManager->getRepository(Outing::class);
            $data = $homeOutingForm->getData();


            $outings = $outingRepository->findOutingForHome($this->getUser(), $data);

        }

        return $this->render(
            'outing/home.html.twig',
            [
                'homeOutingFormView' => $homeOutingForm->createView(),
                'outings' => $outings,
            ]
        );
    }

    /**
     * @Route("/create", name="create")
     */
    public function create(Request $request, EntityManagerInterface $entityManager)
    {
        $outing = new Outing();
        $outing->setOrganizer($this->getUser());

        $statutRepository = $entityManager->getRepository(Status::class);


        $establishementUser = $this->getUser()->getEstablishment();
        $outing->setEstablishment($establishementUser);


        $outingForm = $this->createForm(OutingType::class, $outing);

        $outingForm->handleRequest($request);

        if ($outingForm->isSubmitted() && $outingForm->isValid()) {
            if ($outingForm->getClickedButton() && 'save' === $outingForm->getClickedButton()->getName()) {
                $statutCreated = $statutRepository->findOneBy(['nameTech' => 'draft']);

                $outing->setStatus($statutCreated);
                $entityManager->persist($outing);
                $entityManager->flush();

                return $this->redirectToRoute('home');
            } else {
                if ($outingForm->getClickedButton() && 'createOuting' === $outingForm->getClickedButton()->getName()) {
                    $statutCreated = $statutRepository->findOneBy(['nameTech' => 'published']);

                    $outing->setStatus($statutCreated);
                    $entityManager->persist($outing);
                    $entityManager->flush();

                    return $this->redirectToRoute('home');
                }
            }

        }

        return $this->render(
            'outing/create.html.twig',
            ['outingFormView' => $outingForm->createView()]
        );

    }

    /**
     * @Route("/delete/{id}",name="delete")
     */
    public function annuler(Outing $outing, EntityManagerInterface $entityManager, Request $request)
    {
        $statutRepository = $entityManager->getRepository(Status::class);
        $statutCanceled = $statutRepository->findOneBy(['nameTech' => 'canceled']);

        $motif = $request->request->get('motif');
        $outing->setStatus($statutCanceled);

        $currentInfos = $outing->getInfoOuting();
        $newInfos = "Sortie annulée pour la raison suivante : \n".$motif."\nAncienne description :\n".$currentInfos;
        $outing->setInfoOuting($newInfos);

        $entityManager->persist($outing);
        $entityManager->flush();


        return $this->redirectToRoute('outing_home');
    }
}

