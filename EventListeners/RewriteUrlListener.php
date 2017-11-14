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

namespace RewriteUrl\EventListeners;

use RewriteUrl\Event\RewriteUrlEvent;
use RewriteUrl\Event\RewriteUrlEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Model\RewritingUrlQuery;

/**
 * Class RewriteUrlListener
 * @package RewriteUrl\EventListeners
 * @author Vincent Lopes <vlopes@openstudio.fr>
 */
class RewriteUrlListener implements EventSubscriberInterface
{
    protected $dispatcher;

    /**
     * RewriteUrlListener constructor.
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            RewriteUrlEvents::REWRITEURL_DELETE =>['deleteRewrite'],
            RewriteUrlEvents::REWRITEURL_UPDATE =>['updateRewrite'],
            RewriteUrlEvents::REWRITEURL_ADD    =>['addRewrite'],
            RewriteUrlEvents::REWRITEURL_SET_DEFAULT =>['setDefaultRewrite']
        ];
    }

    /**
     * @param RewriteUrlEvent $event
     */
    public function deleteRewrite(RewriteUrlEvent $event)
    {
        $rewritingUrl = $event->getRewritingUrl();

        $newDefault = null;

        // test if default url
        if ($event->getRewritingUrl()->getRedirected() === null) {
            // add new default url
            if (null !== $newDefault = RewritingUrlQuery::create()->findOneByRedirected($rewritingUrl->getId())) {
                $this->dispatcher->dispatch(
                    RewriteUrlEvents::REWRITEURL_UPDATE,
                    new RewriteUrlEvent(
                        $newDefault->setRedirected(null)
                    )
                );
            }
        }

        $isRedirection = RewritingUrlQuery::create()->findByRedirected($rewritingUrl->getId());

        //Update urls who redirected to deleted URL
        /** @var \Thelia\Model\RewritingUrl $redirected */
        foreach ($isRedirection as $redirected) {
            $this->dispatcher->dispatch(
                RewriteUrlEvents::REWRITEURL_UPDATE,
                new RewriteUrlEvent(
                    $redirected->setRedirected(
                        ($newDefault !== null) ? $newDefault->getId() : $rewritingUrl->getRedirected()
                    )
                )
            );
        }

        $rewritingUrl->delete();
    }

    /**
     * @param RewriteUrlEvent $event
     * @throws \Exception
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function addRewrite(RewriteUrlEvent $event)
    {
        $rewritingUrl = $event->getRewritingUrl();
        $rewritingUrl->save();

        if ($rewritingUrl->getRedirected() === null) {
            //check if the new redirect is set to default if yes redirect all to the new one
            RewritingUrlQuery::create()
                ->filterByView($rewritingUrl->getView())
                ->filterByViewId($rewritingUrl->getViewId())
                ->filterByViewLocale($rewritingUrl->getViewLocale())
                ->update(array(
                    "Redirected" => $rewritingUrl->getId()
                ));

            //Re set new url to default
            $rewritingDefault = RewritingUrlQuery::create()->findOneById($rewritingUrl->getId());
            $rewritingDefault->setRedirected(null);
            $rewritingDefault->save();
        } else {
            $redirectType = $event->getRedirectType();
            if ($redirectType !== null) {
                $redirectType->setId($rewritingUrl->getId());
                $redirectType->save();
            }
        }
    }

    /**
     * @param RewriteUrlEvent $event
     * @throws \Exception
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function setDefaultRewrite(RewriteUrlEvent $event)
    {
        $rewritingUrl = $event->getRewritingUrl();

        //redirect all url to the new one
        RewritingUrlQuery::create()
            ->filterByView($rewritingUrl->getView())
            ->filterByViewId($rewritingUrl->getViewId())
            ->filterByViewLocale($rewritingUrl->getViewLocale())
            ->update(array(
                "Redirected" => $rewritingUrl->getId()
            ));

        //Re set new url to default
        $rewritingUrl->setRedirected(null);
        $rewritingUrl->save();
    }

    /**
     * @param RewriteUrlEvent $event
     * @throws \Exception
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function updateRewrite(RewriteUrlEvent $event)
    {
        $event->getRewritingUrl()->save();
        $redirectType = $event->getRedirectType();
        if ($redirectType !== null) {
            $redirectType->save();
        }
    }
}
