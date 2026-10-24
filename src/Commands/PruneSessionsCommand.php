<?php

namespace JeffersonGoncalves\SessionManagement\Commands;

use Illuminate\Console\Command;
use JeffersonGoncalves\SessionManagement\SessionService;

class PruneSessionsCommand extends Command
{
    protected $signature = 'session-management:prune';

    protected $description = 'Delete expired sessions from the database session table';

    public function handle(SessionService $sessions): int
    {
        $this->components->info(sprintf('%d expired session(s) pruned.', $sessions->prune()));

        return self::SUCCESS;
    }
}
