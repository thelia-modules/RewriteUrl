<?php

namespace RewriteUrl\Controller\Admin;

use Propel\Runtime\ActiveQuery\Criteria;
use RewriteUrl\Model\RewriteurlRule;
use RewriteUrl\Model\RewriteurlRuleParam;
use RewriteUrl\Model\RewriteurlRuleQuery;
use RewriteUrl\RewriteUrl;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\HttpFoundation\JsonResponse;
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
        $isIndexRedirectionEnabled = ConfigQuery::isIndexRedirectionEnable();
        $isHttpsRedirectionEnabled = ConfigQuery::isHttpsRedirectionEnable();

        return $this->render(
            "RewriteUrl/module-configuration",
            [
                "isRewritingEnabled" => $isRewritingEnabled,
                "isIndexRedirectionEnabled" => $isIndexRedirectionEnabled,
                "isHttpsRedirectionEnabled" => $isHttpsRedirectionEnabled,

            ]
        );
    }

    public function getDatatableRules()
    {
        $request = $this->getRequest();

        $requestSearchValue = $request->get('search') ? '%' . $request->get('search')['value'] . '%' : "";
        $recordsTotal = RewriteurlRuleQuery::create()->count();
        $search = RewriteurlRuleQuery::create();
        if ("" !== $requestSearchValue) {
            $search
                ->filterByValue($requestSearchValue, Criteria::LIKE)
                ->_or()
                ->filterByRedirectUrl($requestSearchValue)
            ;
        }

        $recordsFiltered = $search->count();

        $orderColumn = $request->get('order')[0]['column'];
        $orderDirection = $request->get('order')[0]['dir'];
        switch ($orderColumn) {
            case '0':
                $search->orderByRuleType($orderDirection);
                break;
            case '1':
                $search->orderByValue($orderDirection);
                break;
            case '2':
                $search->orderByOnly404($orderDirection);
                break;
            case '3':
                $search->orderByRedirectUrl($orderDirection);
                break;
            case '4':
                $search->orderByPosition($orderDirection);
                break;
            default:
                $search->orderByPosition();
                break;
        }

        $search
            ->offset($request->get('start'))
            ->limit($request->get('length'))
        ;
        $searchArray = $search->find()->toArray();

        $resultsArray = [];
        foreach ($searchArray as $row) {
            $id = $row['Id'];
            $isRegexSelected = $row['RuleType'] === 'regex' ? 'selected' : '';
            $isParamsSelected = $row['RuleType'] === 'params' ? 'selected' : '';
            $isOnly404Checked = $row['Only404'] ? 'checked' : '';
            $rewriteUrlRuleParams = RewriteurlRuleQuery::create()->findPk($row['Id'])->getRewriteUrlParamCollection();
            $resultsArray[] = [
                'Id' => $row['Id'],
                'RuleType' => '<select class="js_rule_type form-control" data-idrule="' . $id . '" required>
                                <option value="regex" ' . $isRegexSelected . '>' . Translator::getInstance()->trans("Regex", [], RewriteUrl::MODULE_DOMAIN) . '</option>
                                <option value="params" ' . $isParamsSelected . '>' . Translator::getInstance()->trans("Get Params", [], RewriteUrl::MODULE_DOMAIN) . '</option>
                               </select>',
                'Value' => $this->renderRaw(
                    "RewriteUrl/tab-value-render",
                    [
                        "REWRITE_URL_PARAMS" => $rewriteUrlRuleParams,
                        "VALUE" => $row['Value'],
                    ]
                ),
                'Only404' => '<input class="js_only404 form-control" type="checkbox" style="width: 100%!important;" ' . $isOnly404Checked . '/>',
                'RedirectUrl' => '<div class="col-md-12 input-group">
                                    <input class="js_url_to_redirect form-control" type="text" placeholder="/path/mypage.html" value="' . $row['RedirectUrl'] . '"/>
                                  </div>',
                'Position' => '<a href="#" class="u-position-up js_move_rule_position_up" data-idrule="' . $id . '"><i class="glyphicon glyphicon-arrow-up"></i></a>
                                <span class="js_editable_rule_position editable editable-click" data-idrule="' . $id . '">' . $row['Position'] . '</span>
                               <a href="#" class="u-position-down js_move_rule_position_down" data-idrule="' . $id . '"><i class="glyphicon glyphicon-arrow-down"></i></a>',
                'Actions' => '<a href="#" class="js_btn_update_rule btn btn-success" data-idrule="' . $id . '"><span class="glyphicon glyphicon-check"></span></a>
                              <a href="#" class="js_btn_remove_rule btn btn-danger" data-idrule="' . $id . '"><span class="glyphicon glyphicon-remove"></span></a>
',
            ];
        }

        return new JsonResponse([
            'draw' => $request->get('draw'),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $resultsArray
        ]);
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

    // check index_redirection_enable status & write an entry if there is none //
    
    public function setIndexRedirectionEnableAction()
    {
        $request = $this->getRequest()->request;
        $isIndexRedirectionEnable = $request->get("index_redirection_enable", null);

        if ($isIndexRedirectionEnable !== null) {
            ConfigQuery::write("index_redirection_enable", $isIndexRedirectionEnable ? 1 : 0);
            return $this->jsonResponse(json_encode(["state" => "Success"]), 200);
        } else {
            return $this->jsonResponse(Translator::getInstance()->trans(
                "Unable to change the configuration variable.",
                [],
                RewriteUrl::MODULE_DOMAIN
            ), 500);
        }
    }
    // ############################## //

    // check https_redirection_enable status & write an entry if there is none //

    public function setHttpsRedirectionEnableAction()
    {
        $request = $this->getRequest()->request;
        $isHttpsRedirectionEnable = $request->get("https_redirection_enable", null);

        if ($isHttpsRedirectionEnable !== null) {
            ConfigQuery::write("https_redirection_enable", $isHttpsRedirectionEnable ? 1 : 0);
            return $this->jsonResponse(json_encode(["state" => "Success"]), 200);
        } else {
            return $this->jsonResponse(Translator::getInstance()->trans(
                "Unable to change the configuration variable.",
                [],
                RewriteUrl::MODULE_DOMAIN
            ), 500);
        }
    }
    // ############################## //

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
