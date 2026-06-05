<?php

namespace RewriteUrl\Controller\Admin;

use Propel\Runtime\Exception\PropelException;
use RewriteUrl\Model\RewriteurlGoneUrlQuery;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\HttpFoundation\JsonResponse;
use Thelia\Core\HttpFoundation\Response;
use Thelia\Tools\URL;

#[Route('/admin/module/RewriteUrl/manageGoneUrl', name: 'admin_rewrite_url_manage_gone_url_')]
class GoneUrlAdminController extends BaseAdminController
{
    #[Route('', name: 'show', methods: ['GET'])]
    public function manageGoneUrl(): Response|RedirectResponse
    {
        return $this->render('manage-gone-url');
    }

    #[Route('/search', name: 'search', methods: ['GET'])]
    public function searchGoneUrl(Request $request): Response|RedirectResponse
    {
        $successUrl = $request->get('success_url') ?: '/admin/module/RewriteUrl/manageGoneUrl';
        $searchTerm = $request->get('search_term');

        return $this->generateRedirect(
            URL::getInstance()?->absoluteUrl($successUrl, ['search' => $searchTerm])
        );
    }

    #[Route('/clear', name: 'clear', methods: ['POST'])]
    public function clearGoneUrl(): Response|RedirectResponse
    {
        RewriteurlGoneUrlQuery::create()->deleteAll();

        return $this->generateRedirect('/admin/module/RewriteUrl/manageGoneUrl');
    }

    #[Route('/delete/{id}', name: 'delete', methods: ['POST'])]
    public function deleteGoneUrl(int $id): JsonResponse
    {
        try {
            RewriteurlGoneUrlQuery::create()->filterById($id)->delete();
        } catch (PropelException) {
            return new JsonResponse(['success' => false]);
        }

        return new JsonResponse(['success' => true]);
    }
}
