<?php

namespace Ycs77\LaravelWizard;

use Illuminate\Support\Collection;
use Ycs77\LaravelWizard\Contracts\StepRepository as StepRepositoryContract;

class StepRepository implements StepRepositoryContract
{
    /**
     * The wizard instance.
     *
     * @var \Ycs77\LaravelWizard\Wizard
     */
    protected $wizard;

    /**
     * The steps instance.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $steps;

    /**
     * The current step index.
     *
     * @var int
     */
    protected $currentIndex = 0;

    /**
     * Create a new steps.
     *
     * @param  \Ycs77\LaravelWizard\Wizard  $wizard
     * @param  mixed  $steps
     */
    public function __construct(Wizard $wizard)
    {
        $this->wizard = $wizard;
        $this->steps = new Collection();
    }

    /**
     * Get step instance.
     *
     * @param  int $key
     * @return \Ycs77\LaravelWizard\Step|null
     */
    public function get(int $key)
    {
        return $this->steps->get($key);
    }

    /**
     * Find step by slug.
     *
     * @param  string $slug
     * @return \Ycs77\LaravelWizard\Step|null
     */
    public function find(string $slug)
    {
        /** @param \Ycs77\LaravelWizard\Step $step */
        return $this->steps->first(function ($step) use ($slug) {
            return $step->slug() === $slug;
        });
    }

    /**
     * Set the steps.
     *
     * @param  mixed  $steps
     * @return self
     */
    public function set($steps)
    {
        $this->steps = new Collection($steps);
        return $this;
    }

    /**
     * Make new step instance.
     *
     * @param  mixed  $stepClass
     * @param  int|null  $index
     * @return self
     */
    public function make($stepClass, int $index = null)
    {
        if (is_array($stepClass)) {
            foreach ($stepClass as $_index => $_stepClass) {
                $step = new $_stepClass($this->wizard, $_index);
                $this->steps->push($step);
            }
        } else {
            $step = new $stepClass($this->wizard, $index);
            $this->steps->push($step);
        }

        return $this;
    }

    /**
     * Get all steps.
     *
     * @return array
     */
    public function all()
    {
        return $this->steps->all();
    }

    /**
     * Get all steps count.
     *
     * @return int
     */
    public function count()
    {
        return $this->steps->count();
    }

    /**
     * Get original steps.
     *
     * @return \Illuminate\Support\Collection
     */
    public function original()
    {
        return $this->steps;
    }

    /**
     * Get or set the current step.
     *
     * @param  \Ycs77\LaravelWizard\Step|null  $step
     * @return \Ycs77\LaravelWizard\Step|null  $step
     */
    public function current($step = null)
    {
        if ($step) {
            $this->currentIndex = $step->index();
        }

        return $this->get($this->currentIndex);
    }

    /**
     * Set the current step index.
     *
     * @param  int  $index
     * @return void
     */
    public function setCurrentIndex($index)
    {
        $this->currentIndex = $index;
    }

    /**
     * Get first step.
     *
     * @return \Ycs77\LaravelWizard\Step|null
     */
    public function first()
    {
        return $this->steps->first();
    }

    /**
     * Get last step.
     *
     * @return \Ycs77\LaravelWizard\Step|null
     */
    public function last()
    {
        return $this->steps->last();
    }

    /**
     * Get prev step.
     *
     * @return \Ycs77\LaravelWizard\Step|null
     */
    public function prev()
    {
        return $this->get($this->currentIndex - 1);
    }

    /**
     * Get next step.
     *
     * @return \Ycs77\LaravelWizard\Step|null
     */
    public function next()
    {
        return $this->get($this->currentIndex + 1);
    }

    /**
     * Checks if an a step.
     *
     * @param  int  $key
     * @return bool
     */
    public function has(int $key)
    {
        return (bool)$this->get($key);
    }

    /**
     * Checks if an a prev step.
     *
     * @return bool
     */
    public function hasPrev()
    {
        return (bool)$this->prev();
    }

    /**
     * Checks if an a next step.
     *
     * @return bool
     */
    public function hasNext()
    {
        return (bool)$this->next();
    }

    /**
     * Get prev step slug.
     *
     * @return string|null
     */
    public function prevSlug()
    {
        return $this->hasPrev() ? $this->prev()->slug() : null;
    }

    /**
     * Get next step slug.
     *
     * @return string|null
     */
    public function nextSlug()
    {
        return $this->hasNext() ? $this->next()->slug() : null;
    }

    /**
     * Checks steps is empty.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return $this->steps->isEmpty();
    }

    /**
     * Checks steps is not empty.
     *
     * @return bool
     */
    public function isNotEmpty()
    {
        return $this->steps->isNotEmpty();
    }
}
