<?php

namespace TS\PHPValidator;

use phpDocumentor\Reflection\Types\Null_;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class ValidatorTwig
 * @package TS\PHPValidator
 * Requires package "twig/twig": "^3.1"
 */
class ValidatorTwig extends AbstractExtension
{
    /**
     * @var Validator Instance
     */
    protected $validator;

    /**
     * ValidatorTwig constructor.
     * @param Validator $validator
     */
    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Return Twig Functions
     * @return array|TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('has_errors', [$this, 'hasErrors']),
            new TwigFunction('has_error', [$this, 'hasError']),
            new TwigFunction('get_errors', [$this, 'getErrors']),
            new TwigFunction('get_error', [$this, 'getError']),
            new TwigFunction('get_value', [$this, 'getValue']),

        ];
    }

    /**
     * Check whether there is any errors
     * @return bool
     */
    public function hasErrors(): bool
    {
        return $this->validator->failed();
    }

    /**
     * Check whether a field has error
     * @param $key
     * @return bool
     */
    public function hasError($key): bool
    {
        return isset($this->validator->getErrors()[$key]);
    }

    /**
     * Get all error messages
     */
    public function getErrors()
    {
        return $this->validator->getErrors();
    }

    /**
     * Get error by field name
     * @param $key
     * @return false|mixed
     */
    public function getError($key, $toString = true)
    {
        return $toString ? implode(', ', $this->validator->getErrors()[$key]) : $this->validator->getErrors()[$key];
    }

    /**
     * Get Specific value by key
     * @param $key
     * @param null $default
     */
    public function getValue($key, $default = null)
    {
        return $this->validator->getValue($key, $default);
    }
}
