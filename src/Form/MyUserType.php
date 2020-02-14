<?php

namespace App\Form;


use App\Entity\Establishment;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class MyUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'picture',
                FileType::class,
                [
                    'label'=> 'Photo de profil',
                    'mapped'=> false,
                    'required'=> false,
                    'constraints'=>[
                        New File([
                            'maxSize'=> '2048k',
                            'mimeTypes'=> [
                                'image/jpeg',
                                'image/png',
                                'image/gif'
                            ],
                            'mimeTypesMessage' => 'Veuillez choisir une image ou une gif !'
                        ])
                    ],
                ]
            )
            ->add(
                'username',
                TextType::class,
                [
                    'label' => 'Pseudo'
                ]
            )
            ->add(
                'name',
                TextType::class,
                [
                    'label' => 'Nom'
                ]
            )
            ->add(
                'firstname',
                TextType::class,
                [
                    'label' => 'Prénom'
                ]
            )
            ->add(
                'phone',
                TextType::class,
                [
                    'label' => 'Téléphone'
                ]
            )
            ->add(
                'mail',
                EmailType::class,
                [
                    'label' => 'Email'
                ]
            )
            ->add(
                'establishment',
                EntityType::class,
                [
                    'label' => 'Ville de rattachement',
                    'placeholder' => 'Choisir une ville',
                    'class' => Establishment::class,
                    'choice_label' => 'name',
                    'required' => false
                ]


            )
            ->add(
                'newpassword',
                RepeatedType::class,
                [
                    'type' => PasswordType::class,
                    'first_options' => ['label' => 'Nouveau mot de passe', 'attr' => ['oninput' => 'required()', 'onblur' => 'noRequired()'], 'required' => false],
                    'second_options' => ['label' => 'Confirmation', 'required' => false],
                    'invalid_message' => 'Les mots de passe doivent correspondre',
                    'mapped' => false,
                    'required' => false,
                    'constraints' => [

                        new Length(
                            [
                                'min' => 6,
                                'minMessage' => 'Le mot de passe ne peux pas avoir moins de  {{ limit }} charactères',
                                // max length allowed by Symfony for security reasons
                                'max' => 1060,
                            ]
                        ),
                    ],
                ]

            )
//            ->addEventListener(
//                FormEvents::PRE_SUBMIT,
//                function (FormEvent $event) {
//                    $user = $event->getData();
//                    $form = $event->getForm();
//
//
//                    if ($user->get || null === $user->getId())
//
//
//                }
//            );
//    }


            ->add(
                'enregistrer',
                SubmitType::class,
                [
                    'label' => 'Enregistrer'
                ])
        ;


    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
