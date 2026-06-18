<?php

namespace App\Controller\Admin;

use App\Entity\Tag;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class TagCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Tag::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name', 'Nom');
        yield ColorField::new('color', 'Couleur');
        yield AssociationField::new('owner', 'Propriétaire')
            ->setRequired(false)
            ->setHelp('Laisser vide pour un tag global.')
            ->setFormTypeOption('choice_label', 'email')
            ->formatValue(static fn ($value, $entity) => $entity->getOwner()?->getEmail() ?? 'Global');
    }
}
