<?php

namespace Eco\EcoCli;

use Symfony\Component\Console\Question\ChoiceQuestion;

class KeyChoiceQuestion extends ChoiceQuestion
{
    /**
     * {@inheritdoc}
     */
    protected function isAssoc($array)
    {
        return true;
    }
}
