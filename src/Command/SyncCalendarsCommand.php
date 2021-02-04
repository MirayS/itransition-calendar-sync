<?php

namespace App\Command;

use App\Service\CalendarService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SyncCalendarsCommand extends Command
{
    protected static $defaultName = 'app:sync-calendars';
    /**
     * @var CalendarService
     */
    private CalendarService $calendarService;

    public function __construct(CalendarService $calendarService, string $name = null)
    {
        parent::__construct($name);
        $this->calendarService = $calendarService;
    }

    protected function configure()
    {
        $this
            ->setDescription('Sync all calendars')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->calendarService->syncAllCalendars();

        $io->success('Calendars successful synced');

        return Command::SUCCESS;
    }
}
