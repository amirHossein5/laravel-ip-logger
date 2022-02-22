<?php

namespace AmirHossein5\LaravelIpLogger;

use Closure;
use Illuminate\Database\Eloquent\Model;

trait Eloquent
{
    private ?string $model = null;

    public function model(string $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function UpdateOrCreate(Closure $attributes, Closure $values): bool|Model
    {
        $model = $this->model;
        $details = $this->getDetails();

        if ($this->exception) {
            return false;
        }

        $attributes = $attributes($details);
        $values = $values($details);

        $this->resetProps();

        return $model::UpdateOrCreate($attributes, $values);
    }

    public function create(Closure $values): bool|Model
    {
        $model = $this->model;
        $details = $this->getDetails();

        if ($this->exception) {
            return false;
        }

        $values = $values($details);

        $this->resetProps();

        return $model::create($values);
    }
}
