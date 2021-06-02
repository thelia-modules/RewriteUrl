<?php

namespace RewriteUrl\Model\Base;

use \Exception;
use \PDO;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\ModelJoin;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;
use RewriteUrl\Model\RewriteurlRuleParam as ChildRewriteurlRuleParam;
use RewriteUrl\Model\RewriteurlRuleParamQuery as ChildRewriteurlRuleParamQuery;
use RewriteUrl\Model\Map\RewriteurlRuleParamTableMap;

/**
 * Base class that represents a query for the 'rewriteurl_rule_param' table.
 *
 *
 *
 * @method     ChildRewriteurlRuleParamQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildRewriteurlRuleParamQuery orderByIdRule($order = Criteria::ASC) Order by the id_rule column
 * @method     ChildRewriteurlRuleParamQuery orderByParamName($order = Criteria::ASC) Order by the param_name column
 * @method     ChildRewriteurlRuleParamQuery orderByParamCondition($order = Criteria::ASC) Order by the param_condition column
 * @method     ChildRewriteurlRuleParamQuery orderByParamValue($order = Criteria::ASC) Order by the param_value column
 *
 * @method     ChildRewriteurlRuleParamQuery groupById() Group by the id column
 * @method     ChildRewriteurlRuleParamQuery groupByIdRule() Group by the id_rule column
 * @method     ChildRewriteurlRuleParamQuery groupByParamName() Group by the param_name column
 * @method     ChildRewriteurlRuleParamQuery groupByParamCondition() Group by the param_condition column
 * @method     ChildRewriteurlRuleParamQuery groupByParamValue() Group by the param_value column
 *
 * @method     ChildRewriteurlRuleParamQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildRewriteurlRuleParamQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildRewriteurlRuleParamQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildRewriteurlRuleParamQuery leftJoinRewriteurlRule($relationAlias = null) Adds a LEFT JOIN clause to the query using the RewriteurlRule relation
 * @method     ChildRewriteurlRuleParamQuery rightJoinRewriteurlRule($relationAlias = null) Adds a RIGHT JOIN clause to the query using the RewriteurlRule relation
 * @method     ChildRewriteurlRuleParamQuery innerJoinRewriteurlRule($relationAlias = null) Adds a INNER JOIN clause to the query using the RewriteurlRule relation
 *
 * @method     ChildRewriteurlRuleParam findOne(ConnectionInterface $con = null) Return the first ChildRewriteurlRuleParam matching the query
 * @method     ChildRewriteurlRuleParam findOneOrCreate(ConnectionInterface $con = null) Return the first ChildRewriteurlRuleParam matching the query, or a new ChildRewriteurlRuleParam object populated from the query conditions when no match is found
 *
 * @method     ChildRewriteurlRuleParam findOneById(int $id) Return the first ChildRewriteurlRuleParam filtered by the id column
 * @method     ChildRewriteurlRuleParam findOneByIdRule(int $id_rule) Return the first ChildRewriteurlRuleParam filtered by the id_rule column
 * @method     ChildRewriteurlRuleParam findOneByParamName(string $param_name) Return the first ChildRewriteurlRuleParam filtered by the param_name column
 * @method     ChildRewriteurlRuleParam findOneByParamCondition(string $param_condition) Return the first ChildRewriteurlRuleParam filtered by the param_condition column
 * @method     ChildRewriteurlRuleParam findOneByParamValue(string $param_value) Return the first ChildRewriteurlRuleParam filtered by the param_value column
 *
 * @method     array findById(int $id) Return ChildRewriteurlRuleParam objects filtered by the id column
 * @method     array findByIdRule(int $id_rule) Return ChildRewriteurlRuleParam objects filtered by the id_rule column
 * @method     array findByParamName(string $param_name) Return ChildRewriteurlRuleParam objects filtered by the param_name column
 * @method     array findByParamCondition(string $param_condition) Return ChildRewriteurlRuleParam objects filtered by the param_condition column
 * @method     array findByParamValue(string $param_value) Return ChildRewriteurlRuleParam objects filtered by the param_value column
 *
 */
abstract class RewriteurlRuleParamQuery extends ModelCriteria
{

    /**
     * Initializes internal state of \RewriteUrl\Model\Base\RewriteurlRuleParamQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'thelia', $modelName = '\\RewriteUrl\\Model\\RewriteurlRuleParam', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildRewriteurlRuleParamQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildRewriteurlRuleParamQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof \RewriteUrl\Model\RewriteurlRuleParamQuery) {
            return $criteria;
        }
        $query = new \RewriteUrl\Model\RewriteurlRuleParamQuery();
        if (null !== $modelAlias) {
            $query->setModelAlias($modelAlias);
        }
        if ($criteria instanceof Criteria) {
            $query->mergeWith($criteria);
        }

        return $query;
    }

    /**
     * Find object by primary key.
     * Propel uses the instance pool to skip the database if the object exists.
     * Go fast if the query is untouched.
     *
     * <code>
     * $obj  = $c->findPk(12, $con);
     * </code>
     *
     * @param mixed $key Primary key to use for the query
     * @param ConnectionInterface $con an optional connection object
     *
     * @return ChildRewriteurlRuleParam|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = RewriteurlRuleParamTableMap::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(RewriteurlRuleParamTableMap::DATABASE_NAME);
        }
        $this->basePreSelect($con);
        if ($this->formatter || $this->modelAlias || $this->with || $this->select
         || $this->selectColumns || $this->asColumns || $this->selectModifiers
         || $this->map || $this->having || $this->joins) {
            return $this->findPkComplex($key, $con);
        } else {
            return $this->findPkSimple($key, $con);
        }
    }

    /**
     * Find object by primary key using raw SQL to go fast.
     * Bypass doSelect() and the object formatter by using generated code.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     ConnectionInterface $con A connection object
     *
     * @return   ChildRewriteurlRuleParam A model object, or null if the key is not found
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT ID, ID_RULE, PARAM_NAME, PARAM_CONDITION, PARAM_VALUE FROM rewriteurl_rule_param WHERE ID = :p0';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key, PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), 0, $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            $obj = new ChildRewriteurlRuleParam();
            $obj->hydrate($row);
            RewriteurlRuleParamTableMap::addInstanceToPool($obj, (string) $key);
        }
        $stmt->closeCursor();

        return $obj;
    }

    /**
     * Find object by primary key.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     ConnectionInterface $con A connection object
     *
     * @return ChildRewriteurlRuleParam|array|mixed the result, formatted by the current formatter
     */
    protected function findPkComplex($key, $con)
    {
        // As the query uses a PK condition, no limit(1) is necessary.
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $dataFetcher = $criteria
            ->filterByPrimaryKey($key)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->formatOne($dataFetcher);
    }

    /**
     * Find objects by primary key
     * <code>
     * $objs = $c->findPks(array(12, 56, 832), $con);
     * </code>
     * @param     array $keys Primary keys to use for the query
     * @param     ConnectionInterface $con an optional connection object
     *
     * @return ObjectCollection|array|mixed the list of results, formatted by the current formatter
     */
    public function findPks($keys, $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getReadConnection($this->getDbName());
        }
        $this->basePreSelect($con);
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $dataFetcher = $criteria
            ->filterByPrimaryKeys($keys)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->format($dataFetcher);
    }

    /**
     * Filter the query by primary key
     *
     * @param     mixed $key Primary key to use for the query
     *
     * @return ChildRewriteurlRuleParamQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(RewriteurlRuleParamTableMap::ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return ChildRewriteurlRuleParamQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(RewriteurlRuleParamTableMap::ID, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the id column
     *
     * Example usage:
     * <code>
     * $query->filterById(1234); // WHERE id = 1234
     * $query->filterById(array(12, 34)); // WHERE id IN (12, 34)
     * $query->filterById(array('min' => 12)); // WHERE id > 12
     * </code>
     *
     * @param     mixed $id The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildRewriteurlRuleParamQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(RewriteurlRuleParamTableMap::ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(RewriteurlRuleParamTableMap::ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(RewriteurlRuleParamTableMap::ID, $id, $comparison);
    }

    /**
     * Filter the query on the id_rule column
     *
     * Example usage:
     * <code>
     * $query->filterByIdRule(1234); // WHERE id_rule = 1234
     * $query->filterByIdRule(array(12, 34)); // WHERE id_rule IN (12, 34)
     * $query->filterByIdRule(array('min' => 12)); // WHERE id_rule > 12
     * </code>
     *
     * @see       filterByRewriteurlRule()
     *
     * @param     mixed $idRule The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildRewriteurlRuleParamQuery The current query, for fluid interface
     */
    public function filterByIdRule($idRule = null, $comparison = null)
    {
        if (is_array($idRule)) {
            $useMinMax = false;
            if (isset($idRule['min'])) {
                $this->addUsingAlias(RewriteurlRuleParamTableMap::ID_RULE, $idRule['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idRule['max'])) {
                $this->addUsingAlias(RewriteurlRuleParamTableMap::ID_RULE, $idRule['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(RewriteurlRuleParamTableMap::ID_RULE, $idRule, $comparison);
    }

    /**
     * Filter the query on the param_name column
     *
     * Example usage:
     * <code>
     * $query->filterByParamName('fooValue');   // WHERE param_name = 'fooValue'
     * $query->filterByParamName('%fooValue%'); // WHERE param_name LIKE '%fooValue%'
     * </code>
     *
     * @param     string $paramName The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildRewriteurlRuleParamQuery The current query, for fluid interface
     */
    public function filterByParamName($paramName = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($paramName)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $paramName)) {
                $paramName = str_replace('*', '%', $paramName);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(RewriteurlRuleParamTableMap::PARAM_NAME, $paramName, $comparison);
    }

    /**
     * Filter the query on the param_condition column
     *
     * Example usage:
     * <code>
     * $query->filterByParamCondition('fooValue');   // WHERE param_condition = 'fooValue'
     * $query->filterByParamCondition('%fooValue%'); // WHERE param_condition LIKE '%fooValue%'
     * </code>
     *
     * @param     string $paramCondition The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildRewriteurlRuleParamQuery The current query, for fluid interface
     */
    public function filterByParamCondition($paramCondition = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($paramCondition)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $paramCondition)) {
                $paramCondition = str_replace('*', '%', $paramCondition);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(RewriteurlRuleParamTableMap::PARAM_CONDITION, $paramCondition, $comparison);
    }

    /**
     * Filter the query on the param_value column
     *
     * Example usage:
     * <code>
     * $query->filterByParamValue('fooValue');   // WHERE param_value = 'fooValue'
     * $query->filterByParamValue('%fooValue%'); // WHERE param_value LIKE '%fooValue%'
     * </code>
     *
     * @param     string $paramValue The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildRewriteurlRuleParamQuery The current query, for fluid interface
     */
    public function filterByParamValue($paramValue = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($paramValue)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $paramValue)) {
                $paramValue = str_replace('*', '%', $paramValue);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(RewriteurlRuleParamTableMap::PARAM_VALUE, $paramValue, $comparison);
    }

    /**
     * Filter the query by a related \RewriteUrl\Model\RewriteurlRule object
     *
     * @param \RewriteUrl\Model\RewriteurlRule|ObjectCollection $rewriteurlRule The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildRewriteurlRuleParamQuery The current query, for fluid interface
     */
    public function filterByRewriteurlRule($rewriteurlRule, $comparison = null)
    {
        if ($rewriteurlRule instanceof \RewriteUrl\Model\RewriteurlRule) {
            return $this
                ->addUsingAlias(RewriteurlRuleParamTableMap::ID_RULE, $rewriteurlRule->getId(), $comparison);
        } elseif ($rewriteurlRule instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(RewriteurlRuleParamTableMap::ID_RULE, $rewriteurlRule->toKeyValue('PrimaryKey', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByRewriteurlRule() only accepts arguments of type \RewriteUrl\Model\RewriteurlRule or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the RewriteurlRule relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return ChildRewriteurlRuleParamQuery The current query, for fluid interface
     */
    public function joinRewriteurlRule($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('RewriteurlRule');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'RewriteurlRule');
        }

        return $this;
    }

    /**
     * Use the RewriteurlRule relation RewriteurlRule object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \RewriteUrl\Model\RewriteurlRuleQuery A secondary query class using the current class as primary query
     */
    public function useRewriteurlRuleQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinRewriteurlRule($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'RewriteurlRule', '\RewriteUrl\Model\RewriteurlRuleQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   ChildRewriteurlRuleParam $rewriteurlRuleParam Object to remove from the list of results
     *
     * @return ChildRewriteurlRuleParamQuery The current query, for fluid interface
     */
    public function prune($rewriteurlRuleParam = null)
    {
        if ($rewriteurlRuleParam) {
            $this->addUsingAlias(RewriteurlRuleParamTableMap::ID, $rewriteurlRuleParam->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the rewriteurl_rule_param table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(RewriteurlRuleParamTableMap::DATABASE_NAME);
        }
        $affectedRows = 0; // initialize var to track total num of affected rows
        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            RewriteurlRuleParamTableMap::clearInstancePool();
            RewriteurlRuleParamTableMap::clearRelatedInstancePool();

            $con->commit();
        } catch (PropelException $e) {
            $con->rollBack();
            throw $e;
        }

        return $affectedRows;
    }

    /**
     * Performs a DELETE on the database, given a ChildRewriteurlRuleParam or Criteria object OR a primary key value.
     *
     * @param mixed               $values Criteria or ChildRewriteurlRuleParam object or primary key or array of primary keys
     *              which is used to create the DELETE statement
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *                if supported by native driver or if emulated using Propel.
     * @throws PropelException Any exceptions caught during processing will be
     *         rethrown wrapped into a PropelException.
     */
     public function delete(ConnectionInterface $con = null)
     {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(RewriteurlRuleParamTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(RewriteurlRuleParamTableMap::DATABASE_NAME);

        $affectedRows = 0; // initialize var to track total num of affected rows

        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();


        RewriteurlRuleParamTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            RewriteurlRuleParamTableMap::clearRelatedInstancePool();
            $con->commit();

            return $affectedRows;
        } catch (PropelException $e) {
            $con->rollBack();
            throw $e;
        }
    }

} // RewriteurlRuleParamQuery
