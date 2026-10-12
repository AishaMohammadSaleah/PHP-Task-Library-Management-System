<?php

trait ValidatesInput
{
     private function requireNonEmpty(string $value, string $fieldName): void
    {
        if (trim($value) === '') {
            throw new InvalidArgumentException("$fieldName cannot be empty.");
        }
    }
}