<?php

namespace App\Form;

use App\Entity\ClassicExcuse;
use App\Entity\EmergencyExcuse;
use App\Entity\Excuse;
use App\Entity\ExcuseCategory;
use App\Entity\ExcuseContext;
use App\Entity\ExcuseTone;
use App\Entity\ProfessionalExcuse;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExcuseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu',
            ])
            ->add('urgencyLevel', IntegerType::class, [
                'label' => 'Niveau d\'urgence',
                'attr' => ['min' => 1, 'max' => 10],
            ])
            ->add('category', EntityType::class, [
                'class' => ExcuseCategory::class,
                'choice_label' => 'name',
                'label' => 'Catégorie',
            ])
            ->add('context', EntityType::class, [
                'class' => ExcuseContext::class,
                'choice_label' => 'name',
                'label' => 'Contexte',
            ])
            ->add('tone', EntityType::class, [
                'class' => ExcuseTone::class,
                'choice_label' => 'name',
                'label' => 'Ton',
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            $data = $event->getData();
            if ($data instanceof Excuse) {
                $this->addTypeSpecificFields($event->getForm(), $data);
            }
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($options): void {
            $form = $event->getForm();
            $type = (string) $options['excuse_type'];

            // During submission we rely on the expected type from route context.
            $this->addTypeSpecificFieldsByType($form, $type);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Excuse::class,
            'excuse_type' => '',
        ]);

        $resolver->setAllowedTypes('excuse_type', 'string');
    }

    private function addTypeSpecificFieldsByType($form, string $type): void
    {
        if ('classic' === $type) {
            $form
                ->add('estimatedDelay', IntegerType::class, [
                    'label' => 'Retard estimé (minutes)',
                    'required' => false,
                ])
                ->add('isReusable', CheckboxType::class, [
                    'label' => 'Réutilisable',
                    'required' => false,
                ]);

            return;
        }

        if ('emergency' === $type) {
            $form
                ->add('emergencyLevel', IntegerType::class, [
                    'label' => 'Niveau d\'urgence spécifique',
                    'attr' => ['min' => 1, 'max' => 5],
                ])
                ->add('requiresProof', CheckboxType::class, [
                    'label' => 'Preuve requise',
                    'required' => false,
                ]);

            return;
        }

        if ('professional' === $type) {
            $form
                ->add('targetRecipient', TextType::class, [
                    'label' => 'Destinataire cible',
                    'required' => false,
                ])
                ->add('professionalTone', TextType::class, [
                    'label' => 'Ton professionnel',
                    'required' => false,
                ]);
        }
    }

    private function addTypeSpecificFields($form, Excuse $excuse): void
    {
        if ($excuse instanceof ClassicExcuse) {
            $this->addTypeSpecificFieldsByType($form, 'classic');

            return;
        }

        if ($excuse instanceof EmergencyExcuse) {
            $this->addTypeSpecificFieldsByType($form, 'emergency');

            return;
        }

        if ($excuse instanceof ProfessionalExcuse) {
            $this->addTypeSpecificFieldsByType($form, 'professional');
        }
    }
}

