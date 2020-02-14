<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\MyUserType;
use App\Form\PasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\UploadPP;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class ProfilController extends AbstractController
{

    /**
     * @Route("/myprofil", name ="myprofil")
     */

    public function myprofil(
        EntityManagerInterface $entityManager,
        EncoderFactoryInterface $encoderFactory,
        Request $request, UploadPP $uploadPP
    )
    {
        $user = $this->getUser();

        $encoder = $encoderFactory->getEncoder($user);


        $newp=$request->request->get('my_user')['newpassword']['first'];

        dump($request);

        // Verification of the password to validate the change.
        $validPassword = $encoder->isPasswordValid(
            $user->getPassword(), // the encoded password
            $request->request->get('password'),       // the submitted password

            $user->getSalt()
        );


        $profilForm = $this->createForm(MyUserType::class, $user);
        $profilForm->handleRequest($request);


        if ($profilForm->isSubmitted() && $profilForm->isValid() && $validPassword) {
            if (!empty($newp)){
                $user->setPassword($encoder->encodePassword($newp, $user->getSalt()));
            }
            $this->addFlash('success', 'Profil modifié !');
            $picture = $request->files->get('my_user')['picture'];
            if($picture){
                $pictureName = $uploadPP->upload($picture);
                $user->setPicture($pictureName);
            }
            $entityManager->persist($user);
            $entityManager->flush();


            $this->redirectToRoute('myprofil');

        } else if ($profilForm->isSubmitted() && !$validPassword) {
            $this->addFlash('error', 'Mot de passe erroné !');
        }


        $profilFormView = $profilForm->createView();


        return $this->render(
            'main/myprofil.html.twig',
            compact('profilFormView', $user)
        );
    }

    /**
     * @Route("/profil/{id}", name="profil")
     */
    public function profil(User $user)
    {
        return $this->render('profil/profils.html.twig', compact('user'));
    }


}