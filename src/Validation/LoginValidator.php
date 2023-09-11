<?php

declare(strict_types=1);

namespace App\Validation;

use App\Dto\LoginDto;
use App\Repository\UserRepository;
use Doctrine\ORM\NonUniqueResultException;
use Psr\Log\LoggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LoginValidator extends AbstractValidator
{
    public function __construct(
        ValidatorInterface $validator,
        object $object,
        private UserRepository $userRepository,
        private LoggerInterface $logger,
        private UserPasswordHasherInterface $passwordHasher
    ){
        parent::__construct($validator, $object);
    }

    public function validate(object $object): void
    {
        if ($object instanceof LoginDto) {
            if (!$object->getUser() || !$object->getPassword()) {
                $this->addError('The submitted inquiry does not contain all the required data');
                $this->setCode(422);
                return;
            }

            try {
                $user = $this->userRepository->findUserByUsernameOrEmail($object->getUser());
            }
            catch (NonUniqueResultException $exception){
                $this->logger->error('An error occurred while searching for the user: ' . $exception->getMessage());
                $this->addError('An unexpected error occurred');
                $this->setCode(500);
                return;
            }

            if (!$user | !$this->passwordHasher->isPasswordValid($user, $object->getPassword())) {
                $this->addError('The login or password provided is incorrect');
                $this->setCode(401);
            }
        }
    }
}