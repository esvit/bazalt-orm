<?php
/**
 * RelationsCounter.php
 *
 * @category   System
 * @package    ORM
 * @subpackage Plugin
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

namespace Bazalt\ORM\Plugin;

/**
 * ORM_Plugin_RelationsCounter
 * @link http://wiki.bazalt.org.ua/ORMRelationsCounter
 *
 * @category   System
 * @package    ORM
 * @subpackage Plugin
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */
class RelationsCounter extends AbstractPlugin
{
    /**
     * Ініціалізує плагін
     * 
     * @param ORM_Record $model   Модель, для якої викликано initFields
     * @param array      $options Масив опцій, передається з базової моделі при ініціалізації плагіна
     *
     * @return void 
     */
    public function init(ORM_Record $model, $options)
    {
        //Event::register($model->$options['relation'], 'OnAdd', array($this,'onAdd'));
        //Event::register($model->$options['relation'], 'OnRemove', array($this,'onRemove'));
    }
    
    /**
     * Хендлер на евент onAdd моделей які юзають плагін.
     * Евент запалюється при додаванні нового запису в реферальну таблицю
     *
     * @param ORM_Record   $record    Поточний об'єкт моделі, до якого доданий плагін
     * @param ORM_Record   $refRecord Об'єкт реферальної моделі
     *
     * @return void 
     */
    public function onAdd(ORM_Record $record, ORM_Record $refRecord)
    {
        $options = $this->getOptions();
        if (!array_key_exists(get_class($record), $options)) {
            return;
        }
        $options = $options[get_class($record)];
        if(isset($options['condition']) && is_array($options['condition'])) {
            foreach($options['condition'] as $field => $value) {
                if($refRecord->$field != $value) {
                    return;
                }
            }
        }
        $record->{$options['field']}++;
        $record->save();
    }
    
    /**
     * Хендлер на евент onRemove моделей які юзають плагін.
     * Евент запалюється видаленні запису з реферальної таблиці
     *
     * @param ORM_Record   $record    Поточний об'єкт моделі, до якого доданий плагін
     * @param ORM_Record   $refRecord Об'єкт реферальної моделі
     *
     * @return void 
     */
    public function onRemove(ORM_Record $record, ORM_Record $refRecord)
    {
        $options = $this->getOptions();
        if (!array_key_exists(get_class($record), $options)) {
            return;
        }
        $options = $options[get_class($record)];
        if(isset($options['condition']) && is_array($options['condition'])) {
            foreach($options['condition'] as $field => $value) {
                if($refRecord->$field != $value) {
                    return;
                }
            }
        }
        $record->{$options['field']}--;
        if($record->{$options['field']} < 0) {
            $record->{$options['field']} = 0;
        }
        $record->save();
    }
}