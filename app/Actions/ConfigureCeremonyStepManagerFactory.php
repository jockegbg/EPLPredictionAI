<?php

namespace App\Actions;

use Spatie\LaravelPasskeys\Actions\ConfigureCeremonyStepManagerFactoryAction as BaseAction;
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;

class ConfigureCeremonyStepManagerFactory extends BaseAction
{
    public function execute(): CeremonyStepManagerFactory
    {
        $factory = parent::execute();

        // Build list of secured domains (HTTPS or localhost)
        $securedDomains = ['localhost'];

        // Add production domain from APP_URL if it exists
        $appUrl = config('app.url');
        if ($appUrl) {
            $host = parse_url($appUrl, PHP_URL_HOST);
            if ($host && $host !== 'localhost') {
                $securedDomains[] = $host;
            }
        }

        $factory->setSecuredRelyingPartyId($securedDomains);

        return $factory;
    }
}
