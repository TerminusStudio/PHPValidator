<?php

namespace TS\PHPValidator;

use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Exceptions\NestedValidationException;

class Validator
{
    /**
     * @var array values
     */
    protected $values = [];

    /**
     * @var array errors
     */
    protected $errors = [];

    /**
     * @var bool Use Session variable to store data
     * If set to true, use ValidationMiddleware to extract the error from the Session.
     */
    protected $useSession;

    /**
     * Validator constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->useSession = $config['useSession'] ?? false;
    }

    /**
     * @param Request|array|object $params
     * @param array $rules
     * @param mixed $default
     * @return $this
     * @throws \Exception
     */
    public function validate($params, array $rules, $default = null): Validator
    {
        if ($params instanceof Request) {
            return $this->validateRequest($params, $rules, $default);
        } elseif (is_array($params)) {
            return $this->validateArray($params, $rules, $default);
        } elseif (is_object($params)) {
            return $this->validateObject($params, $rules, $default);
        }

        throw new \Exception('Unknown type given for $params.');
    }

    /**
     * @param Request $request
     * @param array $rules
     * @param mixed $default
     * @return $this
     */
    public function validateRequest(Request $request, array $rules, $default = null)
    {
        $params = $request->getParsedBody() ?? [];

        //Call back validate function since params can be an object or array
        return $this->validate($params, $rules, $default);
    }

    /**
     * @param object $object
     * @param array $rules
     * @param mixed $default
     * @return $this
     */
    public function validateObject(object $object, array $rules, $default = null)
    {
        $params = get_object_vars($object);
        return $this->runValidation($params, $rules, $default);
    }

    /**
     * @param array $params
     * @param array $rules
     * @param mixed $default
     * @return $this
     */
    public function validateArray(array $params, array $rules, $default = null)
    {
        return $this->runValidation($params, $rules, $default);
    }

    /**
     * @param array $params
     * @param array $rules
     * @param mixed $default
     * @return Validator
     */
    protected function runValidation(array $params, array $rules, $default = null)
    {
        foreach ($rules as $field => $rule) {
            try {
                $param = isset($params[$field]) ? $params[$field] : $default;
                $this->values[$field] = $param;
                $rule->setName(ucfirst($field))->assert($param);
            } catch (NestedValidationException $e) {
                $this->errors[$field] = $e->getMessages();
            }
        }

        if ($this->failed() && $this->useSession) {
            $_SESSION['TS_PHPValidator_Errors'] = $this->errors;
            $_SESSION['TS_PHPValidator_Values'] = $this->values;
        }
        return $this;
    }

    /**
     * Validation failed?
     * @return bool
     */
    public function failed(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Is the validation valid.
     * @return bool
     */
    public function isValid(): bool
    {
        return empty($this->errors);
    }

    /**
     * @param array $errors
     */
    public function setErrors(array $errors)
    {
        $this->errors = $errors;
    }

    /**
     * Get all errors
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param array $values
     */
    public function setValues(array $values)
    {
        $this->values = $values;
    }


    /**
     * Set value by key
     * @param $key
     * @param mixed $default
     */
    public function setValue($key, $value)
    {
        $this->values[$key] = $value;
    }

    /**
     * Get all values
     * @return array
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * Get value by key
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public function getValue($key, $default = null)
    {
        return isset($this->values[$key]) ? $this->values[$key] : $default;
    }
}
