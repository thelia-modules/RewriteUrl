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

namespace RewriteUrl\Event;

use Thelia\Core\Event\ActionEvent;
use Thelia\Model\RewritingUrl;

/**
 * Class RewriteUrlEvent
 * @package RewriteUrl\Event
 * @author Vincent Lopes <vlopes@openstudio.fr>
 * @author Gilles Bourgeat <gbourgeat@openstudio.fr>
 */
class RewriteUrlEvent extends ActionEvent
{
    /** @var int|null */
    protected $id;

    /** @var string */
    protected $url;

    /** @var string */
    protected $view;

    /** @var string */
    protected $viewLocale;

    /** @var int|null */
    protected $redirected;

    /** @var RewritingUrl */
    private $rewritingUrl;

    /**
     * @param RewritingUrl $rewritingUrl
     */
    public function __construct(RewritingUrl $rewritingUrl)
    {
        $this->id = $rewritingUrl->getId();
        $this->url = $rewritingUrl->getUrl();
        $this->view = $rewritingUrl->getView();
        $this->viewLocale = $rewritingUrl->getViewLocale();
        $this->redirected = $rewritingUrl->getRedirected();
        $this->rewritingUrl = $rewritingUrl;
    }

    /**
     * @return RewritingUrl
     */
    public function getRewritingUrl()
    {
        return $this->rewritingUrl;
    }

    /**
     * @param int $id|null
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param array $parameters
     * @return $this
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param boolean $propagationStopped
     * @return $this
     */
    public function setPropagationStopped($propagationStopped)
    {
        $this->propagationStopped = boolval($propagationStopped);

        return $this;
    }

    /**
     * @return boolean
     */
    public function getPropagationStopped()
    {
        return $this->propagationStopped;
    }

    /**
     * @param null|int $redirected
     * @return $this
     */
    public function setRedirected($redirected)
    {
        $this->redirected = $redirected;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getRedirected()
    {
        return $this->redirected;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param $view
     * @return $this
     */
    public function setView($view)
    {
        $this->view = $view;

        return $this;
    }

    /**
     * @return null
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @param string $view_locale
     * @return $this
     */
    public function setViewLocale($view_locale)
    {
        $this->viewLocale = $view_locale;

        return $this;
    }

    /**
     * @return null
     */
    public function getViewLocale()
    {
        return $this->viewLocale;
    }
}
