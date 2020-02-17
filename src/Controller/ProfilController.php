<?php

namespace App\Controller;

use App\Entity\Establishment;
use App\Entity\User;
use App\Form\CSVType;
use App\Form\MyUserType;
use App\Form\PasswordType;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\UploadPP;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/profil", name ="profil_")
 */
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


            $this->redirectToRoute('profil_myprofil');

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
     * @Route("/{id}", name="byid", requirements={"id" : "\d+"})
     */
    public function profil(User $user)
    {
        return $this->render('profil/profils.html.twig', compact('user'));
    }



    /**
     * @Route("/csvadd", name="csvadd")
     */
    public function csvadd(Request $request, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $encoder)
    {
        $csvForm = $this->createForm(CSVType::class);

        $csvForm->handleRequest($request);

        if ($csvForm->isSubmitted() && $csvForm->isValid()) {

            $file = $csvForm['csv_file']->getData()->openFile();

            $reader = Reader::createFromFileObject($file)
                ->setHeaderOffset(0);

            $estabishmentRepository = $entityManager->getRepository(Establishment::class);

            $correctData = true;
            foreach ($reader as $record) {

                if(!isset($record['password'])
                    || !isset($record['username'])
                    || !isset($record['firstname'])
                    || !isset($record['name'])
                    || !isset($record['phone'])
                    || !isset($record['mail'])
                    || !isset($record['administrator'])
                    || !isset($record['active'])
                    || !isset($record['establishment'])
                ){
                    $correctData = false;
                    break;
                }

                $user = new User();
                $password = $record['password'];
                $encodedPassword = $encoder->encodePassword($user, $password);
                $user->setUsername($record['username'])
                    ->setPassword($encodedPassword)
                    ->setFirstname($record['firstname'])
                    ->setName($record['name'])
                    ->setPhone($record['phone'])
                    ->setMail($record['mail'])
                    ->setAdministrator($record['administrator'] == 1)
                    ->setActive($record['active'] ==1)
                    ->setEstablishment($estabishmentRepository->findByName($record['establishment'])[0]);
                $entityManager->persist($user);
            }

            if ($correctData){
                $entityManager->flush();

                $this->addFlash('success', 'Utilisateurs intégrés avec succès');
                return $this->redirectToRoute('outing_home');
            } else {
                $this->addFlash('warning', 'données incorrectes, veuillez vérifier votre fichier');
            }

        }


        return $this->render(
            'profil/csvusersadd.html.twig',
            [
                'csvFormView' => $csvForm->createView(),
            ]
        );
    }

}