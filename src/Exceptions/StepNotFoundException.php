<?php

namespace Ycs77\LaravelWizard\Exceptions;

class StepNotFoundException extends InternalException
{
    /**
     * The step slug.
     *
     * @var string
     */
    protected $slug;

    /**
     * The wizard title.
     *
     * @var string
     */
    protected $wizardTitle;

    public function __construct(string $wizardTitle, string $slug)
    {
        parent::__construct("Step [$slug] is not found to wizard $wizardTitle.", 404);

        $this->slug = $slug;
        $this->wizardTitle = $wizardTitle;
    }
}
