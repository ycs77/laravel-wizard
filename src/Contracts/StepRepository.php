<?php

namespace Ycs77\LaravelWizard\Contracts;

interface StepRepository
{
    /**
     * Get step instance.
     *
     * @param  int  $key
     * @return \Ycs77\LaravelWizard\Step|null
     */
    public function get(int $key);

    /**
     * Find step by slug.
     *
     * @param  string  $slug
     * @return \Ycs77\LaravelWizard\Step|null
     */
    public function find(string $slug);

    /**
     * Find step key by slug.
     *
     * @param  string  $slug
     * @return int|null
     */
    public function findKey(string $slug, int $default = null);

    /**
     * Set the steps.
     *
     * @param  mixed  $item
     * @return self
     */
    public function set($steps);

    /**
     * Push the steps to step repository.
     *
     * @param  array|\Ycs77\LaravelWizard\Step|string  $stepClass
     * @param  int|null  $index
     * @return self
     */
    public function push($stepClass, int $index = null);

    /**
     * Get all steps.
     *
     * @return array
     */
    public function all();

    /**
     * Get all steps count.
     *
     * @return int
     */
    public function count();

    /**
     * Get original steps.
     *
     * @return \Illuminate\Support\Collection
     */
    public function original();

    /**
     * Get or set the current step.
     *
     * @param  \Ycs77\LaravelWizard\Step|null  $step
     * @return \Ycs77\LaravelWizard\Step|null  $step
     */
    public function current($step = null);

    /**
     * Set the current step index.
     *
     * @param  int  $index
     * @return void
     */
    public function setCurrentIndex($index);

    /**
     * Get first step.
     *
     * @return \Ycs77\LaravelWizard\Step|null
     */
    public function first();

    /**
     * Get last step.
     *
     * @return \Ycs77\LaravelWizard\Step|null
     */
    public function last();

    /**
     * Get prev step.
     *
     * @return \Ycs77\LaravelWizard\Step|null
     */
    public function prev();

    /**
     * Get next step.
     *
     * @return \Ycs77\LaravelWizard\Step|null
     */
    public function next();

    /**
     * Checks if an a step.
     *
     * @param  int  $key
     * @return bool
     */
    public function has(int $key);

    /**
     * Checks if an a prev step.
     *
     * @return bool
     */
    public function hasPrev();

    /**
     * Checks if an a next step.
     *
     * @return bool
     */
    public function hasNext();

    /**
     * Get prev step slug.
     *
     * @return \Ycs77\LaravelWizard\Step|null
     */
    public function prevSlug();

    /**
     * Get next step slug.
     *
     * @return \Ycs77\LaravelWizard\Step|null
     */
    public function nextSlug();

    /**
     * Checks steps is empty.
     *
     * @return bool
     */
    public function isEmpty();

    /**
     * Checks steps is not empty.
     *
     * @return bool
     */
    public function isNotEmpty();
}
