<?php

namespace AmirHossein5\LaravelIpLogger;

use Closure;
use Illuminate\Database\Eloquent\Model;

trait Eloquent
{
    /**
     * The intended model that ip details is going to be save.
     *
     * @var null|string
     */
    private ?string $model = null;

    /**
     * Sets the model.
     *
     * @param string $model
     *
     * @return self
     */
    public function model(string $model): self
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Create if attributes does not exist.
     *
     * @param \Closure $attributes
     * @param \Closure $values
     *
     * @return bool|\Illuminate\Database\Eloquent\Model
     */
    public function UpdateOrCreate(Closure $attributes, Closure $values): bool|Model
    {
        $model = $this->model;
        $details = $this->details();

        if ($this->exception) {
            $this->resetProps();

            return false;
        }

        $attributes = $attributes($details);
        $values = $values($details);

        $this->resetProps();

        return $model::UpdateOrCreate($attributes, $values);
    }

    /**
     * Saves details in the database.
     *
     * @param \Closure $values
     *
     * @return bool|\Illuminate\Database\Eloquent\Model
     */
    public function create(Closure $values): bool|Model
    {
        $model = $this->model;
        $details = $this->details();

        if ($this->exception) {
            $this->resetProps();

            return false;
        }

        $values = $values($details);

        $this->resetProps();

        return $model::create($values);
    }
}
