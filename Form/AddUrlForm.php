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
    public function getName()
    {
        return "rewriteurl_add_form";
    }

    public function buildForm()
    {
        $this->formBuilder
            ->add(
                'view',
                'text',
                array(
                    'constraints'   => array(new NotBlank()),
                    'required'      => true
                )
            )
            ->add(
                'view-id',
                'text',
                array(
                    'constraints'   => array(new NotBlank()),
                    'required'      => true
                )
            )
            ->add(
                'url',
                'text',
                array(
                    'constraints'  => array(new NotBlank()),
                    'required'     => true
                )
            )
            ->add(
                'default',
                'text',
                array(
                    'constraints'  => array(new NotBlank()),
                    'required'     => true
                )
            )
            ->add(
                'locale',
                'text',
                array(
                    'constraints'  => array(new NotBlank()),
                    'required'     => true
                )
            )
            ->add(
                'httpcode',
                'text',
                array(
                    'constraints'  => array(new NotBlank()),
                    'required'     => true
                )
            )
        ;
    }
}
