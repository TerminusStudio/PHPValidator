<?php

namespace TS\PHPValidator;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class ValidatorMiddleware
{
    /**
     * @var Validator Instance
     */
    protected Validator $validator;

    /**
     * ValidatorMiddleware constructor.
     * @param Validator $validator
     */
    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param Request $request
     * @param RequestHandler $handler
     * @return ResponseInterface
     */
    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface
    {
        if (isset($_SESSION['TS_PHPValidator'])) {
            $this->validator->setErrors($_SESSION['TS_PHPValidator']);
            unset($_SESSION['TS_PHPValidator']);
        }
        return $handler->handle($request);
    }
}