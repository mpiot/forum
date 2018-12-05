<?php

namespace App\Mailer;

use App\Entity\User;

class UserNotificationMailer extends Mailer
{
    public function sendUserEmailValidationLink(User $user): int
    {
        $subject = 'Confirm your email';
        $textContent = $this->templating->render('mail/user_email_validation.txt.twig', [
            'user' => $user,
        ]);
        $htmlContent = $this->templating->render('mail/user_email_validation.html.twig', [
            'user' => $user,
        ]);

        return $this->sendEMailMessage($user->getEmail(), $subject, $textContent, $htmlContent);
    }
}
