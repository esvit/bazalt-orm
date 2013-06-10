<?php
/**
 * Models.php
 *
 * @category   System
 * @package    ORM
 * @subpackage Generator
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

/**
 * Генератор файлів моделей бази даних
 *
 * @category   System
 * @package    ORM
 * @subpackage Generator
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */ 
class ORM_Generator_Models extends Object
{
    protected $shema;
    
    protected static $relations = array();

    const BASECLASSES_DIR = 'Base';

    /**
     * Об'єкт з'єднання з БД
     *
     * @var ORM_Connection_Abstract
     */
    protected $connection;
    
    /**
     * Префікс, який буде додано до моделі (напр. ComArticle)
     *
     * @var string
     */
    protected $prefix = null;
    
    /**
     * Повертає префікс, який буде додано до моделі (напр. ComArticle), якщо не задано $this->prefix, генератор спробує підібрати префікс самостійно
     *
     * @params string $tableName Назва таблиці
     *
     * @return string Префікс
     */
    protected function getPrefix($tableName)
    {
        if($this->prefix) {
            return $this->prefix;
        }
        $tmp = explode('_', $tableName);
        return ucfirst(DataType_String::toCamelCase($tmp[0].'_'.$tmp[1]));
    }

    /**
     * Створює структуру папок, необхідну для моделей
     *
     * @return void
     */
    protected function createFolders($path)
    {
        if (!is_dir($path)) {
            if (!mkdir($path)) {
                throw new Exception('Cannot create dir for models');
            }
        }
        if (!is_dir($path . '/' . self::BASECLASSES_DIR)) {
            if (!mkdir($path . '/' . self::BASECLASSES_DIR)) {
                throw new Exception('Cannot create dir for models');
            }
        }
    }

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Генерує моделі з БД для заданого з'єднання
     *
     * @param ORM_Connection_Abstract $connection Об'єкт з'єднання
     * @param string                  $path       Шлях для збереження згенерованих моделей
     * @param string                  $table      Назва таблиці для якої необхідно згенерувати модель, якщо не вказано - генерує для всіх
     * @param string                  $prefix     Префікс, який буде додано до моделі (напр. ComArticle), якщо не вказано, генератор спробує підібрати префікс самостійно
     *
     * @return void
     */
    public function generateFromDb(ORM_Connection_Abstract $connection = null, $path, $table = null, $prefix = null)
    {
        //$this->OnBeforeGenerate();
        if($prefix) {
            $this->prefix = $prefix;
        }

        try {
            $this->shema = $connection->getConnectionAdapter()->getDatabase();
            
            #створює папки для моделей
            $this->createFolders($path);
            
            $this->connection = $connection;
            #витягує всі таблиці з бази
            if (empty($table) || $table == '*') {
                $q = new ORM_Query('SHOW TABLES;');
            } else {
                $q = new ORM_Query('SHOW TABLES LIKE \'' . $table . '\';');
            }
            $q->connection($this->connection);
            $tables = $q->fetchAll(); 

            #генерує масив звязків з ключів таблиць
            foreach ($tables as $table) {
                $tableName = current(get_object_vars($table));
                echo 'Table "' . $tableName . "\"\n";
                $this->getRelations($tableName);
            }
            // print_r(self::$relations);
            // exit;
            
            // $fileName = $path . '/' . self::BASECLASSES_DIR . '/baserecord.class.inc';
            // if( !file_exists($fileName) ) {
                // $baseContent = $this->generateBaseRecordFileContent();                
                // file_put_contents($fileName, $baseContent);
            // }
            
            #генерує контент моделей
            foreach ($tables as $table) {
                $tableName = current(get_object_vars($table));
                #витягує всі стовпці з таблиці
                $q = new ORM_Query('SHOW FULL COLUMNS FROM `' . $tableName . '`;');
                $q->connection($this->connection);
                $columns = $q->fetchAll(); 
                
                $res = $this->getColumnsMeta($columns);
                $name = $this->getModelName($tableName);
                
                // Base class
                $baseContent = $this->generateBaseFileContent($name, $tableName, $res['fields'], $res['keys']);
                $fileName = $path . '/' . self::BASECLASSES_DIR . '/' . $name . '.php';
                file_put_contents($fileName, $baseContent);
                // exit;

                // Model class
                $fileName = $path . '/' . $name . '.php';
                if( !file_exists( $fileName ) ) {
                    $content = $this->generateFileContent($name, $tableName, $res['fields']);            
                    file_put_contents($fileName, $content);
                }
            }

            //$this->OnGenerateComplete(Console::OK_STATUS);
        } catch(Exception $e) {
            print $e->getMessage();
            //$this->OnGenerateComplete(Console::FAILED_STATUS);
        }
    }

    /**
     * Генерує контент класу моделі
     *
     * @param string $className Назва моделі
     * @param string $tableName Назва таблиці
     * @param array  $fields    Масив розпарсених совпців таблиці
     *
     * @return string Контент класу моделі
     */
    protected function generateFileContent($className, $tableName, $fields)
    {
        $content = '<?php' . "\n";
        $content .= $this->getFileDocComment($className);
        $content .= $this->getClassDocComment($tableName, $fields);
        
        $content .= 'class '.$this->getPrefix($tableName).'_Model_' . $className . ' extends '.$this->getPrefix($tableName).'_Model_Base_' . $className  . "\n";
        $content .= '{' . "\n";
        $content .= '}' . "\n";
        return $content;
    }


    /**
     * Генерує контент базового класу моделі
     *
     * @param string $className Назва моделі
     * @param string $tableName Назва таблиці
     * @param string $fields    Масив розпарсених совпців таблиці
     * @param array  $keys      Масив розпарсених ключів таблиці
     *
     * @return string Контент базового класу моделі
     */
    public function generateBaseFileContent($className, $tableName, $fields, $keys)
    {
        // $className = ucfirst($tableName);
        $content = '<?php' . "\n";
        $content .= $this->getFileDocComment($className);
        $content .= $this->getClassDocComment($tableName, $fields);

        $content .= 'abstract class '.$this->getPrefix($tableName).'_Model_Base_' . $className . ' extends CMS_Model_Base_Record' . "\n";
        $content .= '{' . "\n";
        $content .= '    const TABLE_NAME = \'' . $tableName . '\';' . "\n\n";
        $content .= '    const MODEL_NAME = \'' . $this->getPrefix($tableName).'_Model_' . $className . '\';' . "\n\n";
        $content .= '    public function __construct()' . "\n";
        $content .= '    {' . "\n";
        $content .= '        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);' . "\n";
        $content .= '    }' . "\n\n";
        $content .= '    protected function initFields()' . "\n";
        $content .= '    {' . "\n";
        foreach ($fields as $fieldName => $field) {
        
            $primary = ( in_array( $fieldName, $keys ) );
            $options = $this->getFieldOptionsText($field, $primary);

            $content .= '        $this->hasColumn(\'' . $fieldName . '\', \'' . $options . '\');';
            if ($field['comment']) {
                $content .= ' // ' . $field['comment'];
            }
            $content .= "\n";
        }
        $content .= '    }' . "\n\n";
        $content .= $this->generateRelations($className);

        $content .= "\n";
        
        $content .= '    public function getById($id)' . "\n";
        $content .= '    {' . "\n";
        $content .= '        return parent::getRecordById($id, self::MODEL_NAME);' . "\n";
        $content .= '    }' . "\n\n";
        $content .= '    public function getAll($limit = null)' . "\n";
        $content .= '    {' . "\n";
        $content .= '        return parent::getAllRecords($limit, self::MODEL_NAME);' . "\n";
        $content .= '    }' . "\n\n";
        $content .= '    public function select($fields = null)' . "\n";
        $content .= '    {' . "\n";
        $content .= '        return ORM::select(self::MODEL_NAME, $fields);' . "\n";
        $content .= '    }' . "\n\n";
        $content .= '    public function insert($fields = null)' . "\n";
        $content .= '    {' . "\n";
        $content .= '        return ORM::insert(self::MODEL_NAME, $fields);' . "\n";
        $content .= '    }' . "\n\n";

        $content .= '}';
        //print_r(self::$relations); print "\n";
        return $content;
    }

    /**
     * Розпарсює масив стовпців
     */
    protected function getColumnsMeta($columns)
    {
        // Clear any previous column/field info
        $fields = array();
        $fieldMeta = array();
        $primaryKeys = array();

        foreach ($columns as $key => $col) {
            // Insert into fields array
            $colname = $col->Field;
            $fields[$colname] = $col;
            if($col->Key == 'PRI') {
                $primaryKeys[] = $colname;
            }

            // Set field types            
            $colType = $this->parseColumnType($col->Type);
            if($col->Null == 'YES') {
                $colType['nullable'] = true;
            }
            if($col->Extra == 'auto_increment') {
                $colType['auto_increment'] = true;
            }
            if($col->Default != null) {
                $colType['default'] = $col->Default;
            }
            if($col->Comment != null) {
                $colType['comment'] = $col->Comment;
            }
            if($col->Privileges != null) {
                $colType['privileges'] = $col->Privileges;
            }
            if (isset($colType['attributes']) && in_array('unsigned', $colType['attributes'])) {
                $colType['unsigned'] = true;
            }
            $fieldMeta[$colname] = $colType;
        }
        return array(
            'fields' => $fieldMeta,
            'keys' => $primaryKeys
        );
    }    
    
    /**
     * Генерація коментарів для файлу моделі
     *
     * @param string $className Назва моделі
     *
     * @return string Коментарі для файлу моделі
     */
    public function getFileDocComment($className)
    {
        $content = '/**' . "\n";
        $content .= ' * ' . $className .',зрз'. "\n";
        $content .= ' *' . "\n";
        $content .= ' * @category  DataModels' . "\n";
        $content .= ' * @package   DataModel' . "\n";
        $content .= ' * @author    Bazalt CMS (http://bazalt-cms.com/)' . "\n";
        $content .= ' * @version   SVN: $' . 'Id$' . "\n";
        $content .= ' */' . "\n";
        return $content;
    }

    /**
     * Генерація коментарів для класу моделі
     *
     * @param string $tableName Назва таблиці
     * @param array  $fields    Масив розпарсених совпців таблиці
     *
     * @return string Коментарі для класу моделі
     */
    public function getClassDocComment($tableName, $fields)
    {
        $content  = '/**' . "\n";
        $content .= ' * Data model for table "' . $tableName . '"' . "\n";
        $content .= ' *' . "\n";
        $content .= ' * @category  DataModels' . "\n";
        $content .= ' * @package   DataModel' . "\n";
        $content .= ' * @author    Bazalt CMS (http://bazalt-cms.com/)' . "\n";
        $content .= ' * @version   Release: $' . 'Revision$' . "\n";
        $content .= ' *' . "\n";

        foreach ($fields as $fieldName => $field) {
            $content .= ' * @property-read ' . $field['type'] . ' $' . $fieldName;
            if ($field['comment']) {
                $content .= ' ' . $field['comment'];
            }
            $content .= "\n";
        }
        $content .= ' */' . "\n";
        return $content;
    }

    /**
     * Повертає набір параметрів, які описують поле в БД
     */
    protected function getFieldOptionsText($field, $primary)
    {
        $mainOptions = '';
        $options = '';

        # Основний ключ
        if ($primary) {
            $mainOptions .= 'P';
        }
        if ($field['unsigned']) {
            $mainOptions .= 'U';
        }

        if ($field['auto_increment']) {
            $mainOptions .= 'A';
        }

        # Чи може приймати null
        if ($field['nullable']) {
            $mainOptions .= 'N';
        }

        # Тип поля у БД
        $options = $field['type'];

        # Ширина поля
        if ($field['length']) {
            $options .= '(' . intval($field['length']) . ')';
        }

        # Значення, які може приймати поле (для enum, set)
        /*if ($field['values']) {
            $option[] = '"values" => array(' . implode(',', $field['values']) . ')';
        }*/

        # Значення по замовчуванню
        if (isset($field['default'])) {
            //$option[] = '"default" => "'.$field['default'].'"';
            $options .= '|' . $field['default'];
        }

        if (!empty($mainOptions)) {
            $options = $mainOptions . ':' . $options;
        }
        return $options;
    }

    /**
     * Генерує код функції для ініціалізації звязків
     */
    protected function generateRelations($modelName)
    {
        $content = '';

        $content .= '    public function initRelations()'. "\n";
        $content .= '    {'. "\n";
        if (count(self::$relations[$modelName]) > 0) {
            foreach (self::$relations[$modelName] as $refName => $ref) {
                $content .= '        $this->hasRelation(\'' . $refName . '\', ' . 
                                                              $ref . ');' . "\n";
            }
        }
        $content .= $refss;
        $content .= '    }'. "\n";
        
        return $content;
    }

    /**
     * Завантажує інформацію про звязки з БД
     */
    public function getRelations($tableName)
    {
        $q = new ORM_Query('
        SELECT
            CONSTRAINT_NAME,
            REFERENCED_COLUMN_NAME AS `column`,
            TABLE_NAME AS `refTable`,
            COLUMN_NAME AS `refColumn`
        FROM
            information_schema.KEY_COLUMN_USAGE
        WHERE
            CONSTRAINT_SCHEMA = \'' . $this->shema . '\'
            AND REFERENCED_TABLE_NAME = \'' . $tableName . '\'
            AND REFERENCED_TABLE_SCHEMA IS NOT NULL');
    
        $res = $q->fetchAll();
        if (count($res) > 0) {
            foreach ($res as &$refs) {
                $this->getRelationType($tableName, $refs);                
            }
        }
    }

    /**
     * Повертає тип звязку
     */
    protected function getRelationType($tableName, $relation)
    {
        $q = new ORM_Query('
        SELECT
            CONSTRAINT_NAME as `constrName`,
            REFERENCED_COLUMN_NAME AS `column`,
            REFERENCED_TABLE_NAME AS `refTable`,
            COLUMN_NAME AS `refColumn`
        FROM
            information_schema.KEY_COLUMN_USAGE
        WHERE 
            CONSTRAINT_SCHEMA = \''.$this->shema.'\'
            AND TABLE_NAME = \''.$relation->refTable.'\'
            AND REFERENCED_TABLE_SCHEMA IS NOT NULL
            AND REFERENCED_TABLE_NAME != \''.$tableName.'\'');
        
        $res = $q->fetch();

        $refModel = $this->getModelName($res->refTable);
        $refTable = $this->getModelName($relation->refTable);
        $tabName = $this->getModelName($tableName);
        
        $column = $res->refColumn;
        $relColumn = $relation->refColumn;

        if (!array_key_exists($refModel,self::$relations)) {
            self::$relations[$refModel] = array();
        }

        if (!array_key_exists($refTable,self::$relations)) {
            self::$relations[$refTable] = array();
        }

        if ($res && $res->constrName == $res->refColumn) {
            #Генеруємо звязок Many2Many
            self::$relations[ $tabName ][$refModel] = 'new ORM_Relation_Many2Many(\''.$this->getPrefix($refModel).'_Model_' . $refModel . '\', \'' . $relColumn . '\', \''.$this->getPrefix($refTable).'_Model_'.$refTable.'\', \''. $column .'\')';
        } else {
            #Генеруємо звязоки ORMRelationOne2Many / ORMRelationOne2One одразу в двох таблицях
            if( $this->isUnique($relation->refTable, $relation->refColumn) ) {
                #Генеруємо прямий звязок - ORMRelationOne2One
                self::$relations[ $tabName ][$refTable] = 'new ORM_Relation_One2One(\''.$this->getPrefix($refTable).'_Model_'.$refTable.'\', \'' . $relation->column . '\',  \''. $relColumn .'\')';
                #Якщо поле по якому йде звязок унікальне
                if( $this->isUnique($tableName, $relation->refColumn) ) {
                    #Генеруємо зворотній звязок - ORMRelationOne2One
                    self::$relations[ $refTable ][$tabName] = 'new ORM_Relation_One2One(\''.$this->getPrefix($tabName).'_Model_'.$tabName.'\', \'' . $relColumn . '\',  \''. $relation->column .'\')';
                } else {
                    #Генеруємо зворотній звязок - ORMRelationOne2Many
                    self::$relations[ $refTable ][$tabName] = 'new ORM_Relation_One2Many(\''.$this->getPrefix($tabName).'_Model_'.$tabName.'\', \'' . $relColumn . '\',  \''. $relation->column .'\')';
                }
            } else {
                #Генеруємо прямий звязок - ORMRelationOne2Many
                self::$relations[ $tabName ][$refTable] = 'new ORM_Relation_One2Many(\''.$this->getPrefix($refTable).'_Model_'.$refTable.'\', \'' . $relation->column . '\', \''. $relColumn .'\')';
                #Генеруємо зворотній звязок - ORMRelationOne2One
                self::$relations[ $refTable ][$tabName] = 'new ORM_Relation_One2One(\''.$this->getPrefix($tabName).'_Model_'.$tabName.'\', \'' . $relColumn . '\',  \''. $relation->column .'\')';
            }
        }        
    }
    
    protected function getModelName($tableName)
    {
        $tmp = explode('_', $tableName);
        if(count($tmp) == 1) {
            return ucfirst($tableName);
        }
        unset($tmp[0]);
        if(count($tmp) > 2) {
            unset($tmp[1]);
        }
        return ucfirst(DataType_String::toCamelCase(implode('_', $tmp)));
    }

    /**
     * Перевіряє унікальність ключа
     */
    protected function isUnique($tableName, $column)
    {
        $q = new ORM_Query('
        SELECT 
            COLUMN_KEY as `key`
        FROM 
            information_schema.COLUMNS
        WHERE 
            TABLE_SCHEMA = \'' . $this->shema . '\'
            AND TABLE_NAME = \'' . $tableName . '\'
            AND COLUMN_NAME = \'' . $column . '\'');
        $res = $q->fetch();
        return ($res->key == 'UNI');
    }
    
    /**
     * Parse PDO-produced column type
     */    
    protected function parseColumnType($colType)
    {
        $colInfo = array();
        $colParts = explode(' ', $colType);
        if ($fparen = strpos($colParts[0], '(')) {
            $colInfo['type'] = substr($colParts[0], 0, $fparen);
            $values = str_replace(')', '', substr($colParts[0], $fparen+1));
            if ($colInfo['type'] == 'enum' || $colInfo['type'] == 'set') {
                $colInfo['values'] = explode(',', $values);
                foreach ($colInfo['values'] as $k => $i) {
                    $colInfo['values'][$k] = str_replace("''", "\\'", $i);
                }
            } else {
                $colInfo['length'] = $values;
            }
            if (count($colParts) > 1) {
                $colInfo['attributes'][] = $colParts[1];
            }
            if (count($colParts) > 2) {
                $colInfo['attributes'][] = $colParts[2];
            }
        } else {
            $colInfo['type'] = $colParts[0];
        }

        return $colInfo;
    }
}