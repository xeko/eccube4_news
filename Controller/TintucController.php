<?php

namespace Plugin\NewsUpgrade\Controller;

use Eccube\Controller\AbstractController;
use Plugin\NewsUpgrade\Repository\TintucRepository;
use Eccube\Entity\News;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraints as Assert;
use Knp\Component\Pager\PaginatorInterface;

class TintucController extends AbstractController {

    /**
     * @var TintucRepository
     */
    protected $newsRepository;

    /**
     * NewsController constructor.
     */
    public function __construct(
            TintucRepository $newsRepository
    ) {
        $this->newsRepository = $newsRepository;
    }

    /**
     * ニュース一覧画面.
     *
     * @Route( "/news" , name="news_index" )
     * @Template("News/index.twig")
     */
    public function index(Request $request, PaginatorInterface $paginator) {
        // handleRequestは空のqueryの場合は無視するため
        if ($request->getMethod() === 'GET') {
            $request->query->set('pageno', $request->query->get('pageno', '1'));
        }
        $qb = $this->newsRepository->getQueryBuilderPublished();
        $query = $qb->getQuery()->useResultCache(true, $this->eccubeConfig['eccube_result_cache_lifetime_short']);

        /** @var SlidingPagination $pagination */
        $pagination = $paginator->paginate(
                $query,
                $request->query->get('pageno', '1')
        );

        return [
            'pagination' => $pagination,
        ];
    }

    /**
     * ニュース詳細画面.
     *
     * @Route("/news/{id}" , name="news_detail" )
     * @Template("News/detail.twig")
     * @ParamConverter("News", options={"id" = "id"})
     */
    public function detail(Request $request, News $News) {

        if (!$this->checkVisibility($News)) {
            throw new NotFoundHttpException();
        }

        $NewsUrl = $News->getUrl();
        if ($NewsUrl !== null) {
            return new RedirectResponse($NewsUrl);
        }
        return [
            'news' => $News,
        ];
    }

    /**
     * 閲覧可能なニュースかどうかを判定
     *
     * @param News $News
     *
     * @return boolean 閲覧可能な場合はtrue
     */
    protected function checkVisibility(News $News) {
        $is_admin = $this->session->has('_security_admin');

        $date = time();

        // 管理ユーザの場合はステータスやオプションにかかわらず閲覧可能.
        if (!$is_admin) {
            // 公開ステータスでない商品は表示しない.
            if ($News->isVisible() === false) {
                return false;
            } elseif ($News->getPublishDate()->getTimestamp() >= $date) {
                return false;
            } else {
                return true;
            }
        }

        return true;
    }

}
