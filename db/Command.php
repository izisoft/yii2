<?php
namespace izi\db;
use Yii;
/**
 * Cookie represents information related with a cookie, such as [[name]], [[value]], [[domain]], etc.
 *
 * For more details and usage information on Cookie, see the [guide article on handling cookies](guide:runtime-sessions-cookies).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Command extends \yii\db\Command
{
	
	
	
	public function queryOne($fetchMode = null, $biz = true)
	{
		 
		$row = $this->queryInternal('fetch', $fetchMode);
		
		 
		 
		if($biz && isset($row['bizrule']) && ($content = json_decode($row['bizrule'],1)) != NULL){
			$row += $content;
			unset($row['bizrule']); 
			//view($content);
		}
		
		if($biz && isset($row['content']) && ($content = json_decode($row['content'],1)) != NULL){
		    $row += $content;
			unset($row['content']);
		}
				 
		return $row;
	}
	
	
	public function queryAOne($fetchMode = null)
	{
	    
	    $row = $this->queryInternal('fetch', $fetchMode);
	    
	    
	     
	    return $row;
	}
	
	public function queryAll($fetchMode = null , $biz = true)
	{
		
		$rows = $this->queryInternal('fetchAll', $fetchMode);			
		
		if($biz && !empty($rows)){
			foreach ($rows as $k=>$row){
				if(isset($row['bizrule']) && ($content = json_decode($row['bizrule'],1)) != NULL){
				    $row += $content;
					unset($row['bizrule']);
				}
				
				if(isset($row['content']) && ($content = json_decode($row['content'],1)) != NULL){
				    $row += $content;
					unset($row['content']);
				}				
				$rows[$k] = $row;
			}
		}
		return $rows;
	}
}