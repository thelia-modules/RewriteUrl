<?php

/*
 * This file is part of the Thelia package.
 * http://www.thelia.net
 *
 * (c) OpenStudio <info@thelia.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RewriteUrl\Model;

use RewriteUrl\Model\Base\RewriteurlRule as BaseRewriteurlRule;
use Thelia\Log\Tlog;
use Thelia\Model\Tools\PositionManagementTrait;

class RewriteurlRule extends BaseRewriteurlRule
{
    use PositionManagementTrait;

    /** @var string */
    public const TYPE_REGEX = 'regex';

    /** @var string */
    public const TYPE_GET_PARAMS = 'params';

    /** @var string */
    public const TYPE_REGEX_GET_PARAMS = 'regex-params';

    protected $rewriteUrlParamCollection = null;

    /**
     * @return \Propel\Runtime\Collection\ObjectCollection|RewriteurlRuleParam[]
     */
    public function getRewriteUrlParamCollection()
    {
        if ($this->rewriteUrlParamCollection === null) {
            $this->rewriteUrlParamCollection = RewriteurlRuleParamQuery::create()->filterByIdRule($this->getId())->find();
        }

        return $this->rewriteUrlParamCollection;
    }

    protected function isMatchingPath(string $url): bool
    {
        if (!empty($this->getValue())) {
            try {
                $match = @preg_match('/'.$this->getValue().'/', $url);

                if (false === $match) {
                    Tlog::getInstance()->error('Invalid pattern: '.$this->getValue());
                }

                return (bool) $match;
            } catch (\Exception $ex) {
                Tlog::getInstance()->error('Failed to match rule : '.$ex->getMessage());
            }
        }

        return false;
    }

    protected function isMatchingGetParams(array $getParamArray): bool
    {
        if ($this->getRewriteUrlParamCollection()->count() === 0) {
            return false;
        }

        foreach ($this->getRewriteUrlParamCollection() as $rewriteUrlParam) {
            if (!$rewriteUrlParam->isMatching($getParamArray)) {
                return false;
            }
        }

        return true;
    }

    public function isMatching(string $url, array $getParamArray): bool
    {
        if ($this->getRuleType() === self::TYPE_REGEX) {
            return $this->isMatchingPath($url);
        }

        if ($this->getRuleType() === self::TYPE_GET_PARAMS) {
            return $this->isMatchingGetParams($getParamArray);
        }

        if ($this->getRuleType() === self::TYPE_REGEX_GET_PARAMS) {
            return $this->isMatchingPath($url) && $this->isMatchingGetParams($getParamArray);
        }

        return false;
    }

    public function deleteAllRelatedParam(): void
    {
        RewriteurlRuleParamQuery::create()->filterByIdRule($this->getId())->find()->delete();
    }
}
