<?php
namespace izi\migration;

use yii\db\Migration;

class Sim extends Migration
{
    public function up()
    {
        $tableOptions = null;
//         if ($this->db->driverName === 'mysql') {
//             // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
//             $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
//         }

        $this->db = \Yii::$app->dbs;
//         $this->createTable('{{%cart}}', [
//             'item_id' => $this->integer()->notNull()->defaultValue(0)->unsigned(),
//             'quantity' => $this->integer()->notNull()->defaultValue(0)->unsigned(),
//             'customer_id' => $this->integer()->notNull()->defaultValue(0)->unsigned(),
//             'seller_id' => $this->integer()->notNull()->defaultValue(0)->unsigned(),
//             'sid' => $this->integer()->notNull()->defaultValue(0)->unsigned(),
//             'time' => $this->integer()->notNull()->defaultValue(0),
            
//         ], $tableOptions);
        
//         $this->addPrimaryKey('primary', 'cart', ['item_id', 'customer_id', 'seller_id', 'sid']);
        
//         $sql = "CREATE TABLE [sim_cache] (
// [id] VARCHAR(255)  NULL PRIMARY KEY,
// [partner_id] INTEGER  DEFAULT 0 NULL,
// [type_id] INTEGER  DEFAULT 0 NULL,
// [group_id] INTEGER  DEFAULT 0 NULL,
// [page] INTEGER  DEFAULT 0 NULL,
// [status] INTEGER DEFAULT -1 NULL,
// [json_data] TEXT  NULL,
// [updated_time] INTEGER  DEFAULT 0 NULL
// )";
        
//         $sql = "
// ALTER TABLE sim_cache 
// ADD COLUMN [page] INTEGER DEFAULT 0 NULL;";
//         $this->db->createCommand($sql)->execute();
        
    }

    public function down()
    {
//         $this->db = \Yii::$app->dbs;
//         $this->dropTable('{{%cart}}');
    }
}
