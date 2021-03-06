<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic Contributors. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
if ($tmpl == 'index')
$view->extend('MauticPageBundle:Page:index.html.php');
?>

<?php if (count($items)): ?>
    <div class="table-responsive page-list">
            <table class="table table-hover table-striped table-bordered pagetable-list" id="pageTable">
                <thead>
                <tr>
                    <?php
                    echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', array(
                        'checkall' => 'true',
                        'target'   => '#pageTable'
                    ));

                    echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', array(
                        'sessionVar' => 'page',
                        'orderBy'    => 'p.title',
                        'text'       => 'mautic.core.title',
                        'class'      => 'col-page-title',
                        'default'    => true
                    ));

                    echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', array(
                        'sessionVar' => 'page',
                        'orderBy'    => 'c.title',
                        'text'       => 'mautic.core.category',
                        'class'      => 'visible-md visible-lg col-page-category'
                    ));

                    echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', array(
                        'sessionVar' => 'page',
                        'orderBy'    => 'p.hits',
                        'text'       => 'mautic.page.thead.hits',
                        'class'      => 'col-page-hits visible-md visible-lg'
                    ));

                    echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', array(
                        'sessionVar' => 'page',
                        'orderBy'    => 'p.id',
                        'text'       => 'mautic.core.id',
                        'class'      => 'col-page-id visible-md visible-lg'
                    ));
                    ?>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                    <?php
                    $variantChildren     = $item->getVariantChildren();
                    $translationChildren = $item->getTranslationChildren();
                    ?>
                    <tr>
                        <td>
                            <?php
                            echo $view->render('MauticCoreBundle:Helper:list_actions.html.php', array(
                                'item'      => $item,
                                'templateButtons' => array(
                                    'edit'      => $security->hasEntityAccess($permissions['page:pages:editown'], $permissions['page:pages:editother'], $item->getCreatedBy()),
                                    'clone'     => $permissions['page:pages:create'],
                                    'delete'    => $security->hasEntityAccess($permissions['page:pages:deleteown'], $permissions['page:pages:deleteother'], $item->getCreatedBy()),
                                ),
                                'routeBase' => 'page',
                                'nameGetter' => 'getTitle'
                            ));
                            ?>
                        </td>
                        <td>
                            <?php echo $view->render('MauticCoreBundle:Helper:publishstatus_icon.html.php',array('item' => $item, 'model' => 'page.page')); ?>
                            <a href="<?php echo $view['router']->generate('mautic_page_action', array("objectAction" => "view", "objectId" => $item->getId())); ?>" data-toggle="ajax">
                                <?php echo $item->getTitle(); ?> (<?php echo $item->getAlias(); ?>)
                                <?php
                                $hasVariants   = count($variantChildren);
                                $hasTranslations = count($translationChildren);
                                if ($hasVariants || $hasTranslations): ?>
                                    <span>
                                <?php if ($hasVariants): ?>
                                        <span data-toggle="tooltip" title="<?php echo $view['translator']->trans('mautic.page.icon_tooltip.abtest'); ?>"><i class="fa fa-fw fa-sitemap"></i></span>
                                <?php endif; ?>
                                        <?php if ($hasTranslations): ?>
                                        <span data-toggle="tooltip" title="<?php echo $view['translator']->trans('mautic.page.icon_tooltip.translation'); ?>"><i class="fa fa-fw fa-language"></i></span>
                                        <?php endif; ?>
                                 </span>
                                <?php endif; ?>
                            </a>
                        </td>
                        <td class="visible-md visible-lg">
                            <?php $category = $item->getCategory(); ?>
                            <?php $catName  = ($category) ? $category->getTitle() : $view['translator']->trans('mautic.core.form.uncategorized'); ?>
                            <?php $color    = ($category) ? '#' . $category->getColor() : 'inherit'; ?>
                            <span style="white-space: nowrap;"><span class="label label-default pa-4" style="border: 1px solid #d5d5d5; background: <?php echo $color; ?>;"> </span> <span><?php echo $catName; ?></span></span>
                        </td>
                        <td class="visible-md visible-lg"><?php echo $item->getHits(); ?></td>
                        <td class="visible-md visible-lg"><?php echo $item->getId(); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <div class="panel-footer">
        <?php echo $view->render('MauticCoreBundle:Helper:pagination.html.php', array(
            "totalItems"      => count($items),
            "page"            => $page,
            "limit"           => $limit,
            "menuLinkId"      => 'mautic_page_index',
            "baseUrl"         => $view['router']->generate('mautic_page_index'),
            'sessionVar'      => 'page'
        )); ?>
        </div>
    </div>
<?php else: ?>
    <?php echo $view->render('MauticCoreBundle:Helper:noresults.html.php'); ?>
<?php endif; ?>
