<?php 
namespace izi\product\migrations;
use yii\db\Migration;
use Yii;

class catalog_product_entity_gallery extends Migration
{
    public $tableName = 'catalog_product_entity_gallery';

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