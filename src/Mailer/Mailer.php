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

namespace App\Mailer;

use Doctrine\ORM\EntityManagerInterface;

abstract class Mailer
{
    protected $mailer;
    protected $entityManager;
    protected $parameterManipulator;
    protected $senderMail = 'no-reply@forum.localhost';
    protected $templating;

    public function __construct(\Swift_Mailer $mailer, EntityManagerInterface $entityManager, \Twig_Environment $templating)
    {
        $this->mailer = $mailer;
        $this->entityManager = $entityManager;
        $this->templating = $templating;
    }

    protected function sendEMailMessage(string $to, string $subject, string $textBody, string $htmlBody, $from = null): int
    {
        if (null === $from) {
            $from = $this->senderMail;
        }

        $message = (new \Swift_Message())
            ->setFrom($from)
            ->setTo($to)
            ->setSubject($subject)
            ->setContentType('text/plain; charset=UTF-8')
            ->setBody($textBody, 'text/plain')
            ->addPart($htmlBody, 'text/html')
        ;

        return $this->mailer->send($message);
    }
}
