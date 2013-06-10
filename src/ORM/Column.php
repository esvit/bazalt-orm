<?php
/**
 * Column.php
 *
 * @category   System
 * @package    ORM
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

namespace Bazalt\ORM;

/**
 * Клас, який описує одну колонку в базі даних
 *
 * @category   System
 * @package    ORM
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */ 
class Column
{
    /**
     * Поле AUTO INCREMENT
     */
    const AUTO_INCREMENT = 'auto_increment';

    /**
     * Поле PRIMARY KEY
     */
    const PRIMARY_KEY = 'primary_key';

    /**
     * Дефолтне значення поля
     */
    const DEFAULT_VALUE = 'default';

    /**
     * Поле має беззнакове числове значення
     */
    const UNSIGNED = 'unsigned';

    /**
     * Поле може мати значення NULL
     */
    const NULLABLE = 'nullable';

    /**
     * Поле має автоінкремент
     */
    const FLAG_AUTO_INCREMENT = 'A';

    /**
     * Поле є основним ключем
     */
    const FLAG_PRIMARY_KEY = 'P';

    /**
     * Поле може бути тільки додатнє
     */
    const FLAG_UNSIGNED = 'U';

    /**
     * Поле може бути пустим
     */
    const FLAG_NULLABLE = 'N';

    /**
     * Поле може бути пустим
     */
    const FLAG_SEPARATOR = ':';
    
    /**
     * Розділювач для дефолтного значення поля
     */
    const DEFAULT_SEPARATOR = '|';

    /**
     * Назва колонки
     *
     * @var string
     */
    protected $name;

    /**
     * Опції
     *
     * @var array
     */
    protected $options;

    /**
     * Тип
     *
     * @var string
     */
    protected $dataType;

    /**
     * Довжина
     *
     * @var int
     */
    protected $length = null;

    /**
     * Construct
     *
     * @param string       $name    Назва колонки
     * @param string|array $options Опції колонки
     */
    public function __construct($name, $options)
    {
        $this->name = $name;
        if (is_array($options)) {
            $this->options = $options;
        } else {
            $this->options = self::parseFlagOptions($options);
            if (isset($this->options['type'])) {
                $this->dataType = $this->options['type'];
                unset($this->options['type']);
            }
            if (isset($this->options['length'])) {
                $this->length = $this->options['length'];
                unset($this->options['length']);
            }
        }
    }

    /**
     * Повертає або встановлює назву поля
     *
     * @param string|null $value Назва поля
     *
     * @return string Назва поля
     */
    public function name($value = null)
    {
        if ($value != null) {
            $this->name = $value;
            return $this;
        }
        return $this->name;
    }

    /**
     * Повертає або встановлює довжину поля
     *
     * @param int|null $value Довжина поля
     *
     * @return int Довжина поля
     */
    public function length($value = null)
    {
        if ($value != null) {
            $this->length = $value;
            return $this;
        }
        return $this->length;
    }

    /**
     * Повертає або встановлює тип данних поля
     *
     * @param string|null $value Тип данних поля
     *
     * @return string Тип данних поля
     */
    public function dataType($value = null)
    {
        if ($value != null) {
            $this->dataType = $value;
            return $this;
        }
        return $this->dataType;
    }

    /**
     * Повертає або встановлює опції поля
     *
     * @param array|null $value Опції поля
     *
     * @return array Опції поля
     */
    public function options($value = null)
    {
        if ($value != null) {
            $this->options = $value;
            return $this;
        }
        return $this->options;
    }

    /**
     * Розбиває строку флагів на опції
     *
     * @param string $str Опції поля
     * 
     * @todo Parse data type
     *
     * @return array Масив опцій
     */
    public static function parseFlagOptions($str)
    {
        $options = array();
        if (stripos($str, self::FLAG_SEPARATOR) !== false) {
            for ($i = 0; $i < strlen($str); $i++) {
                switch($str{$i}) {
                case self::FLAG_AUTO_INCREMENT :
                    $options[self::AUTO_INCREMENT] = true;
                    break;
                case self::FLAG_PRIMARY_KEY:
                    $options[self::PRIMARY_KEY] = true;
                    break;
                case self::FLAG_UNSIGNED:
                    $options[self::UNSIGNED] = true;
                    break;
                case self::FLAG_NULLABLE:
                    $options[self::NULLABLE] = true;
                    break;
                case self::FLAG_SEPARATOR: 
                    break 2;
                }
            }
            $str = substr($str, $i + 1);
        }
        if (stripos($str, self::DEFAULT_SEPARATOR) !== false) {
            $strs = explode(self::DEFAULT_SEPARATOR, $str);
            $str = $strs[0];
            $options[self::DEFAULT_VALUE] = $strs[1];
        }
        preg_match('#([^\(\)]+)(\((.*)\))?#', $str, $matches); 

        if (isset($matches[1])) {
            $options['type'] = $matches[1];
        }
        if (isset($matches[3])) {
            $options['length'] = $matches[3];
        }
        return $options;
    }

    /**
     * Перевіряє чи є стовпець PRIMARY KEY
     *
     * @return bool
     */
    public function isPrimaryKey()
    {
        return (array_key_exists(self::PRIMARY_KEY, $this->options) && 
                 ($this->options[self::PRIMARY_KEY] == true));
    }

    /**
     * Перевіряє чи стовпець AUTO INCREMENT
     *
     * @return bool
     */
    public function isAutoIncrement()
    {
        return (array_key_exists(self::AUTO_INCREMENT, $this->options) && 
                  $this->options[self::AUTO_INCREMENT] === true);
    }

    /**
     * Перевіряє чи стовпець UNSIGNED
     *
     * @return bool
     */
    public function isUnsigned()
    {
        return (array_key_exists(self::UNSIGNED, $this->options) && 
                  $this->options[self::UNSIGNED] === true);
    }

    /**
     * Перевіряє чи стовпець може мати значення NULL
     *
     * @return bool
     */
    public function isNullable()
    {
        return (array_key_exists(self::NULLABLE, $this->options) && 
                  $this->options[self::NULLABLE] === true);
    }

    /**
     * Перевіряє чи стовпець має дефолтні значення
     *
     * @return bool
     */
    public function hasDefault()
    {
        return array_key_exists(self::DEFAULT_VALUE, $this->options);
    }

    /**
     * Повертає дефолтні значення стовпця
     *
     * @return mixed
     */
    public function getDefault()
    {
        if (!$this->hasDefault()) {
            return null;
        }
        if ($this->options[self::DEFAULT_VALUE] == 'NULL') {
            return null;
        }
        return $this->options[self::DEFAULT_VALUE];
    }
}