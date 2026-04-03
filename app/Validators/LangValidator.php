<?php

namespace Validators;

use Src\Validator\AbstractValidator;

class LangValidator extends AbstractValidator
{
    protected string $message = 'Поле :field должно использовать кириллицу';

    public function rule(): bool
    {
        $value = $this->value;
        $pattern = '/^[а-яёА-ЯЁ\-]+$/u';
        return (bool)preg_match($pattern, $value);
    }
}