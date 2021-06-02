<?php

namespace RewriteUrl\Model;

use RewriteUrl\Model\Base\RewriteurlRule as BaseRewriteurlRule;
use Thelia\Model\Tools\PositionManagementTrait;

class RewriteurlRule extends BaseRewriteurlRule
{
    use PositionManagementTrait;


    /** @var string  */
    const TYPE_REGEX = "regex";

    /** @var string  */
    const TYPE_GET_PARAMS = "params";


    protected $rewriteUrlParamCollection = null;


    public function getRewriteUrlParamCollection()
    {
        if ($this->rewriteUrlParamCollection == null) {
            $this->rewriteUrlParamCollection = RewriteurlRuleParamQuery::create()->filterByIdRule($this->getId())->find();
        }
        return $this->rewriteUrlParamCollection;
    }

    public function isMatching($url, $getParamArray)
    {
        if ($this->getRuleType() == self::TYPE_REGEX) {
            if (!empty($this->getValue())) {
                return preg_match("/" . $this->getValue() . "/", $url) === 1;
            }
        } elseif ($this->getRuleType() == self::TYPE_GET_PARAMS) {
            if ($this->getRewriteUrlParamCollection()->count() > 0) {
                foreach ($this->getRewriteUrlParamCollection() as $rewriteUrlParam) {
                    if (!$rewriteUrlParam->isMatching($getParamArray)) {
                        return false;
                    }
                }
                return true;
            }
        }
        return false;
    }

    public function deleteAllRelatedParam()
    {
        RewriteurlRuleParamQuery::create()->filterByIdRule($this->getId())->find()->delete();
    }
}
