<?php

namespace Validators;

use Src\Validator\AbstractValidator;

class NumValidator extends AbstractValidator
{
    protected string $message = 'Поле :field должно содержать только цифры';

    public function rule(): bool
    {
        $value = $this->value;
        return is_numeric($value) && $value >= 0;
    }
}
