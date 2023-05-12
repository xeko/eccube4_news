<?php

namespace Plugin\NewsUpgrade\EventListener;

use Eccube\Request\Context;
use Eccube\Repository\NewsRepository;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class TintucPagesListener implements EventSubscriberInterface
{
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


    public function __construct(RequestStack $requestStack, Context $requestContext, NewsRepository $newsRepository)
    {
        $this->requestStack = $requestStack;
        $this->requestContext = $requestContext;
        $this->newsRepository = $newsRepository;
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        if (!$event->isMainRequest()) {
            return;
        }
        if ($this->requestContext->isFront()) {
            $request = $event->getRequest();
            $pathInfo = $request->getPathInfo();
            if( strpos($pathInfo,'/news/') === false ){
                return;
            }
            
            $response = $event->getResponse();
            $content = $response->getContent();
            
            $News = $this->newsRepository->find( basename( $pathInfo ) );

            $title = $News->getTtseoTitle();
            if( $title !== null ){
                $title = "<title>{$title}</title>";
                preg_match('/\<title\>(.*?)\<\/title\>/s', $content, $matches_title);
                if( $matches_title != false){
                $content = str_replace( $matches_title[0] , $title, $content);
                }else{
                $content = str_replace( "</head>" , $title."\r\n</head>" , $content);
                }
            }

            $description = $News->getTtseoDescription();

            if( $description !== null ){
                $description = "<meta name=\"description\" content=\"{$description}\" >";
                preg_match('/\<meta name=\"description\" (.*?)\>/s', $content, $matches_description);

                if( $matches_description != false){
                $content = str_replace( $matches_description[0] , $description, $content);
                }else{
                $content = str_replace( "</head>" , $description."\r\n</head>", $content);
                }
            }


            $robots = $News->getTtseoRobots();
            if( $robots !== null ){
                $robots = "<meta name=\"robots\" content=\"{$robots}\" >";
                preg_match('/\<meta name=\"robots\" (.*?)\>/', $content, $matches_robots);

                if( $matches_robots != false){
                $content = str_replace( $matches_robots[0] , $robots, $content);
                }else{
                $content = str_replace( "</head>" , $robots."\r\n</head>", $content);
                }
            }

            $response->setContent($content);

        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse', 512],
        ];
    }

}