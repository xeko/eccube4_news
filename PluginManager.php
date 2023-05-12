<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * https://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\NewsUpgrade;

use Eccube\Entity\Layout;
use Eccube\Entity\Page;
use Eccube\Entity\Block;
use Eccube\Entity\BlockPosition;
use Eccube\Entity\Master\DeviceType;

use Eccube\Entity\PageLayout;
use Eccube\Plugin\AbstractPluginManager;

use Eccube\Repository\LayoutRepository;
use Eccube\Repository\PageLayoutRepository;
use Eccube\Repository\PageRepository;
use Eccube\Repository\BlockRepository;
use Eccube\Repository\Master\DeviceTypeRepository;


use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;

class PluginManager extends AbstractPluginManager
{

    private $pluginOrgFileDir = __DIR__.'/Resource/template/';

    private $createPages = array(
      [
        'name' => '[Tintuc] Chi tiết',
        'url' => 'news_detail',
        'fileName' => 'News/detail'
      ],
      [
        'name' => '[Tintuc] Dabh sách',
        'url' => 'news_index',
        'fileName' => 'News/index'
      ]
    );

    private $createBlocks = array(
      [
        'name' => '[Tintuc] New',
        'fileName' => 'tt_recent_news'
      ]
    );

    public function enable(array $meta, ContainerInterface $container)
    {
        $this->copyFiles($container);

        $entityManager = $container->get('doctrine')->getManager();
        $PageLayout = $entityManager->getRepository(Page::class)->findOneBy(['url' => $this->createPages[0]['url'] ]);
        if (is_null($PageLayout)) {
            $this->createPageLayout($container);
        }
        foreach( $this->createBlocks as $createBlock ):
          $Block = $entityManager->getRepository(Block::class)->findOneBy(['file_name' => $createBlock['fileName'] ]);
          if ( is_null($Block) ) {
              $this->createDataBlock($container , $createBlock );
          }
        endforeach;
        
    }

    /**
     * @param array $meta
     * @param ContainerInterface $container
     */
    public function disable(array $meta, ContainerInterface $container)
    {
        $entityManager = $container->get('doctrine')->getManager();
        
        $this->removePageLayout($container);
        $this->removeFiles($container);
        foreach( $this->createBlocks as $createBlock ):
          $Block = $entityManager->getRepository(Block::class)->findOneBy(['file_name' => $createBlock['fileName'] ]);
          if ( !is_null($Block) ) {
              $this->removeDataBlock($container , $createBlock );
          }
        endforeach;
    }

    /**
     * @param ContainerInterface $container
     */
    private function createPageLayout(ContainerInterface $container)
    {

        $pages = $this->createPages;
        foreach( (array)$pages as $p ){

          /** @var \Eccube\Entity\Page $Page */
          $entityManager = $container->get('doctrine')->getManager();

          $Page = $entityManager->getRepository(Page::class)->newPage();

          $Page->setEditType(Page::EDIT_TYPE_DEFAULT);
          $Page->setName( $p['name'] );
          $Page->setUrl( $p['url'] );
          $Page->setFileName( $p['fileName'] );

          // DB登録
          $entityManager = $container->get('doctrine')->getManager();
          $entityManager->persist($Page);
          $entityManager->flush($Page);

          $Layout = $entityManager->getRepository(Layout::class)->find(Layout::DEFAULT_LAYOUT_UNDERLAYER_PAGE);
          $PageLayout = new PageLayout();
          $PageLayout->setPage($Page)
              ->setPageId($Page->getId())
              ->setLayout($Layout)
              ->setLayoutId($Layout->getId())
              ->setSortNo(0);

          $entityManager->persist($PageLayout);
          $entityManager->flush($PageLayout);

        }

    }

    /**
     * ページレイアウトを削除.
     *
     * @param ContainerInterface $container
     */
    private function removePageLayout(ContainerInterface $container)
    {

        $pages = $this->createPages;
        foreach( $pages as $p ){
            $entityManager = $container->get('doctrine')->getManager();
            $Page = $entityManager->getRepository(Page::class)->findOneBy(['url' => $p['url'] ]);
            if ($Page) {
                $Layout = $entityManager->getRepository(Layout::class)->find(Layout::DEFAULT_LAYOUT_UNDERLAYER_PAGE);
                $PageLayout = $entityManager->getRepository(PageLayout::class)->findOneBy(['Page' => $Page, 'Layout' => $Layout]);
                // Blockの削除
                $entityManager = $container->get('doctrine')->getManager();
                $entityManager->remove($PageLayout);
                $entityManager->remove($Page);
                $entityManager->flush();
            }
        }
    }

    /**
     * Copy block template.
     *
     * @param ContainerInterface $container
     */
    private function copyFiles(ContainerInterface $container)
    {
        $appTemplateDefDir = $container->getParameter('eccube_theme_front_dir');
        $appTemplateAdminDir = $container->getParameter('eccube_theme_admin_dir');

        $file = new Filesystem();

        $file->copy($this->pluginOrgFileDir . 'default/News/detail.twig' , $appTemplateDefDir.'/News/detail.twig' );
        $file->copy($this->pluginOrgFileDir . 'default/News/index.twig' , $appTemplateDefDir.'/News/index.twig' );
        $file->copy($this->pluginOrgFileDir . 'default/Block/tt_recent_news.twig' , $appTemplateDefDir.'/Block/tt_recent_news.twig' );
    }

    /**
     * Remove block template.
     *
     * @param ContainerInterface $container
     */
    private function removeFiles(ContainerInterface $container)
    {
        $appTemplateDefDir = $container->getParameter('eccube_theme_front_dir');
        $appTemplateAdminDir = $container->getParameter('eccube_theme_admin_dir');

        $file = new Filesystem();

        $file->remove( $appTemplateDefDir.'/News/detail.twig' );
        $file->remove( $appTemplateDefDir.'/News/index.twig' );
        $file->remove( $appTemplateDefDir.'/Block/tt_recent_news.twig' );
    }


    /**
     * ブロックを登録.
     *
     * @param ContainerInterface $container
     *
     * @throws \Exception
     */
    private function createDataBlock(ContainerInterface $container, $createBlock )
    {
        $entityManager = $container->get('doctrine')->getManager();
        $DeviceType = $entityManager->getRepository(DeviceType::class)->find(DeviceType::DEVICE_TYPE_PC);

        try {
            /** @var Block $Block */
            $Block = $entityManager->getRepository(Block::class)->newBlock($DeviceType);

            // Blockの登録
            $Block->setName( $createBlock['name'] )
                ->setFileName( $createBlock['fileName'] )
                ->setUseController(false)
                ->setDeletable(false);
            $entityManager->persist($Block);
            $entityManager->flush($Block);

        } catch (\Exception $e) {
            throw $e;
        }

    }

    /**
     * ブロックを削除.
     *
     * @param ContainerInterface $container
     *
     * @throws \Exception
     */
    private function removeDataBlock(ContainerInterface $container, $createBlock)
    {
        
        $entityManager = $container->get('doctrine')->getManager();
        $Block = $entityManager->getRepository(Block::class)->findOneBy([ 'file_name' => $createBlock['fileName'] ]);

        if (!$Block) {
            return;
        }

        try {
            // BlockPositionの削除
            $blockPositions = $Block->getBlockPositions();
            /** @var \Eccube\Entity\BlockPosition $BlockPosition */
            foreach ($blockPositions as $BlockPosition) {
                $Block->removeBlockPosition($BlockPosition);
                $entityManager->remove($BlockPosition);
            }

            // Blockの削除
            $entityManager->remove($Block);
            $entityManager->flush();
        } catch (\Exception $e) {
            throw $e;
        }
    }

}