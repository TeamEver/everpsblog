<?php

namespace PrestaShop\Module\Everpsblog\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class AuthorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nickhandle', TextType::class, ['required' => false, 'label' => 'Pseudo'])
            ->add('bio', TextType::class, ['required' => false, 'label' => 'Bio'])
        ;
    }
}
