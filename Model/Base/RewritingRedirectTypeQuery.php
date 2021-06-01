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
use RewriteUrl\Model\RewritingRedirectType as ChildRewritingRedirectType;
use RewriteUrl\Model\RewritingRedirectTypeQuery as ChildRewritingRedirectTypeQuery;
use RewriteUrl\Model\Map\RewritingRedirectTypeTableMap;
use Thelia\Model\RewritingUrl;

/**
 * Base class that represents a query for the 'rewriting_redirect_type' table.
 *
 *
 *
 * @method     ChildRewritingRedirectTypeQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildRewritingRedirectTypeQuery orderByHttpcode($order = Criteria::ASC) Order by the httpcode column
 *
 * @method     ChildRewritingRedirectTypeQuery groupById() Group by the id column
 * @method     ChildRewritingRedirectTypeQuery groupByHttpcode() Group by the httpcode column
 *
 * @method     ChildRewritingRedirectTypeQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildRewritingRedirectTypeQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildRewritingRedirectTypeQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildRewritingRedirectTypeQuery leftJoinRewritingUrl($relationAlias = null) Adds a LEFT JOIN clause to the query using the RewritingUrl relation
 * @method     ChildRewritingRedirectTypeQuery rightJoinRewritingUrl($relationAlias = null) Adds a RIGHT JOIN clause to the query using the RewritingUrl relation
 * @method     ChildRewritingRedirectTypeQuery innerJoinRewritingUrl($relationAlias = null) Adds a INNER JOIN clause to the query using the RewritingUrl relation
 *
 * @method     ChildRewritingRedirectType findOne(ConnectionInterface $con = null) Return the first ChildRewritingRedirectType matching the query
 * @method     ChildRewritingRedirectType findOneOrCreate(ConnectionInterface $con = null) Return the first ChildRewritingRedirectType matching the query, or a new ChildRewritingRedirectType object populated from the query conditions when no match is found
 *
 * @method     ChildRewritingRedirectType findOneById(int $id) Return the first ChildRewritingRedirectType filtered by the id column
 * @method     ChildRewritingRedirectType findOneByHttpcode(int $httpcode) Return the first ChildRewritingRedirectType filtered by the httpcode column
 *
 * @method     array findById(int $id) Return ChildRewritingRedirectType objects filtered by the id column
 * @method     array findByHttpcode(int $httpcode) Return ChildRewritingRedirectType objects filtered by the httpcode column
 *
 */
abstract class RewritingRedirectTypeQuery extends ModelCriteria
{

    /**
     * Initializes internal state of \RewriteUrl\Model\Base\RewritingRedirectTypeQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'thelia', $modelName = '\\RewriteUrl\\Model\\RewritingRedirectType', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildRewritingRedirectTypeQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildRewritingRedirectTypeQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof \RewriteUrl\Model\RewritingRedirectTypeQuery) {
            return $criteria;
        }
        $query = new \RewriteUrl\Model\RewritingRedirectTypeQuery();
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
     * @return ChildRewritingRedirectType|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = RewritingRedirectTypeTableMap::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(RewritingRedirectTypeTableMap::DATABASE_NAME);
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
     * @return   ChildRewritingRedirectType A model object, or null if the key is not found
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT ID, HTTPCODE FROM rewriting_redirect_type WHERE ID = :p0';
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
            $obj = new ChildRewritingRedirectType();
            $obj->hydrate($row);
            RewritingRedirectTypeTableMap::addInstanceToPool($obj, (string) $key);
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
     * @return ChildRewritingRedirectType|array|mixed the result, formatted by the current formatter
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
     * @return ChildRewritingRedirectTypeQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(RewritingRedirectTypeTableMap::ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return ChildRewritingRedirectTypeQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(RewritingRedirectTypeTableMap::ID, $keys, Criteria::IN);
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
     * @see       filterByRewritingUrl()
     *
     * @param     mixed $id The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildRewritingRedirectTypeQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(RewritingRedirectTypeTableMap::ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(RewritingRedirectTypeTableMap::ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(RewritingRedirectTypeTableMap::ID, $id, $comparison);
    }

    /**
     * Filter the query on the httpcode column
     *
     * Example usage:
     * <code>
     * $query->filterByHttpcode(1234); // WHERE httpcode = 1234
     * $query->filterByHttpcode(array(12, 34)); // WHERE httpcode IN (12, 34)
     * $query->filterByHttpcode(array('min' => 12)); // WHERE httpcode > 12
     * </code>
     *
     * @param     mixed $httpcode The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildRewritingRedirectTypeQuery The current query, for fluid interface
     */
    public function filterByHttpcode($httpcode = null, $comparison = null)
    {
        if (is_array($httpcode)) {
            $useMinMax = false;
            if (isset($httpcode['min'])) {
                $this->addUsingAlias(RewritingRedirectTypeTableMap::HTTPCODE, $httpcode['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($httpcode['max'])) {
                $this->addUsingAlias(RewritingRedirectTypeTableMap::HTTPCODE, $httpcode['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(RewritingRedirectTypeTableMap::HTTPCODE, $httpcode, $comparison);
    }

    /**
     * Filter the query by a related \Thelia\Model\RewritingUrl object
     *
     * @param \Thelia\Model\RewritingUrl|ObjectCollection $rewritingUrl The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildRewritingRedirectTypeQuery The current query, for fluid interface
     */
    public function filterByRewritingUrl($rewritingUrl, $comparison = null)
    {
        if ($rewritingUrl instanceof \Thelia\Model\RewritingUrl) {
            return $this
                ->addUsingAlias(RewritingRedirectTypeTableMap::ID, $rewritingUrl->getId(), $comparison);
        } elseif ($rewritingUrl instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(RewritingRedirectTypeTableMap::ID, $rewritingUrl->toKeyValue('PrimaryKey', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByRewritingUrl() only accepts arguments of type \Thelia\Model\RewritingUrl or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the RewritingUrl relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return ChildRewritingRedirectTypeQuery The current query, for fluid interface
     */
    public function joinRewritingUrl($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('RewritingUrl');

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
            $this->addJoinObject($join, 'RewritingUrl');
        }

        return $this;
    }

    /**
     * Use the RewritingUrl relation RewritingUrl object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \Thelia\Model\RewritingUrlQuery A secondary query class using the current class as primary query
     */
    public function useRewritingUrlQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinRewritingUrl($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'RewritingUrl', '\Thelia\Model\RewritingUrlQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   ChildRewritingRedirectType $rewritingRedirectType Object to remove from the list of results
     *
     * @return ChildRewritingRedirectTypeQuery The current query, for fluid interface
     */
    public function prune($rewritingRedirectType = null)
    {
        if ($rewritingRedirectType) {
            $this->addUsingAlias(RewritingRedirectTypeTableMap::ID, $rewritingRedirectType->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the rewriting_redirect_type table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(RewritingRedirectTypeTableMap::DATABASE_NAME);
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
            RewritingRedirectTypeTableMap::clearInstancePool();
            RewritingRedirectTypeTableMap::clearRelatedInstancePool();

            $con->commit();
        } catch (PropelException $e) {
            $con->rollBack();
            throw $e;
        }

        return $affectedRows;
    }

    /**
     * Performs a DELETE on the database, given a ChildRewritingRedirectType or Criteria object OR a primary key value.
     *
     * @param mixed               $values Criteria or ChildRewritingRedirectType object or primary key or array of primary keys
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
            $con = Propel::getServiceContainer()->getWriteConnection(RewritingRedirectTypeTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(RewritingRedirectTypeTableMap::DATABASE_NAME);

        $affectedRows = 0; // initialize var to track total num of affected rows

        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();


        RewritingRedirectTypeTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            RewritingRedirectTypeTableMap::clearRelatedInstancePool();
            $con->commit();

            return $affectedRows;
        } catch (PropelException $e) {
            $con->rollBack();
            throw $e;
        }
    }

} // RewritingRedirectTypeQuery
