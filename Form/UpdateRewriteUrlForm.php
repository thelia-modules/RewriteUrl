<?php

namespace RewriteUrl\Form;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Thelia\Form\BaseForm;

class UpdateRewriteUrlForm extends BaseForm
{
    protected function buildForm(): void
    {
        $this->formBuilder
            ->add('rewritten_url', TextType::class)
        ;
    }
}