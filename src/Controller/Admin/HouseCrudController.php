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
            IdField::new('id')->onlyOnIndex(),
            
            TextField::new('address', 'Адрес'),
            NumberField::new('area', 'Площадь (м²)')
                ->setNumDecimals(1),
            IntegerField::new('price', 'Цена (руб)')
                ->setTextAlign('right'),
            
            IntegerField::new('bedrooms', 'Спальни'),
            IntegerField::new('distanceToSea', 'Расстояние до моря (м)'),
            
            BooleanField::new('hasShower', 'Есть душ')
                ->renderAsSwitch(false),
            BooleanField::new('hasBathroom', 'Есть ванная')
                ->renderAsSwitch(false),
            
            CollectionField::new('bookingRequests', 'Бронирования')
                ->onlyOnDetail()
                ->setTemplatePath('admin/field/booking_requests.html.twig'),
            
            AssociationField::new('bookingRequests', 'Броней')
                ->onlyOnIndex()
                ->formatValue(function ($value, $entity) {
                    return count($entity->getBookingRequests());
                }),
        ];
    }
}
