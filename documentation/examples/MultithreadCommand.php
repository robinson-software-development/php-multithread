<?php declare(strict_types=1);

namespace App\Command;

use App\Service\TestService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tbessenreither\PhpMultithread\Dto\ThreadDto;
use Tbessenreither\PhpMultithread\Service\MultithreadService;

#[AsCommand(
    name: 'app:multithread',
    description: 'Add a short description for your command',
)]


class MultithreadCommand extends Command
{

    public function __construct(
        private MultithreadService $multithreadService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Start a multithread test');

        $threads = [];
        for ($i = 0; $i < 10; $i++) {
            $threads[] = new ThreadDto(
                class: TestService::class,
                method: 'doSomethingSlow',
                parameters: [],
                timeout: rand(2, 4),
            );
        }
        $results = $this->multithreadService->runThreads($threads);


        $threads = [$threads[0]];
        for ($i = 0; $i < 20; $i++) {
            $threads[] = new ThreadDto(
                class: TestService::class,
                method: 'doSomething',
                parameters: [],
            );
        }
        $results = $this->multithreadService->runThreads($threads);

        $io->success('Threads did something');

        return Command::SUCCESS;
    }

}
