<?php 
namespace izi\product\migrations;
use yii\db\Migration;
use Yii;

class catalog_product_entity extends Migration
{
    public $tableName = 'catalog_product_entity';

    public function up()
    {
        $this->createTable($this->tableName, [
            'entity_id' => $this->integer()->unsigned()->comment("ID"), // \yii\db\Schema::TYPE_PK,
            'attribute_set_id' => $this->smallInteger()->unsigned()->notNull()->defaultValue(0)->comment("Attribute Set ID"),
            'type_id' => $this->string(32)->notNull()->defaultValue('simple')->comment("Type ID"),
            'type' => $this->string(32)->notNull()->defaultValue('text')->comment("Type"),
            'sku' => $this->string(64)->comment("Sku"),
            'has_options' => $this->smallInteger(6)->notNull()->defaultValue(0)->comment("Has Options"),
            'required_options' => $this->smallInteger()->unsigned()->notNull()->defaultValue(0)->comment("Required Options"),
            'created_at' => 'timestamp default current_timestamp comment \'Creation Time\'',
            'updated_at' => 'timestamp on update current_timestamp comment \'Update Time\'',

            'PRIMARY KEY (`entity_id`)'
        ]);
        $this->alterColumn($this->tableName, 'entity_id', 'INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT \'ID\'');
        $this->createIndex('CATALOG_PRODUCT_ENTITY_SKU', $this->tableName, 'sku');
        $this->createIndex('CATALOG_PRODUCT_ENTITY_ATTRIBUTE_SET_ID', $this->tableName, 'attribute_set_id');
        
    }


    public function down()
    {
        $tableSchema = Yii::$app->db->schema->getTableSchema($this->tableName);

        if($tableSchema !== null){
            $this->dropTable($this->tableName);
        }
        
    }
}