<?php

namespace App\Controller\Admin;

use App\Entity\ExcuseContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ExcuseContextCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ExcuseContext::class;
    }
}
