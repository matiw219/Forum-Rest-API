<?php

declare(strict_types=1);

namespace App\Validation;

use App\Dto\RegistrationDto;
use App\Repository\UserRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationValidator extends AbstractValidator
{
    public function __construct(
        ValidatorInterface $validator,
        private UserRepository $userRepository
    ){
        parent::__construct($validator);
    }

    public function validate(object $object): void
    {
        if (!($object instanceof RegistrationDto)) {
            return;
        }

        $violations = ValidationHandler::getErrors($this->getValidator()->validate($object));

        if (!empty($violations)) {
            $this->addError($violations[0]);
            $this->setCode(400);
            return;
        }

        $user = $this->userRepository->findOneBy(['email' => $object->getEmail()]);

        if ($user) {
            $this->addError('A user with this email already exists');
            $this->setCode(400);
            return;
        }

        $user = $this->userRepository->findOneBy(['username' => $object->getUsername()]);

        if ($user) {
            $this->addError('A user with this username already exists');
            $this->setCode(400);
        }
    }
}