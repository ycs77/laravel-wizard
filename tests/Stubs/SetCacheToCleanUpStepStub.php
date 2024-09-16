<?php

namespace Ycs77\LaravelWizard\Test\Stubs;

use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Http\Request;
use Ycs77\LaravelWizard\Step;
use Ycs77\LaravelWizard\Wizard;

class SetCacheToCleanUpStepStub extends Step
{
    /**
     * The step slug.
     *
     * @var string
     */
    protected $slug = 'set-cache-step-stub';

    /**
     * The step show label text.
     *
     * @var string
     */
    protected $label = 'Set cache step stub';

    /**
     * The cache store instance.
     */
    protected Cache $cache;

    /**
     * Create a new step instance.
     *
     * @return void
     */
    public function __construct(Wizard $wizard, int $index, Cache $cache)
    {
        parent::__construct($wizard, $index);

        $this->cache = $cache;
    }

    /**
     * Set the step model instance or the relationships instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\Relation|null
     */
    public function model(Request $request)
    {
        //
    }

    /**
     * Save this step form data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array|null  $data
     * @param  \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\Relation|null  $model
     * @return void
     */
    public function saveData(Request $request, $data = null, $model = null)
    {
        //
    }

    /**
     * Validation rules.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function rules(Request $request)
    {
        return [
            //
        ];
    }

    /**
     * On step saved event.
     *
     * @return \Illuminate\Http\RedirectResponse|null
     */
    public function onStepSaved(Request $request)
    {
        $this->cache->put('wizard.clean-up.cached', true);
    }
}
