<?php

declare(strict_types=1);

arch()->preset()->php();
arch()->preset()->security();
arch()->preset()->laravel();

arch('strict types everywhere')
    ->expect('VendorName\PackageName')
    ->toUseStrictTypes();

arch('no env() outside config files')
    ->expect('env')
    ->not->toBeUsed();

arch('no blocking sleeps')
    ->expect(['sleep', 'usleep'])
    ->not->toBeUsed();
