<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class DeleteAccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->remove('plainPassword')
        ;
    }

    public function getParent()
    {
        return ChangePasswordType::class;
    }
}
