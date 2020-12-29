<?php

namespace TS\PHPValidator;

use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Exceptions\NestedValidationException;

class Validator
{
    protected array $errors = [];

    protected bool $useSession;

    public function __construct(bool $useSession = false)
    {
        $this->useSession = $useSession;
    }

    public function validate(Request $request, array $rules, $default = null)
    {
        $params = $request->getParsedBody();

        foreach ($rules as $field => $rule) {
            try {
                $param = isset($params[$field]) ? $params[$field] : $default;
                $rule->setName(ucfirst($field))->assert($param);
            } catch (NestedValidationException $e) {
                $this->errors[$field] = $e->getMessages();
            }
        }

        if ($this->failed() && $this->useSession) {
            $_SESSION['TS_PHPValidator'] = $this->errors;
        }

        return $this;
    }

    public function failed()
    {
        return !empty($this->errors);
    }

    public function setErrors(array $errors) {
        $this->errors = $errors;
    }
}
