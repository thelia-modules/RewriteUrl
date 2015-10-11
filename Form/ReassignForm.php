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
 * Class ReassignForm
 * @package RewriteUrl\Form
 * @author Vincent Lopes <vlopes@openstudio.fr>
 */
class ReassignForm extends BaseForm
{
    /**
     * @return string
     */
    public function getName()
    {
        return "rewriteurl_reassign_form";
    }

    protected function buildForm()
    {
        $this->formBuilder
            ->add(
                'rewrite-id',
                'integer',
                array(
                    'required'      => true
                )
            )
            ->add(
                'select-reassign',
                'text',
                array(
                    'constraints'   => array(new NotBlank()),
                    'required'      => true
                )
            )
            ->add(
                'all',
                'integer',
                array(
                    'required' => true
                )
            )
        ;
    }
}
