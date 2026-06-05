<?php

namespace RewriteUrl\Loop;

use Propel\Runtime\ActiveQuery\Criteria;
use RewriteUrl\Model\RewriteurlGoneUrlQuery;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;
use Thelia\Core\Template\Loop\Generic;

class RewriteUrlGoneUrlLoop extends Generic
{
    protected function getArgDefinitions()
    {
        $argumentCollection = parent::getArgDefinitions();
        $argumentCollection->addArgument(Argument::createAnyTypeArgument('search'));

        return $argumentCollection;
    }

    public function buildModelCriteria()
    {
        /** @var RewriteurlGoneUrlQuery $query */
        $query = parent::buildModelCriteria();

        if (null !== $searchTerm = $this->getSearch()) {
            $query->filterByUrlSource('%' . $searchTerm . '%', Criteria::LIKE);
        }

        return $query;
    }
}
