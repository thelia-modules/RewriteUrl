<?php

namespace RewriteUrl\EventListeners;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Thelia\Model\ConfigQuery;


class RequestListener implements EventSubscriberInterface
{

    // permanently redirect request if url contain $regexToMatch string

    public function redirect(GetResponseEvent $event)
    {
        if (ConfigQuery::isRedirectionEnable())
        {
            $request = $event->getRequest();
            $fullPath = $request->getUri();
            $regexToMatch = "^\/index.php^";

            if (preg_match($regexToMatch, $fullPath)) 
            {
                $newPath = preg_replace($regexToMatch, "", $fullPath) ;
                $event->setResponse(new RedirectResponse(
                    $newPath,
                    301
                ));                
            }
        }
    }

    // event to listen to
    public static function getSubscribedEvents()
    {       
        return 
        [
            KernelEvents::REQUEST => ["redirect"],
        ];    
    }

}