<?php

namespace App\Livewire\Wizards;

use App\View\Components\Wizards\UtilityRegistration\ChooseBuildingStep;
use Livewire\Component;
use Spatie\LivewireWizard\Components\WizardComponent;

class UtilityRegistrationWizard extends WizardComponent
{
    public function steps() : array
    {
        return [
            ChooseBuildingStep::class,
        ];
    }
    public function render()
    {
        return view('livewire.wizards.utility-registration');
    }
}
