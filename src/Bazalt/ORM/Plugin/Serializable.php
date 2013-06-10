<?php
/**
 * Serializable.php
 *
 * @category   System
 * @package    ORM
 * @subpackage Plugin
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

namespace Bazalt\ORM\Plugin;

use Bazalt\ORM as ORM;

/**
 * Serializable 
 * Плагін, що надає змогу автоматично серіалізувати поля в базі даних 
 * @link http://wiki.bazalt.org.ua/ORMSerializable
 *
 * @category   System
 * @package    ORM
 * @subpackage Plugin
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */
class Serializable extends AbstractPlugin
{
    /**
     * Ініціалізує плагін
     * 
     * @param ORM\Record $model   Модель, для якої викликано initFields
     * @param array      $options Масив опцій, передається з базової моделі при ініціалізації плагіна
     *
     * @return void 
     */
    public function init(ORM\Record $model, $options)
    {
        ORM\BaseRecord::registerEvent($model->getModelName(), ORM\BaseRecord::ON_FIELD_GET, array($this,'onGet'), ORM\BaseRecord::FIELD_IS_SETTED);
        ORM\BaseRecord::registerEvent($model->getModelName(), ORM\BaseRecord::ON_FIELD_SET, array($this,'onSet'));
    }

    public function toArray(ORM\Record $record, $itemArray, $options)
    {
        if (!is_array($options)) {
            $options = explode(',', $options);
        }
        foreach ($options as $field) {
            $itemArray[$field] = $record->{$field};
        }
        return $itemArray;
    }

    /**
     * Хендлер на евент onGet моделей які юзають плагін.
     * Евент запалюється при виклику __get() для поля і повертає десеріалізоване значення
     *
     * @param ORM\Record   $record  Поточний запис
     * @param string       $field   Поле для якого викликається __get()
     * @param bool|string  &$return Результат, який повернеться методом __get()
     *
     * @return void 
     */
    public function onGet(ORM\Record $record, $field, &$return)
    {
        $options = $this->getOptions();
        if (!array_key_exists($record->getModelName(), $options)) {
            return;
        }

        $options = $options[$record->getModelName()];
        if (!is_array($options)) {
            $options = explode(',', $options);
        }
        if (in_array($field, $options)) {
            $return = unserialize($record->getField($field));
        }
    }
    
    /**
     * Хендлер на евент onSet моделей які юзають плагін.
     * Евент запалюється при виклику __set() для поля і встановлює в поле серіалізоване значення
     *
     * @param ORM\Record $record  Поточний запис
     * @param string     $field   Поле для якого викликається __set()
     * @param string     $value   Значення яке передається в __set()
     * @param bool       &$return Флаг, який зупиняє подальше виконання __set()
     *
     * @return void 
     */
    public function onSet(ORM\Record $record, $field, $value, &$return)
    {
        $options = $this->getOptions();
        if (!array_key_exists($record->getModelName(), $options)) {
            return;
        }

        $options = $options[$record->getModelName()];
        if (!is_array($options)) {
            $options = explode(',', $options);
        }
        if (in_array($field, $options)) {
            $record->setField($field, serialize($value));
            $return = true;
        }
    }
}