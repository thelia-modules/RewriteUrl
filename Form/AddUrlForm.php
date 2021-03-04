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

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Thelia\Form\BaseForm;

/**
 * Class AddUrlForm
 * @package RewriteUrl\Form
 * @author Vincent Lopes <vlopes@openstudio.fr>
 */
class AddUrlForm extends BaseForm
{
    /**
     * @return string
     */
    public static function getName()
    {
        return "rewriteurl_add_form";
    }

    public function buildForm()
    {
        $this->formBuilder
            ->add(
                'view',
                TextType::class,
                array(
                    'constraints'   => array(new NotBlank()),
                    'required'      => true
                )
            )
            ->add(
                'view-id',
                TextType::class,
                array(
                    'constraints'   => array(new NotBlank()),
                    'required'      => true
                )
            )
            ->add(
                'url',
                TextType::class,
                array(
                    'constraints'  => array(new NotBlank()),
                    'required'     => true
                )
            )
            ->add(
                'default',
                TextType::class,
                array(
                    'constraints'  => array(new NotBlank()),
                    'required'     => true
                )
            )
            ->add(
                'locale',
                TextType::class,
                array(
                    'constraints'  => array(new NotBlank()),
                    'required'     => true
                )
            )
            ->add(
                'httpcode',
                TextType::class,
                array(
                    'constraints'  => array(new NotBlank()),
                    'required'     => true
                )
            )
        ;
    }
}
