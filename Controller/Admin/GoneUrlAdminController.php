<?php

namespace RewriteUrl\Controller\Admin;

use Propel\Runtime\Exception\PropelException;
use RewriteUrl\Model\RewriteurlGoneUrlQuery;
use Symfony\Component\Routing\Annotation\Route;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\HttpFoundation\JsonResponse;
use Thelia\Core\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

#[Route('/admin/module/RewriteUrl/manageGoneUrl', name: 'admin_rewrite_url_manage_gone_url_')]
class GoneUrlAdminController extends BaseAdminController
{
    #[Route('', name: 'show', methods: ['GET'])]
    public function manageGoneUrl(): Response|RedirectResponse
    {
        return $this->render('manage-gone-url');
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