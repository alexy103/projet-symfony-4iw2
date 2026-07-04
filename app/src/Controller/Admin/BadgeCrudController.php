<?php

namespace App\Controller\Admin;

use App\Entity\Badge;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class BadgeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Badge::class;
    }
}
