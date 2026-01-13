<?php

namespace App\Actions;

use Spatie\LaravelPasskeys\Actions\ConfigureCeremonyStepManagerFactoryAction as BaseAction;
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;

class ConfigureCeremonyStepManagerFactory extends BaseAction
{
    public function execute(): CeremonyStepManagerFactory
    {
        $factory = parent::execute();

        // Add localhost to secured Relying Party IDs to bypass HTTPS check for local development
        $factory->setSecuredRelyingPartyId(['localhost']);

        return $factory;
    }
}
