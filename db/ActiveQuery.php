<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\db;

use yii\base\InvalidConfigException;
 
class ActiveQuery extends \yii\db\ActiveQuery
{
     
     
    /**
     * Executes query and returns all results as an array.
     * @param Connection $db the DB connection used to create the DB command.
     * If null, the DB connection returned by [[modelClass]] will be used.
     * @return array|ActiveRecord[] the query results. If the query results in nothing, an empty array will be returned.
     */
    public function all($db = null)
    {
        return parent::all($db);
    }
    
    /**
     * {@inheritdoc}
     */
     
    
    /**
     * {@inheritdoc}
     */
    public function populate($rows)
    {
        if (empty($rows)) {
            return [];
        }
        
        $models = $this->createModels($rows);
        if (!empty($this->join) && $this->indexBy === null) {
            $models = $this->removeDuplicatedModels($models);
        }
        if (!empty($this->with)) {
            $this->findWith($this->with, $models);
        }
        
        if ($this->inverseOf !== null) {
            $this->addInverseRelations($models);
        }
        
        if (!$this->asArray) {
            foreach ($models as $model) {
                $model->afterFind();
            }
        }
        
        return parent::populate($models);
    }
    
    /**
     * Removes duplicated models by checking their primary key values.
     * This method is mainly called when a join query is performed, which may cause duplicated rows being returned.
     * @param array $models the models to be checked
     * @throws InvalidConfigException if model primary key is empty
     * @return array the distinctive models
     */
     
    
    /**
     * Executes query and returns a single row of result.
     * @param Connection|null $db the DB connection used to create the DB command.
     * If `null`, the DB connection returned by [[modelClass]] will be used.
     * @return ActiveRecord|array|null a single row of query result. Depending on the setting of [[asArray]],
     * the query result may be either an array or an ActiveRecord object. `null` will be returned
     * if the query results in nothing.
     */
    public function one($db = null)
    {
        $row = parent::one($db, false);
        if ($row !== false) {
            $models = $this->populate([$row]);
            return reset($models) ?: null;
        }
        
        return null;
    }
    
    /**
     * Creates a DB command that can be used to execute this query.
     * @param Connection|null $db the DB connection used to create the DB command.
     * If `null`, the DB connection returned by [[modelClass]] will be used.
     * @return Command the created DB command instance.
     */
     
    /**
     * {@inheritdoc}
     */
    protected function queryScalar($selectExpression, $db)
    {
        /* @var $modelClass ActiveRecord */
        $modelClass = $this->modelClass;
        if ($db === null) {
            $db = $modelClass::getDb();
        }
        
        if ($this->sql === null) {
            return parent::queryScalar($selectExpression, $db);
        }
        
        $command = (new Query())->select([$selectExpression])
        ->from(['c' => "({$this->sql})"])
        ->params($this->params)
        ->createCommand($db);
        $this->setCommandCache($command);
        
        return $command->queryScalar();
    }
    
    /**
     * Joins with the specified relations.
     *
     * This method allows you to reuse existing relation definitions to perform JOIN queries.
     * Based on the definition of the specified relation(s), the method will append one or multiple
     * JOIN statements to the current query.
     *
     * If the `$eagerLoading` parameter is true, the method will also perform eager loading for the specified relations,
     * which is equivalent to calling [[with()]] using the specified relations.
     *
     * Note that because a JOIN query will be performed, you are responsible to disambiguate column names.
     *
     * This method differs from [[with()]] in that it will build up and execute a JOIN SQL statement
     * for the primary table. And when `$eagerLoading` is true, it will call [[with()]] in addition with the specified relations.
     *
     * @param string|array $with the relations to be joined. This can either be a string, representing a relation name or
     * an array with the following semantics:
     *
     * - Each array element represents a single relation.
     * - You may specify the relation name as the array key and provide an anonymous functions that
     *   can be used to modify the relation queries on-the-fly as the array value.
     * - If a relation query does not need modification, you may use the relation name as the array value.
     *
     * The relation name may optionally contain an alias for the relation table (e.g. `books b`).
     *
     * Sub-relations can also be specified, see [[with()]] for the syntax.
     *
     * In the following you find some examples:
     *
     * ```php
     * // find all orders that contain books, and eager loading "books"
     * Order::find()->joinWith('books', true, 'INNER JOIN')->all();
     * // find all orders, eager loading "books", and sort the orders and books by the book names.
     * Order::find()->joinWith([
     *     'books' => function (\yii\db\ActiveQuery $query) {
     *         $query->orderBy('item.name');
     *     }
     * ])->all();
     * // find all orders that contain books of the category 'Science fiction', using the alias "b" for the books table
     * Order::find()->joinWith(['books b'], true, 'INNER JOIN')->where(['b.category' => 'Science fiction'])->all();
     * ```
     *
     * The alias syntax is available since version 2.0.7.
     *
     * @param bool|array $eagerLoading whether to eager load the relations
     * specified in `$with`.  When this is a boolean, it applies to all
     * relations specified in `$with`. Use an array to explicitly list which
     * relations in `$with` need to be eagerly loaded.  Note, that this does
     * not mean, that the relations are populated from the query result. An
     * extra query will still be performed to bring in the related data.
     * Defaults to `true`.
     * @param string|array $joinType the join type of the relations specified in `$with`.
     * When this is a string, it applies to all relations specified in `$with`. Use an array
     * in the format of `relationName => joinType` to specify different join types for different relations.
     * @return $this the query object itself
     */
    
    
     
    
    /**
     * Inner joins with the specified relations.
     * This is a shortcut method to [[joinWith()]] with the join type set as "INNER JOIN".
     * Please refer to [[joinWith()]] for detailed usage of this method.
     * @param string|array $with the relations to be joined with.
     * @param bool|array $eagerLoading whether to eager load the relations.
     * Note, that this does not mean, that the relations are populated from the
     * query result. An extra query will still be performed to bring in the
     * related data.
     * @return $this the query object itself
     * @see joinWith()
     */
    public function innerJoinWith($with, $eagerLoading = true)
    {
        return $this->joinWith($with, $eagerLoading, 'INNER JOIN');
    }
    
    /**
     * Modifies the current query by adding join fragments based on the given relations.
     * @param ActiveRecord $model the primary model
     * @param array $with the relations to be joined
     * @param string|array $joinType the join type
     */
    private function joinWithRelations($model, $with, $joinType)
    {
        $relations = [];
        
        foreach ($with as $name => $callback) {
            if (is_int($name)) {
                $name = $callback;
                $callback = null;
            }
            
            $primaryModel = $model;
            $parent = $this;
            $prefix = '';
            while (($pos = strpos($name, '.')) !== false) {
                $childName = substr($name, $pos + 1);
                $name = substr($name, 0, $pos);
                $fullName = $prefix === '' ? $name : "$prefix.$name";
                if (!isset($relations[$fullName])) {
                    $relations[$fullName] = $relation = $primaryModel->getRelation($name);
                    $this->joinWithRelation($parent, $relation, $this->getJoinType($joinType, $fullName));
                } else {
                    $relation = $relations[$fullName];
                }
                /* @var $relationModelClass ActiveRecordInterface */
                $relationModelClass = $relation->modelClass;
                $primaryModel = $relationModelClass::instance();
                $parent = $relation;
                $prefix = $fullName;
                $name = $childName;
            }
            
            $fullName = $prefix === '' ? $name : "$prefix.$name";
            if (!isset($relations[$fullName])) {
                $relations[$fullName] = $relation = $primaryModel->getRelation($name);
                if ($callback !== null) {
                    call_user_func($callback, $relation);
                }
                if (!empty($relation->joinWith)) {
                    $relation->buildJoinWith();
                }
                $this->joinWithRelation($parent, $relation, $this->getJoinType($joinType, $fullName));
            }
        }
    }
    
    /**
     * Returns the join type based on the given join type parameter and the relation name.
     * @param string|array $joinType the given join type(s)
     * @param string $name relation name
     * @return string the real join type
     */
    private function getJoinType($joinType, $name)
    {
        if (is_array($joinType) && isset($joinType[$name])) {
            return $joinType[$name];
        }
        
        return is_string($joinType) ? $joinType : 'INNER JOIN';
    }
    
    /**
     * Returns the table name and the table alias for [[modelClass]].
     * @return array the table name and the table alias.
     * @internal
     */
    private function getTableNameAndAlias()
    {
        if (empty($this->from)) {
            $tableName = $this->getPrimaryTableName();
        } else {
            $tableName = '';
            foreach ($this->from as $alias => $tableName) {
                if (is_string($alias)) {
                    return [$tableName, $alias];
                }
                break;
            }
        }
        
        if (preg_match('/^(.*?)\s+({{\w+}}|\w+)$/', $tableName, $matches)) {
            $alias = $matches[2];
        } else {
            $alias = $tableName;
        }
        
        return [$tableName, $alias];
    }
    
    /**
     * Joins a parent query with a child query.
     * The current query object will be modified accordingly.
     * @param ActiveQuery $parent
     * @param ActiveQuery $child
     * @param string $joinType
     */
    private function joinWithRelation($parent, $child, $joinType)
    {
        $via = $child->via;
        $child->via = null;
        if ($via instanceof self) {
            // via table
            $this->joinWithRelation($parent, $via, $joinType);
            $this->joinWithRelation($via, $child, $joinType);
            return;
        } elseif (is_array($via)) {
            // via relation
            $this->joinWithRelation($parent, $via[1], $joinType);
            $this->joinWithRelation($via[1], $child, $joinType);
            return;
        }
        
        list($parentTable, $parentAlias) = $parent->getTableNameAndAlias();
        list($childTable, $childAlias) = $child->getTableNameAndAlias();
        
        if (!empty($child->link)) {
            if (strpos($parentAlias, '{{') === false) {
                $parentAlias = '{{' . $parentAlias . '}}';
            }
            if (strpos($childAlias, '{{') === false) {
                $childAlias = '{{' . $childAlias . '}}';
            }
            
            $on = [];
            foreach ($child->link as $childColumn => $parentColumn) {
                $on[] = "$parentAlias.[[$parentColumn]] = $childAlias.[[$childColumn]]";
            }
            $on = implode(' AND ', $on);
            if (!empty($child->on)) {
                $on = ['and', $on, $child->on];
            }
        } else {
            $on = $child->on;
        }
        $this->join($joinType, empty($child->from) ? $childTable : $child->from, $on);
        
        if (!empty($child->where)) {
            $this->andWhere($child->where);
        }
        if (!empty($child->having)) {
            $this->andHaving($child->having);
        }
        if (!empty($child->orderBy)) {
            $this->addOrderBy($child->orderBy);
        }
        if (!empty($child->groupBy)) {
            $this->addGroupBy($child->groupBy);
        }
        if (!empty($child->params)) {
            $this->addParams($child->params);
        }
        if (!empty($child->join)) {
            foreach ($child->join as $join) {
                $this->join[] = $join;
            }
        }
        if (!empty($child->union)) {
            foreach ($child->union as $union) {
                $this->union[] = $union;
            }
        }
    }
    
    /**
     * Sets the ON condition for a relational query.
     * The condition will be used in the ON part when [[ActiveQuery::joinWith()]] is called.
     * Otherwise, the condition will be used in the WHERE part of a query.
     *
     * Use this method to specify additional conditions when declaring a relation in the [[ActiveRecord]] class:
     *
     * ```php
     * public function getActiveUsers()
     * {
     *     return $this->hasMany(User::className(), ['id' => 'user_id'])
     *                 ->onCondition(['active' => true]);
     * }
     * ```
     *
     * Note that this condition is applied in case of a join as well as when fetching the related records.
     * Thus only fields of the related table can be used in the condition. Trying to access fields of the primary
     * record will cause an error in a non-join-query.
     *
     * @param string|array $condition the ON condition. Please refer to [[Query::where()]] on how to specify this parameter.
     * @param array $params the parameters (name => value) to be bound to the query.
     * @return $this the query object itself
     */
    public function onCondition($condition, $params = [])
    {
        $this->on = $condition;
        $this->addParams($params);
        return $this;
    }
    
    /**
     * Adds an additional ON condition to the existing one.
     * The new condition and the existing one will be joined using the 'AND' operator.
     * @param string|array $condition the new ON condition. Please refer to [[where()]]
     * on how to specify this parameter.
     * @param array $params the parameters (name => value) to be bound to the query.
     * @return $this the query object itself
     * @see onCondition()
     * @see orOnCondition()
     */
    public function andOnCondition($condition, $params = [])
    {
        if ($this->on === null) {
            $this->on = $condition;
        } else {
            $this->on = ['and', $this->on, $condition];
        }
        $this->addParams($params);
        return $this;
    }
    
    /**
     * Adds an additional ON condition to the existing one.
     * The new condition and the existing one will be joined using the 'OR' operator.
     * @param string|array $condition the new ON condition. Please refer to [[where()]]
     * on how to specify this parameter.
     * @param array $params the parameters (name => value) to be bound to the query.
     * @return $this the query object itself
     * @see onCondition()
     * @see andOnCondition()
     */
    public function orOnCondition($condition, $params = [])
    {
        if ($this->on === null) {
            $this->on = $condition;
        } else {
            $this->on = ['or', $this->on, $condition];
        }
        $this->addParams($params);
        return $this;
    }
    
    /**
     * Specifies the junction table for a relational query.
     *
     * Use this method to specify a junction table when declaring a relation in the [[ActiveRecord]] class:
     *
     * ```php
     * public function getItems()
     * {
     *     return $this->hasMany(Item::className(), ['id' => 'item_id'])
     *                 ->viaTable('order_item', ['order_id' => 'id']);
     * }
     * ```
     *
     * @param string $tableName the name of the junction table.
     * @param array $link the link between the junction table and the table associated with [[primaryModel]].
     * The keys of the array represent the columns in the junction table, and the values represent the columns
     * in the [[primaryModel]] table.
     * @param callable $callable a PHP callback for customizing the relation associated with the junction table.
     * Its signature should be `function($query)`, where `$query` is the query to be customized.
     * @return $this the query object itself
     * @see via()
     */
    public function viaTable($tableName, $link, callable $callable = null)
    {
        $modelClass = $this->primaryModel !== null ? get_class($this->primaryModel) : __CLASS__;
        
        $relation = new self($modelClass, [
            'from' => [$tableName],
            'link' => $link,
            'multiple' => true,
            'asArray' => true,
        ]);
        $this->via = $relation;
        if ($callable !== null) {
            call_user_func($callable, $relation);
        }
        
        return $this;
    }
    
    /**
     * Define an alias for the table defined in [[modelClass]].
     *
     * This method will adjust [[from]] so that an already defined alias will be overwritten.
     * If none was defined, [[from]] will be populated with the given alias.
     *
     * @param string $alias the table alias.
     * @return $this the query object itself
     * @since 2.0.7
     */
    public function alias($alias)
    {
        if (empty($this->from) || count($this->from) < 2) {
            list($tableName) = $this->getTableNameAndAlias();
            $this->from = [$alias => $tableName];
        } else {
            $tableName = $this->getPrimaryTableName();
            
            foreach ($this->from as $key => $table) {
                if ($table === $tableName) {
                    unset($this->from[$key]);
                    $this->from[$alias] = $tableName;
                }
            }
        }
        
        return $this;
    }
    
    /**
     * {@inheritdoc}
     * @since 2.0.12
     */
    public function getTablesUsedInFrom()
    {
        if (empty($this->from)) {
            return $this->cleanUpTableNames([$this->getPrimaryTableName()]);
        }
        
        return parent::getTablesUsedInFrom();
    }
    
    /**
     * @return string primary table name
     * @since 2.0.12
     */
    protected function getPrimaryTableName()
    {
        /* @var $modelClass ActiveRecord */
        $modelClass = $this->modelClass;
        return $modelClass::tableName();
    }
}
