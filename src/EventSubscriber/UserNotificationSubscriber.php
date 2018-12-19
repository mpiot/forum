<?php

/*
 * Copyright 2018 Mathieu Piot.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

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
            Events::USER_RESET => 'onUserResetPassword'
        ];
    }

    public function onUserRegistered(GenericEvent $event)
    {
        $this->mailer->sendUserEmailValidationLink($event->getSubject());
    }

    public function onUserResetPassword(GenericEvent $event)
    {
        $this->mailer->sendUserResetPasswordLink($event->getSubject());
    }
}
