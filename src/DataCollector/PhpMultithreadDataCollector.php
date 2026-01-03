<?php

declare(strict_types=1);

namespace Tbessenreither\PhpMultithread\DataCollector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface;
use Tbessenreither\PhpMultithread\Dto\ThreadDto;
use Throwable;


class PhpMultithreadDataCollector extends DataCollector implements DataCollectorInterface
{
    public const NAME = 'tbessenreither.php_multithread_service_collector';
    public const TEMPLATE = '@TbessenreitherPhpMultithread/Profiler/php_multithread_service_collector.html.twig';

    public function __construct(
        private readonly string $appEnv,
    ) {
        $this->data['runs'] = [];
        $this->data['issues'] = [];
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function addRun(ThreadDto $threadDto): void
    {
        if (!$this->isCollecting()) {
            return;
        }

        $this->data['runs'][] = $threadDto;
    }

    /**
     * @return ThreadDto[]
     */
    public function getRuns(): array
    {
        return $this->data['runs'] ?? [];
    }

    public function getRunsGroupedByBatchNumber(): array
    {
        $batches = [];
        foreach ($this->getRuns() as $run) {
            if (!isset($batches[$run->getBatchNumber()])) {
                $batches[$run->getBatchNumber()] = [];
            }
            $batches[$run->getBatchNumber()][] = $run;
        }

        return $batches;
    }

    public function isActive(): bool
    {
        return !empty($this->getRuns());
    }

    public function collect(Request $request, Response $response, ?Throwable $exception = null): void
    {
    }

    public function isCollecting(): bool
    {
        return $this->appEnv === 'dev';
    }

    public function hasIssues(): bool
    {
        return !empty($this->data['issues']);
    }

    public function raiseIssue(string $message, array $context = []): void
    {
        if (!$this->isCollecting()) {
            return;
        }

        $this->data['issues'][] = [
            'message' => $message,
            'context' => $context,
        ];
    }

    public function getIssues(): array
    {
        return $this->data['issues'] ?? [];
    }

}
