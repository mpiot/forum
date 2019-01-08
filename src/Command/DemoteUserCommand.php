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

namespace App\Command;

use App\Service\UserManager;
use Symfony\Component\Console\Style\SymfonyStyle;

class DemoteUserCommand extends RoleCommand
{
    // to make your command lazily loaded, configure the $defaultName static property,
    // so it will be instantiated only when the command is actually called.
    protected static $defaultName = 'app:user-demote';

    protected function configure()
    {
        parent::configure();
        $this
            ->setDescription('Remove a role to specified user');
    }

    protected function executeRoleCommand(UserManager $userManager, SymfonyStyle $io, $email, $role)
    {
        if ($userManager->removeRole($email, $role)) {
            $io->success(sprintf('Role "%s" has been removed to user "%s". This change will not apply until the user logs out and back in again.', $role, $email));
        } else {
            $io->error(sprintf('User "%s" didn\'t have "%s" role.', $email, $role));
        }
    }
}
