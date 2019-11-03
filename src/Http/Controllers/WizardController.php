<?php

namespace Ycs77\LaravelWizard\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Ycs77\LaravelWizard\Wizardable;

class WizardController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, Wizardable;
}
