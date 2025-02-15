<?php namespace EvolutionCMS\Services;


class ConfigService
{
    public function get($config = '', $default = null)
    {
        return EvolutionCMS()->getConfig($config, $default);
    }
    public function set($name, $value)
    {
        EvolutionCMS()->setConfig($name, $value);
    }

}
