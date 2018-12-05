<?php

namespace App\EventSubscriber;

use App\Events;
use App\Mailer\UserNotificationMailer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Notifies user.
 */
class UserNotificationSubscriber implements EventSubscriberInterface
{
    private $mailer;

    public function __construct(UserNotificationMailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::USER_REGISTERED => 'onUserRegistered',
        ];
    }

    public function onUserRegistered(GenericEvent $event)
    {
        $this->mailer->sendUserEmailValidationLink($event->getSubject());
    }
}
