<?php namespace Crip\Core\Support;

use Crip\Core\Contracts\ICripObject;

/**
 * Class PackageBase
 * @package Crip\Core\App
 */
class PackageBase implements ICripObject
{
    /**
     * @var string
     */
    public $name = '';
    /**
     * @var string
     */
    public $config_name = '';
    /**
     * @var string
     */
    public $path = '';
    /**
     * @var string
     */
    public $public_path = '';
    /**
     * @var string
     */
    public $public_url = '';

    public $enable_translations = true;
    public $enable_views = true;
    public $enable_routes = true;
    public $publish_public = true;
    public $publish_database = true;
    public $publish_config = true;

    /**
     * @param $name
     * @param $path
     */
    public function __construct($name, $path)
    {
        $this->name = $name;
        $this->path = $path;

        $this->public_url = '/vendor/crip/' . $name . '/';
        $this->public_path = public_path($this->public_url);
        $this->config_name = $name;
    }

    /**
     * @param $name
     * @param $id
     * @param array $parameters
     * @param string $domain
     * @param null $locale
     * @return string
     */
    public function trans($id, $parameters = [], $domain = 'messages', $locale = null)
    {
        return trans($this->name . '::app.' . $id, $parameters, $domain, $locale);
    }

    /**
     * @param $name
     * @param null $view
     * @param array $data
     * @param array $mergeData
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function view($view = null, $data = [], $mergeData = [])
    {
        if ($view) {
            $view = self::incView($view);
        }

        return view($view, $data, $mergeData);
    }

    /**
     * @param $name
     * @param $view
     * @return string
     */
    public function incView($view)
    {
        return $this->name . '::' . $view;
    }

    /**
     * @param $name
     * @param $key
     * @param null $default
     * @return mixed
     */
    public function config($key, $default = null)
    {
        $key = $this->name . '.' . $key;

        return config($key, $default);
    }

    /**
     * Merge target array with values from configuration file
     *
     * @param array $target
     * @param $config_key
     *
     * @param array $default
     */
    public function mergeWithConfig(array &$target, $config_key, $default = [])
    {
        $target = array_merge_recursive($target, $this->config($config_key, $default));
    }
}