<?php 
namespace izi\product\migrations;
use yii\db\Migration;
use Yii;

class eav_entity_type extends Migration
{
    public $tableName = 'eav_entity_type';

    public function up()
    {
        
        $sql = file_get_contents(__DIR__ . "/sql/{$this->tableName}.sql");
        $this->execute($sql);
    }


    public function down()
    {
        $tableSchema = Yii::$app->db->schema->getTableSchema($this->tableName);

        if($tableSchema !== null){
            $this->dropTable($this->tableName);
        }
        
    }
}