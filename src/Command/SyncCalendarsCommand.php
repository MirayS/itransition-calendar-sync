<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\CalendarEntityService;
use App\Service\GoogleService\GoogleCalendarEventsParser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SyncCalendarsCommand extends Command
{
    protected static $defaultName = 'app:sync-calendars';

    private CalendarEntityService $calendarService;

    private GoogleCalendarEventsParser $googleCalendarEventsParser;

    public function __construct(CalendarEntityService $calendarService, GoogleCalendarEventsParser $googleCalendarEventsParser, string $name = null)
    {
        parent::__construct($name);
        $this->calendarService = $calendarService;
        $this->googleCalendarEventsParser = $googleCalendarEventsParser;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Sync all calendars')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $calendars = $this->calendarService->getAllCalendars();
        foreach ($calendars as $calendar) {
            $this->googleCalendarEventsParser->parseEvents($calendar);
        }

        $io->success('Calendars successful synced');

        return Command::SUCCESS;
    }
}
