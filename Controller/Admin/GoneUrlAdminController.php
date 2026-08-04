<?php

namespace RewriteUrl\Controller\Admin;

use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Exception\PropelException;
use RewriteUrl\Model\RewriteurlGoneUrlQuery;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\HttpFoundation\JsonResponse;
use Thelia\Core\HttpFoundation\Response;
use Thelia\Tools\URL;

#[Route('/admin/module/RewriteUrl/manageGoneUrl', name: 'admin_rewrite_url_manage_gone_url_')]
class GoneUrlAdminController extends BaseAdminController
{
    /**
     * The Smarty template builds its own list with the rewrite_url_gone_url_loop, the Twig
     * one uses the rows given here.
     *
     * @throws PropelException
     */
    #[Route('', name: 'show', methods: ['GET'])]
    public function manageGoneUrl(Request $request): Response|RedirectResponse
    {
        $search = $request->query->get('search');
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = 20;

        $query = RewriteurlGoneUrlQuery::create()->orderByCreatedAt(Criteria::DESC);

        if (!empty($search)) {
            $query->filterByUrlSource('%' . $search . '%', Criteria::LIKE);
        }

        $total = (clone $query)->count();

        $goneUrls = [];

        foreach ($query->offset(($page - 1) * $limit)->limit($limit)->find() as $goneUrl) {
            $goneUrls[] = [
                'ID' => $goneUrl->getId(),
                'URL_SOURCE' => $goneUrl->getUrlSource(),
                'CREATED_AT' => $goneUrl->getCreatedAt(),
                'UPDATED_AT' => $goneUrl->getUpdatedAt(),
            ];
        }

        return $this->render('manage-gone-url', [
            'goneUrls' => $goneUrls,
            'search' => $search,
            'page' => $page,
            'pageCount' => (int) ceil(max(1, $total) / $limit),
        ]);
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
