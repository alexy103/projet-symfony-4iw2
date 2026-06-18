<?php

namespace App\Form;

use App\Entity\Badge;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class BadgeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
            ])
            ->add('icon', TextType::class, [
                'label' => 'Icône (emoji)',
                'required' => false,
                'help' => 'Optionnel. Utilisé si aucune image n\'est importée.',
            ])
            ->add('iconFile', FileType::class, [
                'label' => 'Image (optionnel)',
                'mapped' => false,
                'required' => false,
                'help' => 'PNG, JPG, WEBP, SVG ou GIF (2 Mo max). Remplace l\'emoji.',
                'constraints' => [
                    new File(
                        maxSize: '2M',
                        mimeTypes: [
                            'image/png',
                            'image/jpeg',
                            'image/webp',
                            'image/svg+xml',
                            'image/gif',
                        ],
                        mimeTypesMessage: 'Merci d\'importer une image valide (PNG, JPG, WEBP, SVG, GIF).',
                    ),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Badge::class,
        ]);
    }
}
