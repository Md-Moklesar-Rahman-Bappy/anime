<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

class ViewCounterService
{
    protected const SESSION_PREFIX = 'viewed_';

    public function increment(Model $model, ?string $type = null): void
    {
        $key = $this->sessionKey($model, $type);

        if (! session()->has($key)) {
            $model->increment('views');
            session()->put($key, true);
        }
    }

    public function hasViewed(Model $model, ?string $type = null): bool
    {
        return session()->has($this->sessionKey($model, $type));
    }

    protected function sessionKey(Model $model, ?string $type = null): string
    {
        $class = $type ?? class_basename($model);

        return self::SESSION_PREFIX . strtolower($class) . "_{$model->getKey()}";
    }
}
