<?php

namespace App\Controller\Admin;

use App\Entity\ExcuseTone;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ExcuseToneCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ExcuseTone::class;
    }
}
