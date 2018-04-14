<?php
namespace IchHabRecht\MaskExport\Aggregate;

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

use IchHabRecht\MaskExport\CodeGenerator\BackendFluidCodeGenerator;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class BackendPreviewAggregate extends AbstractOverridesAggregate implements PlainTextFileAwareInterface
{
    use PlainTextFileAwareTrait;

    /**
     * @var BackendFluidCodeGenerator
     */
    protected $fluidCodeGenerator;

    /**
     * @var string
     */
    protected $pageTSConfigFileIdentifier = 'BackendPreview.ts';

    /**
     * @var string
     */
    protected $templatesFilePath = 'Resources/Private/Backend/Templates/';

    /**
     * @param array $maskConfiguration
     * @param BackendFluidCodeGenerator $fluidCodeGenerator
     */
    public function __construct(array $maskConfiguration, BackendFluidCodeGenerator $fluidCodeGenerator = null)
    {
        $this->fluidCodeGenerator = (null !== $fluidCodeGenerator) ? $fluidCodeGenerator : GeneralUtility::makeInstance(BackendFluidCodeGenerator::class);
        $this->table = 'tt_content';
        parent::__construct($maskConfiguration);
    }

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

        $this->addPageTsConfiguration();
        $this->addDrawItemHook();
        $this->replaceTableLabels();
        $this->addFluidTemplates();
    }

    protected function addPageTsConfiguration()
    {
        $contentTypes = array_keys($this->maskConfiguration[$this->table]['elements']);
        sort($contentTypes);

        $pageTSConfig = [];
        foreach ($contentTypes as $type) {
            $templateKey = GeneralUtility::underscoredToUpperCamelCase($type);
            $pageTSConfig[] = 'mod.web_layout.tt_content.preview.mask_' . $type
                . ' = EXT:mask/' . $this->templatesFilePath . 'Content/' . $templateKey . '.html';
        }

        $this->appendPlainTextFile(
            $this->pageTSConfigFilePath . $this->pageTSConfigFileIdentifier,
            implode("\n", $pageTSConfig) . "\n"
        );

        $this->appendPhpFile(
            'ext_localconf.php',
            <<<EOS
\\TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility::addPageTSConfig(
    '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:mask/{$this->pageTSConfigFilePath}{$this->pageTSConfigFileIdentifier}">'
);

EOS
        );
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

        $contentTypes = array_map(
            function ($key) {
                return 'mask_' . $key;
            },
            array_keys($this->maskConfiguration[$this->table]['elements'])
        );
        sort($contentTypes);
        $supportedContentTypes = var_export($contentTypes, true);

        $this->addPhpFile(
            'Classes/Hooks/PageLayoutViewDrawItem.php',
            <<<EOS
namespace MASK\Mask\Hooks;

use TYPO3\CMS\Backend\Form\Exception;
use TYPO3\CMS\Backend\Form\FormDataCompiler;
use TYPO3\CMS\Backend\Form\FormDataGroup\TcaDatabaseRecord;
use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Backend\View\PageLayoutViewDrawItemHookInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Fluid\View\StandaloneView;

class PageLayoutViewDrawItem implements PageLayoutViewDrawItemHookInterface
{
    /**
     * @var array
     */
    protected \$supportedContentTypes = {$supportedContentTypes};

    /**
     * Preprocesses the preview rendering of a content element.
     *
     * @param PageLayoutView \$parentObject
     * @param bool \$drawItem
     * @param string \$headerContent
     * @param string \$itemContent
     * @param array \$row
     */
    public function preProcess(PageLayoutView &\$parentObject, &\$drawItem, &\$headerContent, &\$itemContent, array &\$row)
    {
        if (!in_array(\$row['CType'], \$this->supportedContentTypes, true)) {
            return;
        }

        \$objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        \$view = \$objectManager->get(StandaloneView::class);
        
        \$formDataGroup = GeneralUtility::makeInstance(TcaDatabaseRecord::class);
        \$formDataCompiler = GeneralUtility::makeInstance(FormDataCompiler::class, \$formDataGroup);
        \$formDataCompilerInput = [
            'command' => 'edit',
            'tableName' => 'tt_content',
            'vanillaUid' => (int)\$row['uid'],
        ];
        try {
            \$result = \$formDataCompiler->compile(\$formDataCompilerInput);
            
            \$templatePath = \$this->getTemplatePath(\$result['pageTsConfig'], \$row['CType']);
            if (!file_exists(\$templatePath)) {
                return;
            }
        
            \$processedRow = \$this->getProcessedData(\$result['databaseRow'], \$result['processedTca']['columns']);
    
            \$view->setTemplatePathAndFilename(\$templatePath);
            \$view->assignMultiple(
                [
                    'row' => \$row,
                    'processedRow' => \$processedRow,
                ]
            );
    
            \$itemContent = \$view->render();
        } catch (Exception \$exception) {
            \$message = \$GLOBALS['BE_USER']->errorMsg;
            if (empty(\$message)) {
                \$message = \$exception->getMessage() . ' ' . \$exception->getCode();
            }

            \$itemContent = \$message;
        }
        
        \$drawItem = false;
    }

    /**
     * This function is needed for testing purpose
     *
     * @param array \$pageTsConfig
     * @param string \$contentType
     * @return string
     */
    protected function getTemplatePath(array \$pageTsConfig, \$contentType)
    {
        if (empty(\$pageTsConfig['mod.']['web_layout.']['tt_content.']['preview.'][\$contentType])) {
            return '';
        }

        return GeneralUtility::getFileAbsFileName(\$pageTsConfig['mod.']['web_layout.']['tt_content.']['preview.'][\$contentType]);
    }

    /**
     * @param array \$databaseRow
     * @param array \$processedTcaColumns
     * @return array
     */
    protected function getProcessedData(array \$databaseRow, array \$processedTcaColumns)
    {
        \$processedRow = \$databaseRow;
        foreach (\$processedTcaColumns as \$field => \$config) {
            if (!isset(\$config['children'])) {
                continue;
            }
            \$processedRow[\$field] = [];
            foreach (\$config['children'] as \$child) {
                if (!\$child['isInlineChildExpanded']) {
                    \$formDataGroup = GeneralUtility::makeInstance(TcaDatabaseRecord::class);
                    \$formDataCompiler = GeneralUtility::makeInstance(FormDataCompiler::class, \$formDataGroup);
                    \$formDataCompilerInput = [
                        'command' => 'edit',
                        'tableName' => \$child['tableName'],
                        'vanillaUid' => \$child['vanillaUid'],
                    ];
                    \$child = \$formDataCompiler->compile(\$formDataCompilerInput);
                }
                \$processedRow[\$field][] = \$this->getProcessedData(\$child['databaseRow'], \$child['processedTca']['columns']);
            }
        }

        return \$processedRow;
    }
}

EOS
        );
    }

    /**
     * Ensure proper labels in $GLOBALS['TCA'] configuration
     */
    protected function replaceTableLabels()
    {
        foreach ($this->maskConfiguration as $table => $_) {
            if (!isset($GLOBALS['TCA'][$table]) || empty($GLOBALS['TCA'][$table]['columns'])) {
                continue;
            }

            $GLOBALS['TCA'][$table]['columns'] = $this->replaceFieldLabels($GLOBALS['TCA'][$table]['columns'], $table);
            $GLOBALS['TCA'][$table]['columns'] = $this->replaceItemsLabels($GLOBALS['TCA'][$table]['columns'], $table);
        }
    }

    protected function addFluidTemplates()
    {
        foreach ($this->maskConfiguration[$this->table]['elements'] as $key => $element) {
            $templateKey = GeneralUtility::underscoredToUpperCamelCase($key);
            $templatePath = $this->templatesFilePath . 'Content/' . $templateKey . '.html';

            if (empty($element['columns'])) {
                $this->addPlainTextFile(
                    $templatePath,
                    <<<EOS
<strong>{$key}</strong>
EOS
                );
            } else {
                foreach ($element['columns'] as $field) {
                    $field = isset($GLOBALS['TCA'][$this->table]['columns'][$field]) ? $field : 'tx_mask_' . $field;
                    if (!isset($GLOBALS['TCA'][$this->table]['columns'][$field])) {
                        continue;
                    }
                    $this->appendPlainTextFile(
                        $templatePath,
                        $this->fluidCodeGenerator->generateFluid($this->table, $field)
                    );
                }
            }
        }
    }
}
