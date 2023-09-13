<?php

declare(strict_types=1);

namespace App\Validation;

use App\Dto\PostDto;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PostValidator extends AbstractValidator
{
    public function __construct(
        ValidatorInterface $validator,
    ) {
        parent::__construct($validator);
    }

    public function validate(object $object): void
    {
        if (!($object instanceof PostDto)) {
            return;
        }

        if (strlen($object->getTitle()) > 120) {
            $this->addError('The given title is too long (max 120 characters)');
            $this->setCode(400);
            return;
        }

        if (strlen($object->getContent()) > 2500) {
            $this->addError('The given content is too long (max 120 characters)');
            $this->setCode(400);
        }
    }
}
