<?php

namespace InfyOm\Generator\Common;

class TemplatesManager
{
    protected $useLocale = false;

    /**
     * @return bool
     */
    public function isUsingLocale(): bool
    {
        return $this->useLocale;
    }

    /**
     * @param bool $useLocale
     */
    public function setUseLocale(bool $useLocale): void
    {
        $this->useLocale = $useLocale;
    }
}
