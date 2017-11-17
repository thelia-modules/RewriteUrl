<?php

namespace RewriteUrl\Controller\Admin;

use RewriteUrl\Model\RewriteurlRule;
use RewriteUrl\Model\RewriteurlRuleParam;
use RewriteUrl\Model\RewriteurlRuleQuery;
use RewriteUrl\RewriteUrl;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Core\Translation\Translator;
use Thelia\Model\ConfigQuery;

class ModuleConfigController extends BaseAdminController
{
    public function viewConfigAction($params = array())
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), 'GoogleShoppingXml', AccessManager::VIEW)) {
            return $response;
        }

        $isRewritingEnabled = ConfigQuery::isRewritingEnable();
        $rewritingRules = RewriteurlRuleQuery::create()->orderByPosition()->find();

        return $this->render(
            "RewriteUrl/module-configuration",
            [
                "isRewritingEnabled" => $isRewritingEnabled,
                "rewritingRules" => $rewritingRules,
            ]
        );
    }

    public function setRewritingEnableAction()
    {
        $request = $this->getRequest()->request;
        $isRewritingEnable = $request->get("rewriting_enable", null);

        if ($isRewritingEnable !== null) {
            ConfigQuery::write("rewriting_enable", $isRewritingEnable ? 1 : 0);
            return $this->jsonResponse(json_encode(["state" => "Success"]), 200);
        } else {
            return $this->jsonResponse(Translator::getInstance()->trans(
                "Unable to change the configuration variable.",
                [],
                RewriteUrl::MODULE_DOMAIN
            ), 500);
        }
    }

    public function addRuleAction()
    {
        try {
            $request = $this->getRequest()->request;

            $rule = new RewriteurlRule();

            $this->fillRuleObjectFields($rule, $request);
        } catch (\Exception $ex) {
            return $this->jsonResponse($ex->getMessage(), 500);
        }
        return $this->jsonResponse(json_encode(["state" => "Success"]), 200);
    }


    public function updateRuleAction()
    {
        try {
            $request = $this->getRequest()->request;

            $rule = RewriteurlRuleQuery::create()->findOneById($request->get("id"));
            if ($rule == null) {
                throw new \Exception(Translator::getInstance()->trans(
                    "Unable to find rule to update.",
                    [],
                    RewriteUrl::MODULE_DOMAIN
                ));
            }

            $this->fillRuleObjectFields($rule, $request);
        } catch (\Exception $ex) {
            return $this->jsonResponse($ex->getMessage(), 500);
        }
        return $this->jsonResponse(json_encode(["state" => "Success"]), 200);
    }


    public function removeRuleAction()
    {
        try {
            $request = $this->getRequest()->request;

            $rule = RewriteurlRuleQuery::create()->findOneById($request->get("id"));
            if ($rule == null) {
                throw new \Exception(Translator::getInstance()->trans(
                    "Unable to find rule to remove.",
                    [],
                    RewriteUrl::MODULE_DOMAIN
                ));
            }

            $rule->delete();
        } catch (\Exception $ex) {
            return $this->jsonResponse($ex->getMessage(), 500);
        }
        return $this->jsonResponse(json_encode(["state" => "Success"]), 200);
    }


    public function moveRulePositionAction()
    {
        try {
            $request = $this->getRequest()->request;

            $rule = RewriteurlRuleQuery::create()->findOneById($request->get("id"));
            if ($rule == null) {
                throw new \Exception(Translator::getInstance()->trans(
                    "Unable to find rule to change position.",
                    [],
                    RewriteUrl::MODULE_DOMAIN
                ));
            }

            $type = $request->get("type", null);
            if ($type == "up") {
                $rule->movePositionUp();
            } elseif ($type == "down") {
                $rule->movePositionDown();
            } elseif ($type == "absolute") {
                $position = $request->get("position", null);
                if (!empty($position)) {
                    $rule->changeAbsolutePosition($position);
                }
            }
        } catch (\Exception $ex) {
            return $this->jsonResponse($ex->getMessage(), 500);
        }
        return $this->jsonResponse(json_encode(["state" => "Success"]), 200);
    }



    protected function fillRuleObjectFields(RewriteurlRule $rule, $request)
    {
        $ruleType = $request->get("ruleType", null);
        if ($ruleType !== "regex" && $ruleType !== "params") {
            throw new \Exception(Translator::getInstance()->trans("Unknown rule type.", [], RewriteUrl::MODULE_DOMAIN));
        }

        $regexValue = $request->get("value", null);
        if ($ruleType == "regex" && empty($regexValue)) {
            throw new \Exception(Translator::getInstance()->trans("Regex value cannot be empty.", [], RewriteUrl::MODULE_DOMAIN));
        }

        $redirectUrl = $request->get("redirectUrl", null);
        if (empty($redirectUrl)) {
            throw new \Exception(Translator::getInstance()->trans("Redirect url cannot be empty.", [], RewriteUrl::MODULE_DOMAIN));
        }

        $paramRuleArray = array();
        if ($ruleType == "params") {
            $paramRuleArray = $request->get("paramRules", null);
            if (empty($paramRuleArray)) {
                throw new \Exception(Translator::getInstance()->trans("At least one GET parameter is required.", [], RewriteUrl::MODULE_DOMAIN));
            }
        }



        $rule->setRuleType($ruleType);
        $rule->setValue($regexValue);
        $rule->setOnly404($request->get("only404", 1));
        $rule->setRedirectUrl($redirectUrl);
        if (empty($rule->getPosition())) {
            $rule->setPosition($rule->getNextPosition());
        }


        $rule->deleteAllRelatedParam();

        $rule->save();

        if ($ruleType == "params") {
            foreach ($paramRuleArray as $paramRule) {
                if (!array_key_exists("paramName", $paramRule) || empty($paramRule["paramName"])) {
                    throw new \Exception(Translator::getInstance()->trans(
                        "Param name is empty.",
                        [],
                        RewriteUrl::MODULE_DOMAIN
                    ));
                }
                if (!array_key_exists("condition", $paramRule) || empty($paramRule["condition"])) {
                    throw new \Exception(Translator::getInstance()->trans(
                        "Param condition is empty.",
                        [],
                        RewriteUrl::MODULE_DOMAIN
                    ));
                }

                $paramRuleObject = new RewriteurlRuleParam();
                $paramRuleObject->setParamName($paramRule["paramName"]);
                $paramRuleObject->setParamCondition($paramRule["condition"]);
                $paramRuleObject->setParamValue($paramRule["paramValue"]);
                $paramRuleObject->setIdRule($rule->getId());
                $paramRuleObject->save();
            }
        }
    }
}
