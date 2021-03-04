<?php
/*************************************************************************************/
/*      This file is part of the RewriteUrl module for Thelia.                       */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : dev@thelia.net                                                       */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace RewriteUrl\Form;

use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Thelia\Form\BaseForm;

/**
 * Class ReassignForm
 * @package RewriteUrl\Form
 * @author Vincent Lopes <vlopes@openstudio.fr>
 */
class ReassignForm extends BaseForm
{
    /**
     * @return string
     */
    public static function getName()
    {
        return "rewriteurl_reassign_form";
    }

    protected function buildForm()
    {
        $this->formBuilder
            ->add(
                'rewrite-id',
                IntegerType::class,
                array(
                    'required'      => true
                )
            )
            ->add(
                'select-reassign',
                TextType::class,
                array(
                    'constraints'   => array(new NotBlank()),
                    'required'      => true
                )
            )
            ->add(
                'all',
                IntegerType::class,
                array(
                    'required' => true
                )
            )
        ;
    }
}
