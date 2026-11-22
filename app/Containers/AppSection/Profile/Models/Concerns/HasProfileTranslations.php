<?php

namespace App\Containers\AppSection\Profile\Models\Concerns;

use Spatie\Translatable\HasTranslations;

trait HasProfileTranslations
{
    use HasTranslations {
        HasTranslations::setAttribute as setTranslatableAttribute;
    }

    /**
     * @param string $key
     * @param mixed $value
     *
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        if (!$this->isTranslatableAttribute($key)) {
            return $this->setTranslatableAttribute($key, $value);
        }

        if (null === $value) {
            $this->attributes[$key] = null;

            return $this;
        }

        if (is_array($value) && [] !== $value && !array_is_list($value)) {
            return $this->replaceTranslations($key, $value);
        }

        return $this->setTranslatableAttribute($key, $value);
    }
}
