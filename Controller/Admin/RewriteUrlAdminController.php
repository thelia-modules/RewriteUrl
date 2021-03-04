<?php
/*************************************************************************************/
/*      This file is part of the RewriteUrl module for Thelia.                       */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : dev@thelia.net                                                       */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace RewriteUrl\Controller\Admin;

use Propel\Runtime\ActiveQuery\Criteria;
use RewriteUrl\Model\RewritingRedirectType;
use RewriteUrl\Model\RewritingRedirectTypeQuery;
use RewriteUrl\Model\RewritingUrlOverride;
use RewriteUrl\Event\RewriteUrlEvent;
use RewriteUrl\Event\RewriteUrlEvents;
use RewriteUrl\Form\AddUrlForm;
use RewriteUrl\Form\ReassignForm;
use RewriteUrl\Form\SetDefaultForm;
use RewriteUrl\RewriteUrl;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Core\HttpFoundation\Response;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Core\Template\ParserContext;
use Thelia\Core\Translation\Translator;
use Thelia\Form\Exception\FormValidationException;
use Thelia\Model\BrandI18nQuery;
use Thelia\Model\CategoryI18nQuery;
use Thelia\Model\ContentI18nQuery;
use Thelia\Model\FolderI18nQuery;
use Thelia\Model\ProductI18nQuery;
use Thelia\Model\ProductQuery;
use Thelia\Model\RewritingUrl;
use Thelia\Model\RewritingUrlQuery;
use Thelia\Tools\URL;
use Thelia\Log\Tlog;

/**
 * Class RewriteUrlAdminController
 * @package RewriteUrl\Controller\Admin
 * @author Vincent Lopes <vlopes@openstudio.fr>
 * @author Gilles Bourgeat <gbourgeat@openstudio.fr>
 */
class RewriteUrlAdminController extends BaseAdminController
{
    /** @var array */
    private $correspondence = array(
        'brand'     => 'brand',
        'category'  => 'categories',
        'content'   => 'content',
        'folder'    => 'folders',
        'product'   => 'products'
    );

    /**
     * @return mixed
     */
    public function deleteAction(
        Request $request,
        EventDispatcherInterface $dispatcher
    ) {
        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), 'RewriteUrl', AccessManager::DELETE)) {
            return $response;
        }

        $urlId = $request->request->get('id_url');
        $rewritingUrl = RewritingUrlQuery::create()->findOneById($urlId);

        if ($rewritingUrl !== null) {
            $event = new RewriteUrlEvent($rewritingUrl);
            $dispatcher->dispatch($event,RewriteUrlEvents::REWRITEURL_DELETE);
        }

        return $this->generateRedirectFromRoute(
            'admin.'.$this->correspondence[$rewritingUrl->getView()].'.update',
            [
                $rewritingUrl->getView().'_id'=>$rewritingUrl->getViewId(),
                'current_tab' => 'modules'
            ],
            [
                $rewritingUrl->getView().'_id'=>$rewritingUrl->getViewId()
            ]
        );
    }

    /**
     * @return mixed
     */
    public function addAction(EventDispatcherInterface $dispatcher, ParserContext $parserContext)
    {
        $message = null;
        $exception = null;

        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), 'RewriteUrl', AccessManager::CREATE)) {
            return $response;
        }

        $addForm = $this->createForm(AddUrlForm::getName());

        try {
            $form = $this->validateForm($addForm);
            $data = $form->getData();

            $findExist = RewritingUrlQuery::create()->findOneByUrl(($data['url']));

            if ($findExist !== null && !in_array($findExist->getView(), RewriteUrl::getUnknownSources()) && $findExist->getView() !== '') {
                $url = $this->generateUrlByRewritingUrl($findExist);

                throw new \Exception(
                    Translator::getInstance()->trans(
                        "This url is already used here %url.",
                        ['%url' => '<a href="' . $url . '">' . $url . '</a>'],
                        RewriteUrl::MODULE_DOMAIN
                    )
                );
            }

            $rewriting = $findExist !== null ? $findExist : new RewritingUrlOverride();
            $rewriting->setUrl($data['url'])
            ->setView($data['view'])
            ->setViewId($data['view-id'])
            ->setViewLocale($data['locale']);

            $rewriteDefault = RewritingUrlQuery::create()
                ->filterByView($rewriting->getView())
                ->filterByViewId($rewriting->getViewId())
                ->filterByViewLocale($rewriting->getViewLocale())
                ->findOneByRedirected(null);

            $redirectType = null;

            if ($data['default'] == 1) {
                $rewriting->setRedirected(null);
            } else {
                if ($rewriteDefault !== null) {
                    $rewriting->setRedirected($rewriteDefault->getId());
                    $redirectType = RewritingRedirectTypeQuery::create()
                        ->filterById($rewriting->getId())
                        ->findOneOrCreate();
                    $redirectType->setHttpcode($data['httpcode']);
                } else {
                    $rewriting->setRedirected(null);
                }
            }

            $dispatcher->dispatch(
                new RewriteUrlEvent($rewriting, $redirectType),
                RewriteUrlEvents::REWRITEURL_ADD
            );

            return $this->generateSuccessRedirect($addForm);
        } catch (FormValidationException $exception) {
            $message = $this->createStandardFormValidationErrorMessage($exception);
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
        }

        if ($message !== null && $exception !== null) {
            Tlog::getInstance()->error(sprintf("Error during order delivery process : %s. Exception was %s", $message, $exception->getMessage()));

            $addForm->setErrorMessage($message);

            $parserContext
                ->addForm($addForm)
                ->setGeneralError($message);
        }

        return $this->generateSuccessRedirect($addForm);
    }

    /**
     * @return mixed
     */
    public function setDefaultAction(Request $request, EventDispatcherInterface $dispatcher)
    {
        if (null !== $response = $this->checkAuth(AdminResources::MODULE, 'RewriteUrl', AccessManager::UPDATE)) {
            return $response;
        }

        $urlId = $request->request->get('id_url');
        $rewritingUrl = RewritingUrlQuery::create()->findOneById($urlId);

        if ($rewritingUrl !== null) {
            $event = new RewriteUrlEvent($rewritingUrl);
            $dispatcher->dispatch($event,RewriteUrlEvents::REWRITEURL_SET_DEFAULT);
        }

        return $this->generateRedirectFromRoute(
            'admin.'.$this->correspondence[$rewritingUrl->getView()].'.update',
            [
                $rewritingUrl->getView().'_id'=>$rewritingUrl->getViewId(),
                'current_tab' => 'modules'
            ],
            [
                $rewritingUrl->getView().'_id'=>$rewritingUrl->getViewId()
            ]
        );
    }


    public function changeRedirectTypeAction(Request $request, EventDispatcherInterface $dispatcher)
    {
        if (null !== $response = $this->checkAuth(AdminResources::MODULE, 'RewriteUrl', AccessManager::UPDATE)) {
            return $response;
        }

        $urlId = $request->get('id_url');
        $httpcode = $request->get('httpcode');
        $rewritingUrl = RewritingUrlQuery::create()->findOneById($urlId);

        if ($rewritingUrl !== null) {
            $rewritingRedirectType = RewritingRedirectTypeQuery::create()
                ->filterById($urlId)
                ->findOneOrCreate();
            $rewritingRedirectType->setHttpcode($httpcode);

            $event = new RewriteUrlEvent($rewritingUrl, $rewritingRedirectType);
            $dispatcher->dispatch($event,RewriteUrlEvents::REWRITEURL_UPDATE);
        }

        return new Response("", Response::HTTP_OK);
    }

    /**
     * @return mixed
     */
    public function reassignAction(EventDispatcherInterface $dispatcher, ParserContext $parserContext)
    {
        if (null !== $response = $this->checkAuth(AdminResources::MODULE, 'RewriteUrl', AccessManager::UPDATE)) {
            return $response;
        }

        $reassignForm = $this->createForm(ReassignForm::getName());

        try {
            $form = $this->validateForm($reassignForm);
            $data = $form->getData();

            $all = $data['all'];
            $newRewrite = explode('::', $data['select-reassign']);
            $rewriteId = $data['rewrite-id'];
            $newView = $newRewrite[1];
            $newViewId = $newRewrite[0];

            if ($all === 1) {
                self::allReassign($dispatcher, $rewriteId, $newView, $newViewId);
            } else {
                self::simpleReassign($dispatcher, $rewriteId, $newView, $newViewId);
            }

            return $this->generateSuccessRedirect($reassignForm);
        } catch (FormValidationException $e) {
            $message = $this->createStandardFormValidationErrorMessage($e);
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        $reassignForm->setErrorMessage($message);

        $parserContext
            ->addForm($reassignForm)
            ->setGeneralError($message)
        ;

        return $this->generateSuccessRedirect($reassignForm);
    }

    /**
     * @return mixed|\Thelia\Core\HttpFoundation\Response
     */
    public function existAction(Request $request)
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), 'RewriteUrl', AccessManager::VIEW)) {
            return $response;
        }

        $search = $request->query->get('q');

        $rewritingUrl = RewritingUrlQuery::create()
            ->filterByView(RewriteUrl::getUnknownSources(), Criteria::NOT_IN)
            ->findOneByUrl($search);

        if ($rewritingUrl !== null) {
            if (!in_array($rewritingUrl->getView(), $this->correspondence)) {
                return $this->jsonResponse(json_encode(false));
            }

            $rewritingUrlArray = ["reassignUrl" => $this->generateUrlByRewritingUrl($rewritingUrl)];

            return $this->jsonResponse(json_encode($rewritingUrlArray));
        } else {
            return $this->jsonResponse(json_encode(false));
        }
    }

    /**
     * @return mixed|\Thelia\Core\HttpFoundation\Response
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function searchAction(Request $request)
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), 'RewriteUrl', AccessManager::VIEW)) {
            return $response;
        }

        $search = '%'.$request->query->get('q').'%';

        $resultArray = array();

        $categoriesI18n = CategoryI18nQuery::create()->filterByTitle($search, Criteria::LIKE)->limit(10);
        $contentsI18n = ContentI18nQuery::create()->filterByTitle($search, Criteria::LIKE)->limit(10);
        $foldersI18n = FolderI18nQuery::create()->filterByTitle($search, Criteria::LIKE)->limit(10);
        $brandsI18n = BrandI18nQuery::create()->filterByTitle($search, Criteria::LIKE)->limit(10);

        $productsI18n = ProductI18nQuery::create()->filterByTitle($search, Criteria::LIKE)->limit(10);
        $productsRef = ProductQuery::create()->filterByRef($search, Criteria::LIKE)->limit(10);

        /** @var \Thelia\Model\CategoryI18n $categoryI18n */
        foreach ($categoriesI18n as $categoryI18n) {
            $category = $categoryI18n->getCategory();
            $resultArray['category'][$category->getId()] = $categoryI18n->getTitle();
        }

        /** @var \Thelia\Model\ContentI18n $contentI18n */
        foreach ($contentsI18n as $contentI18n) {
            $content = $contentI18n->getContent();
            $resultArray['content'][$content->getId()] = $contentI18n->getTitle();
        }

        /** @var \Thelia\Model\FolderI18n $folderI18n */
        foreach ($foldersI18n as $folderI18n) {
            $folder = $folderI18n->getFolder();
            $resultArray['folder'][$folder->getId()] = $folderI18n->getTitle();
        }

        /** @var \Thelia\Model\BrandI18n $brandI18n */
        foreach ($brandsI18n as $brandI18n) {
            $brand = $brandI18n->getBrand();
            $resultArray['brand'][$brand->getId()] = $brandI18n->getTitle();
        }

        /** @var \Thelia\Model\ProductI18n $productI18n */
        foreach ($productsI18n as $productI18n) {
            $product = $productI18n->getProduct();
            $resultArray['product'][$product->getId()] = $product->getRef().' : '.$productI18n->getTitle();
        }

        /** @var \Thelia\Model\Product $product */
        foreach ($productsRef as $product) {
            $productI18n = ProductI18nQuery::create()->filterByProduct($product)->findOne();
            $resultArray['product'][$product->getId()] = $productI18n->getTitle();
        }

        return $this->jsonResponse(json_encode($resultArray));
    }

    /**
     * @param int $rewriteId
     * @param string $newView
     * @param int $newViewId
     */
    protected function allReassign(
        EventDispatcherInterface $dispatcher,
        $rewriteId,
        $newView,
        $newViewId
    ) {
        $origin = RewritingUrlQuery::create()->findOneById($rewriteId);

        $rewrites = RewritingUrlQuery::create()
            ->filterByView($origin->getView())
            ->filterByViewId($origin->getViewId())
            ->find();

        /** @var RewritingUrl $rewrite */
        foreach ($rewrites as $rewrite) {
            $destination = RewritingUrlQuery::create()
                ->filterByView($newView)
                ->filterByViewId($newViewId)
                ->filterByViewLocale($rewrite->getViewLocale())
                ->filterByRedirected(null)
                ->findOne();

            $rewrite
                ->setView($newView)
                ->setViewId($newViewId)
                ->setRedirected(($destination === null) ? null : $destination->getId());

            $dispatcher->dispatch(
                new RewriteUrlEvent($rewrite),
                RewriteUrlEvents::REWRITEURL_UPDATE
            );
        }
    }

    /**
     * @param int $rewriteId
     * @param string $newView
     * @param int $newViewId
     */
    protected function simpleReassign(
        EventDispatcherInterface $dispatcher,
        $rewriteId,
        $newView,
        $newViewId
    ) {
        $rewrite = RewritingUrlQuery::create()->findOneById($rewriteId);

        // add new default url
        if (null !== $newDefault = RewritingUrlQuery::create()->findOneByRedirected($rewrite->getId())) {
            $dispatcher->dispatch(
                new RewriteUrlEvent(
                    $newDefault->setRedirected(null)
                ),
                RewriteUrlEvents::REWRITEURL_UPDATE
            );
        }

        //Update urls who redirected to updated URL
        if (null !== $isRedirection = RewritingUrlQuery::create()->findByRedirected($rewrite->getId())) {
            /** @var \Thelia\Model\RewritingUrl $redirected */
            foreach ($isRedirection as $redirected) {
                $dispatcher->dispatch(
                    new RewriteUrlEvent(
                        $redirected->setRedirected(
                            ($newDefault !== null) ? $newDefault->getId() : $rewrite->getRedirected()
                        )
                    ),
                    RewriteUrlEvents::REWRITEURL_UPDATE
                );
            }
        }

        $rewrite->setView($newView)
            ->setViewId($newViewId);

        //Check if default url already exist for the view with the locale
        $rewriteDefault = RewritingUrlQuery::create()
            ->filterByView($newView)
            ->filterByViewId($newViewId)
            ->filterByViewLocale($rewrite->getViewLocale())
            ->findOneByRedirected(null);

        if ($rewriteDefault !== null) {
            $rewrite->setRedirected($rewriteDefault->getId());
        } else {
            $rewrite->setRedirected(null);
        }

        $event = new RewriteUrlEvent($rewrite);
        $dispatcher->dispatch($event,RewriteUrlEvents::REWRITEURL_UPDATE);
    }

    /**
     * @param RewritingUrl $rewritingUrl
     * @return null|string url
     */
    protected function generateUrlByRewritingUrl(RewritingUrl $rewritingUrl)
    {
        if (isset($this->correspondence[$rewritingUrl->getView()])) {
            $route = $this->getRoute(
                "admin.".$this->correspondence[$rewritingUrl->getView()] . ".update",
                [$rewritingUrl->getView().'_id' => $rewritingUrl->getViewId()]
            );
            return URL::getInstance()->absoluteUrl(
                $route,
                [$rewritingUrl->getView().'_id' => $rewritingUrl->getViewId(), 'current_tab' => 'modules']
            );
        }

        return null;
    }
}
