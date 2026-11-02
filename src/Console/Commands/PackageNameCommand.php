<?php

declare(strict_types=1);

namespace VendorName\PackageName\Console\Commands;

use Illuminate\Console\Command;

class PackageNameCommand extends Command
{
    /**
     * The command signature.
     */
    protected $signature = 'package-name:placeholder';

    /**
     * The command description.
     */
    protected $description = 'Placeholder Artisan command shipped by the package package-name.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->line('PackageName placeholder command executed.');

        return self::SUCCESS;
    }
}
