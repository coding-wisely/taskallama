<?php

namespace CodingWisely\Taskallama\Commands;

use Illuminate\Console\Command;

class TaskallamaCommand extends Command
{
    public $signature = 'taskallama';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
