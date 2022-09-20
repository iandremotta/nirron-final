<?php

namespace App\Form;

use App\Entity\Solicitacao;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\Blank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Vich\UploaderBundle\Form\Type\VichImageType;


class SolicitacaoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'titulo',
                null,
                [
                    'attr'  =>  array(
                        'class' => 'form-control',
                        'style' => 'margin:5px 0;'
                    ),
                    'invalid_message' => 'O campo não pode estar vazio.',
                ]
            )
            ->add('empresa', ChoiceType::class, [
                'choices'  => [
                    'Assessoria' => 'assessoria',
                    'Logística' => 'logistica'
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('notaFiscal', TextType::class, [
                'attr'  =>  array(
                    'class' => 'form-control',
                    'style' => 'margin:5px 0;'
                ),
                'required' => false,
            ])
            ->add('valor', null, [
                'attr'  =>  array(
                    'class' => 'form-control',
                    'style' => 'margin:5px 0;'
                ),
                'invalid_message' => 'O campo não pode estar vazio.',
            ])
            ->add('tipo', ChoiceType::class, [
                'choices'  => [
                    'Administrativo' => 1,
                    'Operacional' => 2,
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('vencimento', BirthdayType::class, [
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy',
                'html5' => false,
                'attr' => ['data-mask' => '99/99/9999', 'class' => 'form-control'],
                'invalid_message' => 'O campo não pode estar vazio.',
            ])
            ->add('justificativa', TextareaType::class, [
                'required' => true,
                'invalid_message' => 'O campo não pode estar vazio.',
                'attr' => ['class' => 'form-control', 'rows' => 5],
            ])
            ->add('imageFile', VichImageType::class, [
                'required' => true,
                'invalid_message' => 'Você não inseriu nenhum boleto.',
                'allow_delete' => false,
                'download_link' => false,
                'label' => "Boleto",
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Solicitacao::class,
        ]);
    }
}
