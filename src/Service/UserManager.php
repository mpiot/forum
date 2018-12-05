<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserManager
{
    protected $entityManager;
    protected $userRepository;
    protected $passwordUpdater;
    protected $tokenGenerator;

    public function __construct(PasswordUpdater $passwordUpdater, EntityManagerInterface $em, UserRepository $userRepository, TokenGenerator $tokenGenerator)
    {
        $this->entityManager = $em;
        $this->userRepository = $userRepository;
        $this->passwordUpdater = $passwordUpdater;
        $this->tokenGenerator = $tokenGenerator;
    }

    public function createUser()
    {
        $user = new User();

        return $user;
    }

    public function deleteUser(User $user)
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }

    public function findUserBy(array $criteria)
    {
        return $this->userRepository->findOneBy($criteria);
    }

    public function findUserByEmail($email)
    {
        return $this->findUserBy(['email' => $email]);
    }

    public function findUserByConfirmationToken($token)
    {
        return $this->findUserBy(['confirmationToken' => $token]);
    }

    public function findUsers()
    {
        return $this->userRepository->findAll();
    }

    public function updateUser(User $user, $andFlush = true)
    {
        $this->updatePassword($user);
        $this->entityManager->persist($user);

        if ($andFlush) {
            $this->entityManager->flush();
        }
    }

    public function updatePassword(User $user)
    {
        $this->passwordUpdater->encodePassword($user);
    }

    public function generateToken(User $user)
    {
        $token = $this->tokenGenerator->generateToken();
        $user->setConfirmationToken($token);
    }

    public function removeToken(User $user)
    {
        $user->setConfirmationToken(null);
    }
}
