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

namespace App\Security;

use App\Entity\Thread;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class ThreadVoter extends Voter
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

        // The voter is used only for Thread object, else return false
        if (!$subject instanceof Thread) {
            return false;
        }

        // Else the voter support the vote
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $thread = $subject;

        switch ($attribute) {
            case self::UPDATE:
                return $this->canUpdate($thread, $token);

                break;

            case self::DELETE:
                return $this->canDelete($thread, $token);

                break;
        }

        return false;
    }

    private function canUpdate(Thread $thread, TokenInterface $token)
    {
        return $this->canDelete($thread, $token);
    }

    private function canDelete(Thread $thread, TokenInterface $token)
    {
        if (!$token->getUser() instanceof User) {
            return false;
        }

        if ($thread->getCreatedBy() === $token->getUser()) {
            return true;
        }

        if ($this->security->isGranted('ROLE_MODERATOR')) {
            return true;
        }

        return false;
    }
}
