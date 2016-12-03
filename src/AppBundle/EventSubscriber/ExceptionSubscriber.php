<?php

namespace AppBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

use AppBundle\Controller\Response;

/**
 *
 */
class ExceptionSubscriber implements EventSubscriberInterface
{
    /**
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
           KernelEvents::EXCEPTION => [
               ["processException", 10],
           ],
        ];
    }

    /**
     * @param object $logger
     *
     * @return
     */
    public function __construct($logger)
    {
        $this->logger = $logger;
    }

    /**
     *
     * @param GetResponseForExceptionEvent $event
     *
     * @return
     */
    public function processException(GetResponseForExceptionEvent $event)
    {
        // ...
        $e = $event->getException();

        /**
         * Handles controller's exceptions
         * - Sets error response in event and sends it back
         */

        $response = null;

        switch (get_class($e)) {
            case "AppBundle\Exception\BadRequestException":
                $response = new Response\Error($e, Response\Error::HTTP_BAD_REQUEST);
                break;

            case "AppBundle\Exception\NotFoundException":
                $response = new Response\Error($e, Response\Error::HTTP_NOT_FOUND);
                break;

            default:
                break;
        }

        if ($response) {
            $event->setResponse($response);
        }

        /** --- */

        /**
         * In other unknown cases, just log it and let it bubble up as 5xx
         */
        $this->logger->error($e);
    }
}
