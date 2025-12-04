<?php

namespace App\Controller\Admin;

use App\Entity\House;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;


class HouseCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return House::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('address', 'Адрес'),
            NumberField::new('area', 'Площадь'),
            IntegerField::new('price', 'Цена'),
            IntegerField::new('bedrooms', 'Спальни'),
            IntegerField::new('distanceToSea', 'Расстояние до моря'),
            BooleanField::new('hasShower', 'Душ'),
            BooleanField::new('hasBathroom', 'Ванная'),
            AssociationField::new('bookingRequests', 'Бронирования'),
        ];
    }
}
