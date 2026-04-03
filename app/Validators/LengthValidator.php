<?php

namespace Validators;

use Src\Validator\AbstractValidator;

class LengthValidator extends AbstractValidator
{
    protected string $message = 'Поле :field должно быть от :min до :max символов';

    public function rule(): bool
    {
        $value = $this->value;
        $min = (int)($this->args[0] ?? 0);
        $max = (int)($this->args[1] ?? PHP_INT_MAX);

        $length = mb_strlen($value, 'UTF-8');

        return $length >= $min && $length <= $max;
    }
}
