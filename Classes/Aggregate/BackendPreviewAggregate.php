<?php
namespace CPSIT\MaskExport\Aggregate;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Nicole Cordes <typo3@cordes.co>, CPS-IT GmbH
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @package mask
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class BackendPreviewAggregate extends AbstractOverridesAggregate implements PhpAwareInterface, PlainTextFileAwareInterface
{
    use PhpAwareTrait;
    use PlainTextFileAwareTrait;

    /**
     * @var string
     */
    protected $table = 'tt_content';

    /**
     * @var string
     */
    protected $templatesFilePath = 'Resources/Private/Backend/Templates/';

    /**
     * Adds PHP and Fluid files
     */
    protected function process()
    {
        $extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['mask_export']);
        if (empty($extensionConfiguration['backendPreview'])) {
            return;
        }

        if (empty($this->maskConfiguration[$this->table]['elements'])) {
            return;
        }

        $this->addDrawItemHook();
        $this->addFluidTemplates($this->maskConfiguration[$this->table]['elements']);
    }

    /**
     * This adds the PHP file with the hook to render own element template
     */
    protected function addDrawItemHook()
    {
        $this->appendPhpFile(
            'ext_localconf.php',
<<<EOS
// Add backend preview hook
\$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem']['mask'] = 
    MASK\Mask\Hooks\PageLayoutViewDrawItem::class;

EOS
        );

        $this->addPhpFile(
            'Classes/Hooks/PageLayoutViewDrawItem.php',
<<<EOS
namespace MASK\Mask\Hooks;

use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Backend\View\PageLayoutViewDrawItemHookInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Fluid\View\StandaloneView;

class PageLayoutViewDrawItem implements PageLayoutViewDrawItemHookInterface
{
    /**
     * Preprocesses the preview rendering of a content element.
     *
     * @param PageLayoutView \$parentObject
     * @param bool \$drawItem
     * @param string \$headerContent
     * @param string \$itemContent
     * @param array \$row
     */
    public function preProcess(PageLayoutView \$parentObject, &\$drawItem, &\$headerContent, &\$itemContent, array &\$row)
    {
    }
}

EOS
        );
    }

    /**
     * @param array $elements
     */
    protected function addFluidTemplates(array $elements)
    {
        foreach ($elements as $key => $element) {
            $this->addPlainTextFile(
                $this->templatesFilePath . GeneralUtility::underscoredToUpperCamelCase($key) . '.html',
<<<EOS

EOS
            );
        }
    }
}
