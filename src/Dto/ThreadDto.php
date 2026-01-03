<?php declare(strict_types=1);

namespace Tbessenreither\PhpMultithread\Dto;

use InvalidArgumentException;
use Symfony\Component\Uid\UuidV7;
use Tbessenreither\PhpMultithread\Trait\SerializeFunctionsTrait;


class ThreadDto
{
    use SerializeFunctionsTrait;

    private string $uuid;
    private ?ResponseDto $response = null;
    private ?string $runner = null;

    public function __construct(
        private string $class,
        private string $method,
        private array $parameters = [],
    ) {
        $this->uuid = UuidV7::v7()->toRfc4122();

        if (!class_exists($this->class)) {
            throw new InvalidArgumentException("The given class does not exist. " . $this->class . "");
        }
        if (!method_exists($this->class, $this->method)) {
            throw new InvalidArgumentException("The given method does not exist in the class. " . $this->class . "::" . $this->method . "()");
        }
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setResponse(ResponseDto $response): void
    {
        $this->response = $response;
    }

    public function getResponse(): ?ResponseDto
    {
        return $this->response;
    }

    /**
     * @param class-string $runner
     * @return void
     */
    public function setRunner(string $runner): void
    {
        $this->runner = $runner;
    }

    public function getRunner(): string
    {
        return $this->runner ?? 'n/a';
    }

}
