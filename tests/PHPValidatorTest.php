<?php

namespace TS\PHPValidator\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface as Request;
use Nyholm\Psr7\Factory\Psr17Factory;
use Respect\Validation\Validator as v;
use TS\PHPValidator\Validator;
use TS\PHPValidator\ValidatorMiddleware;
use TS\PHPValidator\ValidatorTwig;

class PHPValidatorTest extends TestCase
{
    public function testValidRequest()
    {
        $config = [
            'useSession' => false
        ];
        $request = $this->mockRequest("example@example.com", "Abcd1234_");

        $v = (new Validator($config))->validate($request, [
            'email' => v::noWhitespace()->notEmpty()->email(),
            'password' => v::noWhitespace()->notEmpty()->length(8)->alnum('_')
        ]);
        $this->assertTrue($v->isValid());
    }

    public function testNonValidRequest()
    {
        $config = [
            'useSession' => false
        ];
        $request = $this->mockRequest("example", "1234");

        $v = (new Validator($config))->validate($request, [
            'email' => v::noWhitespace()->notEmpty()->email(),
            'password' => v::noWhitespace()->notEmpty()->length(8)->alnum('_')
        ]);

        $this->assertTrue($v->failed());
    }

    public function testMiddleware()
    {
        $config = [
            'useSession' => true
        ];
        $request = $this->mockRequest("example@example.com", "1234");

        $v = (new Validator($config))->validate($request, [
            'email' => v::noWhitespace()->notEmpty()->email(),
            'password' => v::noWhitespace()->notEmpty()->length(8)->alnum('_')
        ]);

        $v = null;

        $request = $this->createServerRequest('test');
        $v = new Validator($config);
        $vm = new ValidatorMiddleware($v);
        $vm($request, new RequestHandlerTest());

        $this->assertTrue($v->failed());
        $this->assertSame("1234", $v->getValue('password'));
        $this->assertNotNull($v->getErrors());
    }

    public function testTwigFunctions()
    {
        $config = [
            'useSession' => false
        ];
        $request = $this->mockRequest("example@example.com", "1234");

        $v = (new Validator($config))->validate($request, [
            'email' => v::noWhitespace()->notEmpty()->email(),
            'password' => v::noWhitespace()->notEmpty()->length(8)->alnum('_')
        ]);

        $t = new ValidatorTwig($v);

        $this->assertTrue($t->hasErrors());
        $this->assertTrue($t->hasError('password'));
        $this->assertNotTrue($t->hasError('email'));

        $this->assertIsArray($t->getErrors());
        $this->assertIsArray($t->getError('password', false));
        $this->assertIsString($t->getError('password'));

        $this->assertSame("1234", $t->getValue('password'));
    }

    protected function mockRequest(string $email, string $password): Request
    {
        return $this->createServerRequest(
            '/test',
            'POST',
            [
                'email' => $email,
                'password' => $password
            ]
        );
    }

    /**
     * Modified From Slim/tests/Providers/PSR7ObjectProvider.php
     * @param string $uri
     * @param string $method
     * @param array $data
     * @return Request
     */
    protected function createServerRequest(
        string $uri,
        string $method = 'GET',
        array $data = []
    ): Request {
        $headers = [
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'HTTP_ACCEPT_CHARSET' => 'ISO-8859-1,utf-8;q=0.7,*;q=0.3',
            'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.8',
            'HTTP_HOST' => 'localhost',
            'HTTP_USER_AGENT' => 'Slim Framework',
            'QUERY_STRING' => '',
            'REMOTE_ADDR' => '127.0.0.1',
            'REQUEST_METHOD' => $method,
            'REQUEST_TIME' => time(),
            'REQUEST_TIME_FLOAT' => microtime(true),
            'REQUEST_URI' => '',
            'SCRIPT_NAME' => '/index.php',
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => 80,
            'SERVER_PROTOCOL' => 'HTTP/1.1',
        ];
        return (new Psr17Factory())->createServerRequest($method, $uri, $headers)->withParsedBody($data);
    }
}
