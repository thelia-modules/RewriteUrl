<?php

namespace RewriteUrl\Form;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;

class ResearchForm extends BaseForm
{
    protected function buildForm(): void
    {
        $this->formBuilder
            ->add('user_agent', TextType::class, [
                'required' => false,
                'label' => Translator::getInstance()?->trans('User Agent')
            ])
            ->add('url_source', TextType::class, [
                'required' => false,
                'label' => Translator::getInstance()?->trans('URL Source')
            ])
        ;
    }
}