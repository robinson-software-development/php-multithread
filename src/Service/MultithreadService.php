<?php declare(strict_types=1);

namespace Tbessenreither\PhpMultithread\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Stopwatch\Stopwatch;
use Tbessenreither\PhpMultithread\DataCollector\PhpMultithreadDataCollector;
use Tbessenreither\PhpMultithread\Dto\ResponseDto;
use Tbessenreither\PhpMultithread\Dto\ThreadDto;
use Tbessenreither\PhpMultithread\Interface\ThreadRunnerInterface;
use Tbessenreither\PhpMultithread\Service\Runners\CommandRunner;
use Tbessenreither\PhpMultithread\Service\Runners\PcntlRunner;


class MultithreadService
{

    private int $batchNumber = 0;

    public function __construct(
        #[Autowire(service: PcntlRunner::class)]
        private ThreadRunnerInterface $pcntlRunner,
        #[Autowire(service: CommandRunner::class)]
        private ThreadRunnerInterface $commandRunner,
        private PhpMultithreadDataCollector $phpMultithreadDataCollector,
        private Stopwatch $stopwatch,
    ) {
    }

    /**
     * @param ThreadDto[] $threadDto
     * @return ResponseDto[]
     */
    public function runThreads(array $threadDtos): array
    {
        $this->batchNumber++;

        $this->checkThreadDtos($threadDtos);

        $runner = $this->getRunner();

        $this->prepareThreadDtos(
            runner: $runner::class,
            threadDtos: $threadDtos,
        );

        $this->stopwatch->start("Run Threads", "php-multithread");
        $runnerResponses = $runner->run(
            threadDtos: $threadDtos,
        );
        $this->stopwatch->stop("Run Threads");

        return $runnerResponses;
    }

    private function getRunner(): ThreadRunnerInterface
    {
        if (function_exists('pcntl_fork')) {
            return $this->pcntlRunner;
        }

        return $this->commandRunner;
    }

    private function prepareThreadDtos(string $runner, array $threadDtos): void
    {
        foreach ($threadDtos as $threadDto) {
            $threadDto->setRunner($runner);
            $threadDto->setBatchNumber($this->batchNumber);
            $this->phpMultithreadDataCollector->addRun($threadDto);
        }
    }

    private function checkThreadDtos(array &$threadDtos): void
    {
        foreach ($threadDtos as $key => $threadDto) {
            if ($threadDto->hasResponse()) {
                $this->phpMultithreadDataCollector->raiseIssue(
                    message: 'Thread already ran and was removed.',
                    context: [
                        'batch' => $this->batchNumber,
                        'uuid' => $threadDto->getUuid(),
                        'class' => $threadDto->getClass(),
                        'method' => $threadDto->getMethod(),
                        'parameters' => $threadDto->getParameters(),
                    ],
                );
                unset($threadDtos[$key]);
            }
        }
    }

}
