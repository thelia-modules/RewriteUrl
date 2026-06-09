<?php

namespace RewriteUrl\Controller\Admin;

use Exception;
use Propel\Runtime\Exception\PropelException;
use RewriteUrl\Form\UpdateRewriteUrlForm;
use RewriteUrl\Model\RewriteurlErrorUrlQuery;
use RewriteUrl\Model\RewriteurlErrorUrlReferer;
use RewriteUrl\Model\RewriteurlErrorUrlRefererQuery;
use RewriteUrl\Model\RewriteurlRule;
use RewriteUrl\Model\RewriteurlRuleQuery;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\HttpFoundation\JsonResponse;
use Thelia\Core\Template\ParserContext;
use Thelia\Form\Exception\FormValidationException;
use Thelia\Tools\URL;

#[Route('/admin/module/RewriteUrl/manageErrorUrl', name: 'admin_rewrite_url_manage_error_url_')]
class ManageErrorUrlController extends BaseAdminController
{
    #[Route('', name: 'show', methods: ['GET'])]
    public function manageErrorUrl(Request $request): Response|RedirectResponse
    {
        $search = $request->query->get('search');
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = 20;

        $query = RewriteurlErrorUrlQuery::create()->orderByUpdatedAt();

        if (!empty($search)) {
            $query
                ->filterByUrlSource('%' . $search . '%', \Propel\Runtime\ActiveQuery\Criteria::LIKE)
                ->_or()
                ->filterByUserAgent('%' . $search . '%', \Propel\Runtime\ActiveQuery\Criteria::LIKE);
        }

        $total = (clone $query)->count();

        $errorUrls = [];
        $rows = $query
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->find();

        foreach ($rows as $errorUrl) {
            $rule = $errorUrl->getRewriteUrlRule();
            $errorUrls[] = [
                'ID' => $errorUrl->getId(),
                'URL_SOURCE' => $errorUrl->getUrlSource(),
                'USER_AGENT' => $errorUrl->getUserAgent(),
                'COUNT' => $errorUrl->getCount(),
                'UPDATED_AT' => $errorUrl->getUpdatedAt(),
                'REDIRECT' => $rule !== null ? $rule->getRedirectUrl() : '',
            ];
        }

        return $this->render('manage-errors-url', [
            'errorUrls' => $errorUrls,
            'search' => $search,
            'page' => $page,
            'pageCount' => (int) ceil(max(1, $total) / $limit),
            'updateForm' => $this->createForm(UpdateRewriteUrlForm::getName())->getForm()->createView(),
        ]);
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

    /**
     * @throws PropelException
     */
    #[Route('/referer/{id}', name: 'get_referer')]
    public function getReferer($id): JsonResponse
    {
        $referees = RewriteurlErrorUrlRefererQuery::create()->filterByRewriteurlErrorUrlId($id)->find();

        $result = array_map(function (RewriteurlErrorUrlReferer $referer){
            return $referer->getReferer();
        }, $referees->getData());

        return new JsonResponse(['referer' => $result]);
    }

    #[Route('/search', name: 'search_url')]
    public function search(Request $request): Response|RedirectResponse
    {
        return $this->generateRedirect(URL::getInstance()?->absoluteUrl($request->get('success_url'), ['search'=>$request->get('search_term')]));
    }
}