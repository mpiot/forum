<?php

namespace App\Security;

use App\Entity\Post;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class PostVoter extends Voter
{
    private $security;

    // List possible actions
    const UPDATE = 'UPDATE';
    const DELETE = 'DELETE';

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        // If the attribute isn't supported by this voter, return false
        if (!\in_array($attribute, [self::UPDATE, self::DELETE], true)) {
            return false;
        }

        // The voter is used only for Post object, else return false
        if (!$subject instanceof Post) {
            return false;
        }

        // Else the voter support the vote
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $post = $subject;

        switch ($attribute) {
            case self::UPDATE:
                return $this->canUpdate($post, $token);

                break;

            case self::DELETE:
                return $this->canDelete($post, $token);

                break;
        }

        return false;
    }

    private function canUpdate(Post $post, TokenInterface $token)
    {
        return $this->canDelete($post, $token);
    }

    private function canDelete(Post $post, TokenInterface $token)
    {
        if (!$token->getUser() instanceof User) {
            return false;
        }

        if ($post->getCreatedBy() === $token->getUser()->getEmail()) {
            return true;
        }

        if ($this->security->isGranted('ROLE_MODERATOR')) {
            return true;
        }

        return false;
    }
}
