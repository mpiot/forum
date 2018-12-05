<?php

namespace App\Mailer;

use Doctrine\ORM\EntityManagerInterface;

abstract class Mailer {
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
