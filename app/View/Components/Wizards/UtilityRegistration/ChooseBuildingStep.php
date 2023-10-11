<?php

namespace App\View\Components\Wizards\UtilityRegistration;

use Closure;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;
use Spatie\LivewireWizard\Components\StepComponent;

class ChooseBuildingStep extends StepComponent
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.wizards.utility-registration.choose-building-step');
    }
}
