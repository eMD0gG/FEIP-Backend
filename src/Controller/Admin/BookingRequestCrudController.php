<?php

namespace App\Controller\Admin;

use App\Entity\BookingRequest;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;

class BookingRequestCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return BookingRequest::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('comment', 'Комментарий'),
            AssociationField::new('user', 'Пользователь'),
            AssociationField::new('house', 'Дом'),
            ChoiceField::new('status', 'Статус')
                ->setChoices([
                    'Ожидает' => 'pending',
                    'Подтверждено' => 'confirmed',
                    'Отменено' => 'cancelled',
                ]),
        ];
    }
}
