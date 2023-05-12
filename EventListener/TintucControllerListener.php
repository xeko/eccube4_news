<?php

namespace Plugin\NewsUpgrade\EventListener;

use Eccube\Request\Context;
use Eccube\Repository\NewsRepository;
use Eccube\Event\EventArgs;
use Eccube\Common\EccubeConfig;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Filesystem\Filesystem;

class TintucControllerListener implements EventSubscriberInterface {

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var Context
     */
    protected $requestContext;

    /**
     * @var NewsRepository
     */
    protected $newsRepository;

    /**
     * @var EccubeConfig
     */
    private $eccubeConfig;

    public function __construct(RequestStack $requestStack, Context $requestContext, NewsRepository $newsRepository, EccubeConfig $eccubeConfig) {
        $this->requestStack = $requestStack;
        $this->requestContext = $requestContext;
        $this->newsRepository = $newsRepository;
        $this->eccubeConfig = $eccubeConfig;
    }

    public function saveNewsThumbnail(EventArgs $event) {
        $request = $event->getRequest();
        $form = $event->getArgument('form');
        $News = $event->getArgument('News');
        $tt_thumbnail_data = $form->get('tt_thumbnail_data')->getData();

        if ($tt_thumbnail_data !== null) {
            $filename = time() . '_' . $tt_thumbnail_data->getClientOriginalName();
            $file_save_dir = $this->eccubeConfig['eccube_save_image_dir'] . '/';
            try {
                $tt_thumbnail_data->move($file_save_dir, $filename);
            } catch (FileException $e) {
                log_info('Xay ra loi luu hinh anh', [$e]);
            }
            $News->setTtThumbnailUrl($filename);
            $this->newsRepository->save($News);
        }
    }
    
    public function delNewsThumbnail(EventArgs $event) {
        $News = $event->getArgument('News');
        $thumbName = $News->getTtThumbnailUrl();        
        $file_save_dir = $this->eccubeConfig['eccube_save_image_dir'] . '/';
        if ($thumbName !== null) {
            $fs = new Filesystem();
            try {
                $fs->remove($file_save_dir.$thumbName);
            } catch (FileException $ex) {
                log_info('Loi xoa anh thumbnail', [$ex]);
            }
        }
    }

    public static function getSubscribedEvents() {
        return [
            'admin.content.news.edit.complete' => ['saveNewsThumbnail', 512],
            'admin.content.news.delete.complete' => ['delNewsThumbnail', 512],
        ];
    }

}
