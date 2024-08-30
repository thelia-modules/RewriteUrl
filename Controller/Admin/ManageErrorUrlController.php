<?php

namespace RewriteUrl\Controller\Admin;

use Exception;
use Propel\Runtime\Exception\PropelException;
use RewriteUrl\Form\ResearchForm;
use RewriteUrl\Form\UpdateRewriteUrlForm;
use RewriteUrl\Model\RewriteurlErrorUrlQuery;
use RewriteUrl\Model\RewriteurlRule;
use RewriteUrl\Model\RewriteurlRuleQuery;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\HttpFoundation\JsonResponse;
use Thelia\Core\HttpFoundation\Response;
use Thelia\Core\Template\ParserContext;
use Thelia\Form\Exception\FormValidationException;

#[Route('/admin/module/RewriteUrl/manageErrorUrl', name: 'admin_rewrite_url_manage_error_url_')]
class ManageErrorUrlController extends BaseAdminController
{
    #[Route('', name: 'show', methods: ['GET'])]
    public function manageErrorUrl(): Response|RedirectResponse
    {
        return $this->render('manage-errors-url');
    }
    
    #[Route('/get-rule', name: 'get_value', methods: ['GET'])]
    public function getRewriteUrlRuleValue(RequestStack $requestStack): JsonResponse
    {
        $ruleId = $requestStack->getCurrentRequest()->query->get('rule_id');
        return new JsonResponse(RewriteurlRuleQuery::create()->findOneById($ruleId)?->getRedirectUrl());
    }
    
    #[Route('/search', name: 'search', methods: ['POST'])]
    public function search(ParserContext $parserContext): RedirectResponse|Response
    {
        $url = "/admin/module/RewriteUrl/manageErrorUrl?";
        $form = $this->createForm(ResearchForm::getName());

        try {
            $data = $this->validateForm($form)->getData();

            if (null !== $userAgent = $data['user_agent']) {
                $url .= "user_agent=" . $userAgent;
            }

            if (null !== $data['user_agent'] && null !== $data['url_source']) {
                $url .= "&";
            }

            if (null !== $urlSource = $data['url_source']) {
                $url .= "url_source=" . $urlSource;
            }

            return $this->generateRedirect($url);
        } catch (FormValidationException $e) {
            $error_message = $this->createStandardFormValidationErrorMessage($e);
        } catch (Exception $e) {
            $error_message = $e->getMessage();
        }

        $form->setErrorMessage($error_message);

        $parserContext
            ->addForm($form)
            ->setGeneralError($error_message);

        return $this->generateErrorRedirect($form);
    }

    #[Route('/update/{id}', name: 'update', methods: ['POST'])]
    public function updateRewriteUrl(ParserContext $parserContext, $id): Response|RedirectResponse
    {
        $form = $this->createForm(UpdateRewriteUrlForm::getName());

        try {
            $data = $this->validateForm($form)->getData();

            if (null === $rewriteURLErrorUrl = RewriteurlErrorUrlQuery::create()->findOneById($id)) {
                throw new PropelException("Rewrite url with id $id not found");
            }

            if (null === $rewriteUrlRule = $rewriteURLErrorUrl->getRewriteUrlRule()) {
                $rewriteUrlRule = new RewriteurlRule();
            }

            $rewriteUrlRule
                ->setRuleType(RewriteurlRule::TYPE_TEXT)
                ->setValue($rewriteURLErrorUrl->getUrlSource())
                ->setOnly404(1)
                ->setRedirectUrl($data['rewritten_url'])
                ->save()
            ;

            $rewriteURLErrorUrl
                ->setRewriteurlRuleId($rewriteUrlRule->getId())
                ->save()
            ;

            return $this->generateSuccessRedirect($form);
        } catch (FormValidationException $e) {
            $error_message = $this->createStandardFormValidationErrorMessage($e);
        } catch (Exception $e) {
            $error_message = $e->getMessage();
        }

        $form->setErrorMessage($error_message);

        $parserContext
            ->addForm($form)
            ->setGeneralError($error_message);

        return $this->generateErrorRedirect($form);
    }

    #[Route('/delete/{id}', name: 'delete')]
    public function deleteErrorUrl($id): JsonResponse
    {
        try {
            RewriteurlErrorUrlQuery::create()->filterById($id)->delete();
        } catch (PropelException) {
            return new JsonResponse(['success' => false]);
        }

        return new JsonResponse(['success' => true]);
    }

    /**
     * @throws PropelException
     */
    #[Route('/delete', name: 'delete_all')]
    public function deleteAllErrorUrl(): Response|RedirectResponse
    {
        RewriteurlErrorUrlQuery::create()->deleteAll();

        return $this->generateRedirect('/admin/module/RewriteUrl/manageErrorUrl');
    }
}