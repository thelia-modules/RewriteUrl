<?php

namespace RewriteUrl\EventListeners;

use RewriteUrl\RewriteUrl;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Thelia\Model\ConfigQuery;


class RequestListener implements EventSubscriberInterface
{
    public function redirect(GetResponseEvent $event)
    {
        $fullPath = $event->getRequest()->getUri();
        $regexToMatch = "^\/index.php^";

        /** permanently redirect request if url contain $regexToMatch string */

        if (RewriteUrl::getConfigValue("index_redirection_enable"))
        {
            if (preg_match($regexToMatch, $fullPath)) 
            {
                $newPath = preg_replace($regexToMatch, "", $fullPath) ;
                $event->setResponse(new RedirectResponse(
                    $newPath,
                    301
                ));                
            }
        }

        /** permanently redirect http to https protocol */

        if (RewriteUrl::getConfigValue("https_redirection_enable"))
        {
            if (!$event->getRequest()->isSecure()) 
            {
                $securePath = preg_replace("/^http:/i", "https:", $fullPath) ;
                $event->setResponse(new RedirectResponse(
                    $securePath,
                    301
                ));                
            }
        }
    }

    public static function getSubscribedEvents()
    {       
        return 
        [
            KernelEvents::REQUEST => ["redirect"],
        ];    
    }
}