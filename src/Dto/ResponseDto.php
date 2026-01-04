<?php declare(strict_types=1);

namespace Tbessenreither\PhpMultithread\Dto;

use Exception;
use Tbessenreither\PhpMultithread\Exception\TypeMissmatchException;
use Tbessenreither\PhpMultithread\Trait\SerializeFunctionsTrait;


class ResponseDto
{
    use SerializeFunctionsTrait;

    private ?float $startTime = null;
    private ?float $finishTime = null;

    private ResourceUsageDto $resourceUsageDto;

    public function __construct(
        public ?string $uuid = null,
        private mixed $result = null,
        private mixed $error = null,
        private ?string $output = null,
    ) {
        $this->startTime = microtime(true);
        $this->resourceUsageDto = new ResourceUsageDto();
    }

    public function setUuid(string $uuid): void
    {
        $this->uuid = $uuid;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function setResult(mixed $result): void
    {
        $this->finishTime = microtime(true);
        $this->result = $result;
    }

    /**
     * @template T
     * @param class-string<T> $expectedResponseType
     * @return T
     */
    public function getResult(string $expectedResponseType = 'mixed'): mixed
    {
        if ($expectedResponseType !== 'mixed' && !($this->result instanceof $expectedResponseType)) {
            throw new TypeMissmatchException("The response type does not match the expected type. Expected: " . $expectedResponseType . ", got: " . get_debug_type($this->result) . "");
        }

        return $this->result;
    }

    public function setError(mixed $error): void
    {
        $this->error = $error;
    }

    public function hasError(): bool
    {
        return $this->error !== null;
    }

    public function getError(): mixed
    {
        return $this->error;
    }

    public function setOutput(mixed $output): void
    {
        $this->output = $output;
    }

    public function getOutput(): mixed
    {
        return $this->output;
    }

    public function getDuration(): ?float
    {
        if (is_null($this->startTime) || is_null($this->finishTime)) {
            return null;
        }
        return $this->finishTime - $this->startTime;
    }

    public function getResourceUsageDto(): ResourceUsageDto
    {
        return $this->resourceUsageDto;
    }

}
