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
use RewriteUrl\Model\RewriteurlRule as ChildRewriteurlRule;
use RewriteUrl\Model\RewriteurlRuleQuery as ChildRewriteurlRuleQuery;
use RewriteUrl\Model\Map\RewriteurlRuleTableMap;

/**
 * Base class that represents a query for the 'rewriteurl_rule' table.
 *
 *
 *
 * @method     ChildRewriteurlRuleQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildRewriteurlRuleQuery orderByRuleType($order = Criteria::ASC) Order by the rule_type column
 * @method     ChildRewriteurlRuleQuery orderByValue($order = Criteria::ASC) Order by the value column
 * @method     ChildRewriteurlRuleQuery orderByOnly404($order = Criteria::ASC) Order by the only404 column
 * @method     ChildRewriteurlRuleQuery orderByRedirectUrl($order = Criteria::ASC) Order by the redirect_url column
 * @method     ChildRewriteurlRuleQuery orderByPosition($order = Criteria::ASC) Order by the position column
 *
 * @method     ChildRewriteurlRuleQuery groupById() Group by the id column
 * @method     ChildRewriteurlRuleQuery groupByRuleType() Group by the rule_type column
 * @method     ChildRewriteurlRuleQuery groupByValue() Group by the value column
 * @method     ChildRewriteurlRuleQuery groupByOnly404() Group by the only404 column
 * @method     ChildRewriteurlRuleQuery groupByRedirectUrl() Group by the redirect_url column
 * @method     ChildRewriteurlRuleQuery groupByPosition() Group by the position column
 *
 * @method     ChildRewriteurlRuleQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildRewriteurlRuleQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildRewriteurlRuleQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildRewriteurlRuleQuery leftJoinRewriteurlRuleParam($relationAlias = null) Adds a LEFT JOIN clause to the query using the RewriteurlRuleParam relation
 * @method     ChildRewriteurlRuleQuery rightJoinRewriteurlRuleParam($relationAlias = null) Adds a RIGHT JOIN clause to the query using the RewriteurlRuleParam relation
 * @method     ChildRewriteurlRuleQuery innerJoinRewriteurlRuleParam($relationAlias = null) Adds a INNER JOIN clause to the query using the RewriteurlRuleParam relation
 *
 * @method     ChildRewriteurlRule findOne(ConnectionInterface $con = null) Return the first ChildRewriteurlRule matching the query
 * @method     ChildRewriteurlRule findOneOrCreate(ConnectionInterface $con = null) Return the first ChildRewriteurlRule matching the query, or a new ChildRewriteurlRule object populated from the query conditions when no match is found
 *
 * @method     ChildRewriteurlRule findOneById(int $id) Return the first ChildRewriteurlRule filtered by the id column
 * @method     ChildRewriteurlRule findOneByRuleType(string $rule_type) Return the first ChildRewriteurlRule filtered by the rule_type column
 * @method     ChildRewriteurlRule findOneByValue(string $value) Return the first ChildRewriteurlRule filtered by the value column
 * @method     ChildRewriteurlRule findOneByOnly404(boolean $only404) Return the first ChildRewriteurlRule filtered by the only404 column
 * @method     ChildRewriteurlRule findOneByRedirectUrl(string $redirect_url) Return the first ChildRewriteurlRule filtered by the redirect_url column
 * @method     ChildRewriteurlRule findOneByPosition(int $position) Return the first ChildRewriteurlRule filtered by the position column
 *
 * @method     array findById(int $id) Return ChildRewriteurlRule objects filtered by the id column
 * @method     array findByRuleType(string $rule_type) Return ChildRewriteurlRule objects filtered by the rule_type column
 * @method     array findByValue(string $value) Return ChildRewriteurlRule objects filtered by the value column
 * @method     array findByOnly404(boolean $only404) Return ChildRewriteurlRule objects filtered by the only404 column
 * @method     array findByRedirectUrl(string $redirect_url) Return ChildRewriteurlRule objects filtered by the redirect_url column
 * @method     array findByPosition(int $position) Return ChildRewriteurlRule objects filtered by the position column
 *
 */
abstract class RewriteurlRuleQuery extends ModelCriteria
{

    /**
     * Initializes internal state of \RewriteUrl\Model\Base\RewriteurlRuleQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'thelia', $modelName = '\\RewriteUrl\\Model\\RewriteurlRule', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildRewriteurlRuleQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildRewriteurlRuleQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof \RewriteUrl\Model\RewriteurlRuleQuery) {
            return $criteria;
        }
        $query = new \RewriteUrl\Model\RewriteurlRuleQuery();
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
     * @return ChildRewriteurlRule|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = RewriteurlRuleTableMap::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(RewriteurlRuleTableMap::DATABASE_NAME);
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
     * @return   ChildRewriteurlRule A model object, or null if the key is not found
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT ID, RULE_TYPE, VALUE, ONLY404, REDIRECT_URL, POSITION FROM rewriteurl_rule WHERE ID = :p0';
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
            $obj = new ChildRewriteurlRule();
            $obj->hydrate($row);
            RewriteurlRuleTableMap::addInstanceToPool($obj, (string) $key);
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
     * @return ChildRewriteurlRule|array|mixed the result, formatted by the current formatter
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
     * @return ChildRewriteurlRuleQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(RewriteurlRuleTableMap::ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return ChildRewriteurlRuleQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(RewriteurlRuleTableMap::ID, $keys, Criteria::IN);
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
     * @return ChildRewriteurlRuleQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(RewriteurlRuleTableMap::ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(RewriteurlRuleTableMap::ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(RewriteurlRuleTableMap::ID, $id, $comparison);
    }

    /**
     * Filter the query on the rule_type column
     *
     * Example usage:
     * <code>
     * $query->filterByRuleType('fooValue');   // WHERE rule_type = 'fooValue'
     * $query->filterByRuleType('%fooValue%'); // WHERE rule_type LIKE '%fooValue%'
     * </code>
     *
     * @param     string $ruleType The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildRewriteurlRuleQuery The current query, for fluid interface
     */
    public function filterByRuleType($ruleType = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($ruleType)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $ruleType)) {
                $ruleType = str_replace('*', '%', $ruleType);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(RewriteurlRuleTableMap::RULE_TYPE, $ruleType, $comparison);
    }

    /**
     * Filter the query on the value column
     *
     * Example usage:
     * <code>
     * $query->filterByValue('fooValue');   // WHERE value = 'fooValue'
     * $query->filterByValue('%fooValue%'); // WHERE value LIKE '%fooValue%'
     * </code>
     *
     * @param     string $value The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildRewriteurlRuleQuery The current query, for fluid interface
     */
    public function filterByValue($value = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($value)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $value)) {
                $value = str_replace('*', '%', $value);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(RewriteurlRuleTableMap::VALUE, $value, $comparison);
    }

    /**
     * Filter the query on the only404 column
     *
     * Example usage:
     * <code>
     * $query->filterByOnly404(true); // WHERE only404 = true
     * $query->filterByOnly404('yes'); // WHERE only404 = true
     * </code>
     *
     * @param     boolean|string $only404 The value to use as filter.
     *              Non-boolean arguments are converted using the following rules:
     *                * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *                * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     *              Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildRewriteurlRuleQuery The current query, for fluid interface
     */
    public function filterByOnly404($only404 = null, $comparison = null)
    {
        if (is_string($only404)) {
            $only404 = in_array(strtolower($only404), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
        }

        return $this->addUsingAlias(RewriteurlRuleTableMap::ONLY404, $only404, $comparison);
    }

    /**
     * Filter the query on the redirect_url column
     *
     * Example usage:
     * <code>
     * $query->filterByRedirectUrl('fooValue');   // WHERE redirect_url = 'fooValue'
     * $query->filterByRedirectUrl('%fooValue%'); // WHERE redirect_url LIKE '%fooValue%'
     * </code>
     *
     * @param     string $redirectUrl The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildRewriteurlRuleQuery The current query, for fluid interface
     */
    public function filterByRedirectUrl($redirectUrl = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($redirectUrl)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $redirectUrl)) {
                $redirectUrl = str_replace('*', '%', $redirectUrl);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(RewriteurlRuleTableMap::REDIRECT_URL, $redirectUrl, $comparison);
    }

    /**
     * Filter the query on the position column
     *
     * Example usage:
     * <code>
     * $query->filterByPosition(1234); // WHERE position = 1234
     * $query->filterByPosition(array(12, 34)); // WHERE position IN (12, 34)
     * $query->filterByPosition(array('min' => 12)); // WHERE position > 12
     * </code>
     *
     * @param     mixed $position The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildRewriteurlRuleQuery The current query, for fluid interface
     */
    public function filterByPosition($position = null, $comparison = null)
    {
        if (is_array($position)) {
            $useMinMax = false;
            if (isset($position['min'])) {
                $this->addUsingAlias(RewriteurlRuleTableMap::POSITION, $position['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($position['max'])) {
                $this->addUsingAlias(RewriteurlRuleTableMap::POSITION, $position['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(RewriteurlRuleTableMap::POSITION, $position, $comparison);
    }

    /**
     * Filter the query by a related \RewriteUrl\Model\RewriteurlRuleParam object
     *
     * @param \RewriteUrl\Model\RewriteurlRuleParam|ObjectCollection $rewriteurlRuleParam  the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildRewriteurlRuleQuery The current query, for fluid interface
     */
    public function filterByRewriteurlRuleParam($rewriteurlRuleParam, $comparison = null)
    {
        if ($rewriteurlRuleParam instanceof \RewriteUrl\Model\RewriteurlRuleParam) {
            return $this
                ->addUsingAlias(RewriteurlRuleTableMap::ID, $rewriteurlRuleParam->getIdRule(), $comparison);
        } elseif ($rewriteurlRuleParam instanceof ObjectCollection) {
            return $this
                ->useRewriteurlRuleParamQuery()
                ->filterByPrimaryKeys($rewriteurlRuleParam->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByRewriteurlRuleParam() only accepts arguments of type \RewriteUrl\Model\RewriteurlRuleParam or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the RewriteurlRuleParam relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return ChildRewriteurlRuleQuery The current query, for fluid interface
     */
    public function joinRewriteurlRuleParam($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('RewriteurlRuleParam');

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
            $this->addJoinObject($join, 'RewriteurlRuleParam');
        }

        return $this;
    }

    /**
     * Use the RewriteurlRuleParam relation RewriteurlRuleParam object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \RewriteUrl\Model\RewriteurlRuleParamQuery A secondary query class using the current class as primary query
     */
    public function useRewriteurlRuleParamQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinRewriteurlRuleParam($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'RewriteurlRuleParam', '\RewriteUrl\Model\RewriteurlRuleParamQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   ChildRewriteurlRule $rewriteurlRule Object to remove from the list of results
     *
     * @return ChildRewriteurlRuleQuery The current query, for fluid interface
     */
    public function prune($rewriteurlRule = null)
    {
        if ($rewriteurlRule) {
            $this->addUsingAlias(RewriteurlRuleTableMap::ID, $rewriteurlRule->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the rewriteurl_rule table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(RewriteurlRuleTableMap::DATABASE_NAME);
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
            RewriteurlRuleTableMap::clearInstancePool();
            RewriteurlRuleTableMap::clearRelatedInstancePool();

            $con->commit();
        } catch (PropelException $e) {
            $con->rollBack();
            throw $e;
        }

        return $affectedRows;
    }

    /**
     * Performs a DELETE on the database, given a ChildRewriteurlRule or Criteria object OR a primary key value.
     *
     * @param mixed               $values Criteria or ChildRewriteurlRule object or primary key or array of primary keys
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
            $con = Propel::getServiceContainer()->getWriteConnection(RewriteurlRuleTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(RewriteurlRuleTableMap::DATABASE_NAME);

        $affectedRows = 0; // initialize var to track total num of affected rows

        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();


        RewriteurlRuleTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            RewriteurlRuleTableMap::clearRelatedInstancePool();
            $con->commit();

            return $affectedRows;
        } catch (PropelException $e) {
            $con->rollBack();
            throw $e;
        }
    }

} // RewriteurlRuleQuery
