<?php

namespace AppBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

use AppBundle\Controller\Response;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        $subscribedEvents = [
            KernelEvents::EXCEPTION => [
                ['processException', 10],
            ],
        ];

        return $subscribedEvents;
    }

    public function __construct(
        $logger
    )
    {
        $this->logger = $logger;
    }

    public function processException(
        GetResponseForExceptionEvent $event
    )
    {
        $e = $event->getException();

        /**
         * Handles controller's exceptions
         * - Sets error response in event and sends it back
         */

        $response = null;

        switch (get_class($e))
        {
            case 'AppBundle\Exception\BadRequestException':
                $response = new Response\Error($e, Response\Error::HTTP_BAD_REQUEST);
                $this->logger->debug($e);
                break;

            case 'AppBundle\Exception\NotFoundException':
                $response = new Response\Error($e, Response\Error::HTTP_NOT_FOUND);
                $this->logger->debug($e);
                break;

            // @codeCoverageIgnoreStart
            default:
                // In other unkown cases, just log the error and let it bubble up
                $this->logger->error($e);
                break;
            // @codeCoverageIgnoreEnd
        }

        if ($response)
        {
            $event->setResponse($response);
        }
    }
}
