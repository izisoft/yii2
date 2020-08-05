<?php
namespace izi\db;
use Yii;
use yii\db\Query;
/**
 * Cookie represents information related with a cookie, such as [[name]], [[value]], [[domain]], etc.
 *
 * For more details and usage information on Cookie, see the [guide article on handling cookies](guide:runtime-sessions-cookies).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Connection extends \yii\db\Connection
{
	public $commandClass = 'izi\db\Command';
	
	public $commandMap = [
			'pgsql' => 'izi\db\Command', // PostgreSQL
			'mysqli' => 'izi\db\Command', // MySQL
			'mysql' => 'izi\db\Command', // MySQL
			'sqlite' => 'yii\db\sqlite\Command', // sqlite 3
			'sqlite2' => 'yii\db\sqlite\Command', // sqlite 2
			'sqlsrv' => 'izi\db\Command', // newer MSSQL driver on MS Windows hosts
			'oci' => 'izi\db\Command', // Oracle driver
			'mssql' => 'izi\db\Command', // older MSSQL driver on MS Windows hosts
			'dblib' => 'izi\db\Command', // dblib drivers on GNU/Linux (and maybe other OSes) hosts
			'cubrid' => 'izi\db\Command', // CUBRID
	];
	
	
	public function createCommand($sql = null, $params = [])
	{
		//writeFile("SQL: " . $sql);
		$driver = $this->getDriverName();
		$config = ['class' => 'izi\db\Command'];
		if ($this->commandClass !== $config['class']) {
			$config['class'] = $this->commandClass;
		} elseif (isset($this->commandMap[$driver])) {
			$config = !is_array($this->commandMap[$driver]) ? ['class' => $this->commandMap[$driver]] : $this->commandMap[$driver];
		}
		$config['db'] = $this;
		$config['sql'] = $sql;
		/** @var Command $command */
		$command = Yii::createObject($config);
		return $command->bindValues($params);
	}
	/**
	 * 
	 */
	
	public function getConfigs($code = false, $lang = __LANG__,$sid=__SID__,$cached=true, $required = false){
		$langx = $lang == false ? 'all' : $lang;
		$code = $code !== false ? $code : 'SITE_CONFIGS';
		$config = Yii::$app->session->get('config');
		if(!YII_DEBUG && $cached && !isset($config['adLogin']) && isset($config['preload'][$code.$sid][$langx])
		    && !empty($config['preload'][$code.$sid][$langx])){
		        return $config['preload'][$code.$sid][$langx];
		}
		//
		$query = (new Query())->select(['a.bizrule'])->from(['a'=>'{{%site_configs}}'])
		->where(['a.code'=>$code]);
		if($sid>0){
			$query->andWhere(['a.sid'=>$sid]);
		}elseif ($required || $sid == -1){
		    $query->andWhere(['a.sid'=>$sid]);
		}
		if($lang !== false){
			$query->andWhere(['a.lang'=>$lang]);
		}
		
		if($code == 'LANGUAGE'){
		    //view2($query->createCommand()->getRawSql());
		}
		
		$j = $query->scalar();
		if($code == 'VERSION'){
			
		}
		$l = json_decode($j,true);
		switch ($code){
			case 'SITEMAP':
			case 'VERSION': 
			    break;
			
			default:
			    $config['preload'][$code.$sid][$langx] = $l;
				Yii::$app->session->set('config', $config);
				break;
		}
		return $l;
	}
	
	
	public function updateJsonData($table , $data , $condition, $field = 'bizrule')
	{
	    $columns = Yii::$app->db->getTableSchema($table)->columnNames;
	    foreach ($columns as $col){
	        if(isset($data[$col])){
	            unset($data[$col]);
	        }
	    }
	    
	    
	    
	    Yii::$app->db->createCommand()->update($table, [$field => json_encode($data, JSON_UNESCAPED_UNICODE)] ,$condition)->execute();
	    
	}
	
	public function updateBizrule($table , $data , $condition){
		
		$b = (new \yii\db\Query())->select('bizrule')->from(['a'=>$table])->where($condition)->one();
		if(isset($b['bizrule']) && $b['bizrule'] != ""){
			$b = json_decode($b['bizrule'],1);
		}
		if(is_array($b) && count($b) == 1 && isset($b['bizrule'])){
			$b = [];
		}
		
		if(is_array($data)){
			if(!empty($data)){
				foreach ($data as $k=>$v){
					$b[$k] = $v;
				}
			}
			if((new \yii\db\Query())->from($table)->where($condition)->count(1) == 0){
				$condition['bizrule']=json_encode($b);
				return $this->createCommand()->insert($table,$condition)->execute();
			}
			//view($b);
			return $this->createCommand()->update($table,['bizrule'=>json_encode($b)],$condition)->execute();
		}
	}
	
	
	public function updateBizData($biz = [],$con = [], $replace = false){
		$table = '{{%site_configs}}';
		$b = (new Query())->select('bizrule')->from(['a'=>$table])->where($con)->one();
		
		if(isset($b['bizrule']) && $b['bizrule'] != ""){
			$b = json_decode($b['bizrule'],1);
		}
		
		if($replace){
			$b = [];
		}
		
		if(is_array($b) && count($b) == 1 && isset($b['bizrule'])){ 
			$b = [];
		}
		if(is_array($biz)){
			if(!empty($biz)){
				foreach ($biz as $k=>$v){
					$b[$k] = $v;
				}
			}
			if((new Query())->from($table)->where($con)->count(1) == 0){
				$con['bizrule']=json_encode($b);
				return $this->createCommand()->insert($table,$con)->execute();
			}
			return $this->createCommand()->update($table,['bizrule'=>json_encode($b)],$con)->execute();
		}
	}
	
	public function copyRows($table, $fromCondition, $toCondition, $params = []){
	    $limit = isset($params['limit']) && $params['limit']>0 ? $params['limit'] : 0;
	    $auto_increment = isset($params['auto_increment']) ? $params['auto_increment'] : ['id'];
	    $l = (new \yii\db\Query())->from($table)->where($fromCondition)->createCommand()->queryAll(null, false);
	    if(!empty($l)){
	        foreach ($l as $v){
	            //
	            if(!empty($auto_increment)){
	                foreach ($auto_increment as $x){
	                    if(isset($v[$x])){
	                        unset($v[$x]);
	                    }
	                }
	            }
	            // 
	            if(!empty($toCondition)){
    	            foreach ($toCondition as $kt=>$vt){
    	                $v[$kt] = $vt;
    	            }	            
	            }
	            
	            $id = Yii::$app->zii->insert($table, $v, !empty($auto_increment) ? $auto_increment[0] : 'id');
	            return $id;
	        }
	    }
	}
	
}