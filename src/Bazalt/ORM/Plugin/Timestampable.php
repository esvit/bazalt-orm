<?php
/**
 * Timestampable.php
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
use Bazalt\ORM as ORM;

/**
 * Timestampable
 * Плагін, який автоматично заповнить поля created та updated в моделі
 *
 * @category   System
 * @package    ORM
 * @subpackage Plugin
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */
class Timestampable extends AbstractPlugin
{
    /**
     * Ініціалізує плагін
     *
     * @param Record $model   Модель, для якої викликано initFields
     * @param array      $options Масив опцій, передається з базової моделі при ініціалізації плагіна
     *
     * @return void
     */
    public function init(Record $model, $options)
    {
        ORM\BaseRecord::registerEvent($model->getModelName(), ORM\BaseRecord::ON_RECORD_SAVE, array($this,'onSave'));
    }

    public function toArray(ORM\Record $record, $itemArray, $options)
    {
        if(array_key_exists('created', $options) && isset($record->{$options['created']})) {
            $itemArray[$options['created']] = strToTime($record->{$options['created']})  * 1000; // for javascript time
        }
        if(array_key_exists('updated', $options) && isset($record->{$options['updated']})) {
            $itemArray[$options['updated']] = strToTime($record->{$options['updated']})  * 1000; // for javascript time
        }
        return $itemArray;
    }

    /**
     * Додає додаткові службові поля до моделі.
     * Викликається в момент ініціалізації моделі
     *
     * @param Record $model   Модель, для якої викликано initFields
     * @param array      $options Масив опцій, передається з базової моделі при ініціалізації плагіна
     *
     * @return void
     */
    protected function initFields(Record $model, $options)
    {
        $columns = $model->getColumns();
        if(array_key_exists('created', $options) && !array_key_exists($options['created'], $columns)) {
            $model->hasColumn($options['created'], 'N:datetime');//|CURRENT_TIMESTAMP
        }
        if(array_key_exists('updated', $options) && !array_key_exists($options['updated'], $columns)) {
            $model->hasColumn($options['updated'], 'N:datetime');
        }
    }

    /**
     *
     *
     * @param Record $record  Поточний запис
     * @param bool       &$return Флаг, який зупиняє подальше виконання save()
     *
     * @return void
     */
    public function onSave(Record $record, &$return)
    {
        $options = $this->getOptions();
        if (!array_key_exists($record->getModelName(), $options)) {
            return;
        }
        $options = $options[$record->getModelName()];
        if(array_key_exists('created', $options) && $record->isPKEmpty()) {
            $record->{$options['created']} = date('Y-m-d H:i:s');
        }
        if(array_key_exists('updated', $options)) {
            $record->{$options['updated']} = date('Y-m-d H:i:s');
        }
    }
}