<?php

namespace App\Controller\Admin;

use App\Entity\ExcuseCategory;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ExcuseCategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ExcuseCategory::class;
    }
}
