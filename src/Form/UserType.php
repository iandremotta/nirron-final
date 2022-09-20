<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\CallbackTransformer;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', null, ['attr'  =>  array(
                'class' => 'form-control',
                'style' => 'margin:5px 0;'
            ),])
            ->add('nome', null, ['attr'  =>  array(
                'class' => 'form-control',
                'style' => 'margin:5px 0;'
            ),])
            ->add('Roles', ChoiceType::class, [
                'required' => true,
                'multiple' => false,
                'expanded' => false,
                'choices'  => [
                    'Solicitante' => User::SOLICITANTE,
                    'Aprovador Administrativo' => User::APROVADOR_ADMINISTRATIVO,
                    'Aprovador Operacional' => User::APROVADOR_OPERACIONAL,
                    'Administrador Assessoria' => User::ADMINISTRADOR_ASSESSORIA,
                    'Administrador Logística' => User::ADMINISTRADOR_LOGISTICA,
                    'Financeiro Assessoria' => User::FINANCEIRO_ASSESSORIA,
                    'Financeiro Logística' => User::FINANCEIRO_LOGISTICA,
                    'Super Usuário' => User::SUPER_USUARIO,
                ],
                'attr'  =>  array(
                    'class' => 'form-control',
                    'style' => 'margin:5px 0;'
                )
            ])
            // ->add(
            //     'roles',
            //     ChoiceType::class,
            //     array(
            //         'attr'  =>  array(
            //             'class' => 'form-control',
            //             'style' => 'margin:5px 0;'
            //         ),
            //         'choices' =>
            //         array(
            //             'Super' => array(
            //                 'Sim' => 'ROLE_SUPER',
            //             ),
            //             'Usuário' => array(
            //                 'Sim' => 'ROLE_USER'
            //             ),
            //             'Financeiro' => array(
            //                 'Sim' => 'ROLE_FINANCEIRO'
            //             ),
            //             'Administrativo' => array(
            //                 'Sim' => 'ROLE_ADMINISTRATIVO'
            //             ),
            //         ),
            //         'multiple' => true,
            //         'required' => true,
            //         'label' => "Função"
            //     )
            // )
            // ->add('isActive', ChoiceType::class, [
            //     'choices'  => [
            //         'Ativo' => true,
            //         'Inativo' => false,
            //     ],
            //     'attr' => ['class' => 'form-control']
            // ])
            ->add('password', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password',  'class' => 'form-control'],
                // 'label' => false,
                'required' => false,
                'constraints' => [
                    // new NotBlank([
                    //     'message' => 'Please enter a password',
                    // ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'O campo senha precisa ter no mínimo {{ limit }} characters.',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ]);

        // Data transformer
        $builder->get('Roles')
            ->addModelTransformer(new CallbackTransformer(
                function ($rolesArray) {
                    // transform the array to a string
                    return count($rolesArray) ? $rolesArray[0] : null;
                },
                function ($rolesString) {
                    // transform the string back to an array
                    return [$rolesString];
                }
            ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
