<?php

namespace RewriteUrl\Model;

use RewriteUrl\Model\Base\RewriteurlRuleParam as BaseRewriteurlRuleParam;

class RewriteurlRuleParam extends BaseRewriteurlRuleParam
{
    const PARAM_CONDITION_EQUALS = "equals";
    const PARAM_CONDITION_NOT_EQUALS = "not_equals";
    const PARAM_CONDITION_EXISTS = "exists";
    const PARAM_CONDITION_MISSING = "missing";
    const PARAM_CONDITION_EMPTY = "empty";
    const PARAM_CONDITION_NOT_EMPTY = "not_empty";


    public function isMatching($getParamArray)
    {
        if (array_key_exists($this->getParamName(), $getParamArray)) {
            $value = $getParamArray[$this->getParamName()];
            if (empty($value)) {
                if ($this->getParamCondition() === self::PARAM_CONDITION_EMPTY) {
                    return true;
                }
            } else {
                if ($this->getParamCondition() === self::PARAM_CONDITION_NOT_EMPTY) {
                    return true;
                }
            }

            if ($value == $this->getParamValue()) {
                if ($this->getParamCondition() === self::PARAM_CONDITION_EQUALS) {
                    return true;
                }
            } else {
                if ($this->getParamCondition() === self::PARAM_CONDITION_NOT_EQUALS) {
                    return true;
                }
            }

            if ($this->getParamCondition() === self::PARAM_CONDITION_EXISTS) {
                return true;
            }

        } else {
            if ($this->getParamCondition() === self::PARAM_CONDITION_MISSING) {
                return true;
            }
        }

        return false;
    }
}
