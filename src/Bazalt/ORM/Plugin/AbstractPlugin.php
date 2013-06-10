<?php
/**
 * Abstract.php
 *
 * @category   System
 * @package    ORM
 * @subpackage Plugin
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

namespace Bazalt\ORM\Plugin;

use Bazalt\ORM\Record;

/**
 * AbstractPlugin
 * Клас, що описує плагін ORM
 *
 * @category   System
 * @package    ORM
 * @subpackage Plugin
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */
abstract class AbstractPlugin
{
    /**
     * All loaded plugins
     *
     * @var array
     */
    protected static $allPlugins = array();

    /**
     * Options of all plugins
     *
     * @var array
     */
    private static $_options = array();

    /**
     * Init plugin
     *
     * @param Record $model   Record
     * @param array      $options Options for plugin
     *
     * @return void
     */
    abstract function init(Record $model, $options);

    /**
     * Init model fields
     *
     * @param Record $model   Record
     * @param array      $options Options for plugin
     *
     * @return void
     */
    protected function initFields(Record $model, $options)
    {
    }

    /**
     * Init model relations
     *
     * @param Record $model   Record
     * @param array     $options Options for plugin
     *
     * @return void
     */
    protected function initRelations($model, $options)
    {
    }

    /**
     * Init model plugins
     *
     * @param Record $model   Record
     * @param array     $options Options for plugin
     *
     * @return void
     */
    protected function initPlugins($model, $options)
    {
    }

    /**
     * Constructor
     */
    protected function __construct()
    {
        self::initPlugin($this);
    }

    /**
     * Init plugin
     *
     * @param AbstractPlugin &$plugin Plugin
     *
     * @return void
     */
    protected static function initPlugin(&$plugin)
    {
        self::$allPlugins[get_class($plugin)] = &$plugin;
    }

    /**
     * Return plugin by name
     *
     * @param string $name Name of plugin
     *
     * @throws Exception
     * @return AbstractPlugin Plugin
     */
    public static function getPlugin($name)
    {
        if (!array_key_exists($name, self::$allPlugins)) {
            $plugin = new $name();
            if (!($plugin instanceof AbstractPlugin)) {
                throw new \Exception('Class ' . $name . ' must have parent class ' . 'AbstractPlugin');
            }
            return $plugin;
        }
        return self::$allPlugins[$name];
    }

    /**
     * Init plugin for model
     *
     * @param Record $model   Record
     * @param array      $options Options for plugin
     *
     * @return void
     */
    public function initForModel(Record $model, $options)
    {
        self::$_options[get_class($this)][$model->getModelName()] = $options;
        
        $this->initFields($model, $options);
        $this->initRelations($model, $options);
        $this->initPlugins($model, $options);
        
        $this->init($model, $options);
    }

    /**
     * Get plugin options
     *
     * @return array Options
     */
    public function getOptions()
    {
        return self::$_options[get_class($this)];
    }

    public function toArray(Record $record, $itemArray, $options)
    {
        return $itemArray;
    }
}
