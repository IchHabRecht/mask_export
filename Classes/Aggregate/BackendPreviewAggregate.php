<?php

declare(strict_types=1);

namespace IchHabRecht\MaskExport\Aggregate;

/*
 * This file is part of the TYPO3 extension mask_export.
 *
 * (c) 2016 Nicole Cordes <typo3@cordes.co>, CPS-IT GmbH
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use IchHabRecht\MaskExport\CodeGenerator\BackendFluidCodeGenerator;
use TYPO3\CMS\Core\Utility\ArrayUtility;
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
    protected $pageTSConfigFileIdentifier = 'BackendPreview.tsconfig';

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
        $rootPaths = $this->getFluidRootPaths();

        $this->appendPlainTextFile(
            $this->pageTSConfigFilePath . $this->pageTSConfigFileIdentifier,
            <<<EOS
mod.web_layout.tt_content.preview.mask.templateRootPath = {$rootPaths['template']}
mod.web_layout.tt_content.preview.mask.layoutRootPath = {$rootPaths['layout']}
mod.web_layout.tt_content.preview.mask.partialRootPath = {$rootPaths['partials']}

EOS
        );

        $this->appendPhpFile(
            'ext_localconf.php',
            <<<EOS
\\TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility::addPageTSConfig(
    '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:mask/{$this->pageTSConfigFilePath}{$this->pageTSConfigFileIdentifier}">'
);

EOS
            ,
            PhpAwareInterface::PHPFILE_DEFINED_TYPO3_MODE | PhpAwareInterface::PHPFILE_CLOSURE_FUNCTION
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
            ,
            PhpAwareInterface::PHPFILE_DEFINED_TYPO3_MODE | PhpAwareInterface::PHPFILE_CLOSURE_FUNCTION
        );

        $rootPaths = $this->getFluidRootPaths();
        $contentTypes = [];
        foreach (array_keys($this->maskConfiguration[$this->table]['elements']) as $key) {
            $contentTypes['mask_' . $key] = GeneralUtility::underscoredToUpperCamelCase($key);
        }
        asort($contentTypes);
        $supportedContentTypes = ArrayUtility::arrayExport($contentTypes);

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
use TYPO3\CMS\Fluid\View\StandaloneView;

class PageLayoutViewDrawItem implements PageLayoutViewDrawItemHookInterface
{
    /**
     * @var array
     */
    protected \$supportedContentTypes = {$supportedContentTypes};

    /**
     * @var StandaloneView
     */
    protected \$view;

    public function __construct(StandaloneView \$view = null)
    {
        \$this->view = \$view ?: GeneralUtility::makeInstance(StandaloneView::class);
    }

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
        if (!isset(\$this->supportedContentTypes[\$row['CType']])) {
            return;
        }
        
        \$formDataGroup = GeneralUtility::makeInstance(TcaDatabaseRecord::class);
        \$formDataCompiler = GeneralUtility::makeInstance(FormDataCompiler::class, \$formDataGroup);
        \$formDataCompilerInput = [
            'command' => 'edit',
            'tableName' => 'tt_content',
            'vanillaUid' => (int)\$row['uid'],
        ];
        try {
            \$result = \$formDataCompiler->compile(\$formDataCompilerInput);
            \$processedRow = \$this->getProcessedData(\$result['databaseRow'], \$result['processedTca']['columns']);
            
            \$this->configureView(\$result['pageTsConfig'], \$row['CType']);
            \$this->view->assignMultiple(
                [
                    'row' => \$row,
                    'processedRow' => \$processedRow,
                ]
            );
    
            \$itemContent = \$this->view->render();
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
     * @param array \$pageTsConfig
     * @param string \$contentType
     */
    protected function configureView(array \$pageTsConfig, \$contentType)
    {
        if (empty(\$pageTsConfig['mod.']['web_layout.']['tt_content.']['preview.'])) {
            return;
        }

        \$previewConfiguration = \$pageTsConfig['mod.']['web_layout.']['tt_content.']['preview.'];
        list(\$extensionKey) = explode('_', \$contentType, 2);
        \$extensionKey .= '.';
        if (!empty(\$previewConfiguration[\$extensionKey]['templateRootPath'])) {
            \$this->view->setTemplateRootPaths([
                '{$rootPaths['template']}',
                \$previewConfiguration[\$extensionKey]['templateRootPath'],
            ]);
        }
        if (!empty(\$previewConfiguration[\$extensionKey]['layoutRootPath'])) {
            \$this->view->setLayoutRootPaths([
                \$previewConfiguration[\$extensionKey]['layoutRootPath'],
            ]);
        }
        if (!empty(\$previewConfiguration[\$extensionKey]['partialRootPath'])) {
            \$this->view->setPartialRootPaths([
                \$previewConfiguration[\$extensionKey]['partialRootPath'],
            ]);
        }
        \$this->view->setTemplate(\$this->supportedContentTypes[\$contentType]);
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

            $columns = $GLOBALS['TCA'][$table]['columns'];
            if ($table === 'tt_content' && !empty($this->maskConfiguration['tt_content']['elements'])) {
                $columns = [];
                foreach ($this->maskConfiguration['tt_content']['elements'] as $element) {
                    $columns = array_merge($columns, $element['columns'] ?? []);
                }
                $columns = array_combine($columns, $columns);
            }

            $columns = array_intersect_key(
                $GLOBALS['TCA'][$table]['columns'],
                $columns
            );

            $columns = $this->replaceFieldLabels($columns, $table);
            $columns = $this->replaceItemsLabels($columns, $table);

            $GLOBALS['TCA'][$table]['columns'] = array_replace(
                $GLOBALS['TCA'][$table]['columns'],
                $columns
            );
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

    protected function getFluidRootPaths()
    {
        $rootPath = dirname($this->templatesFilePath);

        return [
            'template' => 'EXT:mask/' . $this->templatesFilePath . 'Content/',
            'layout' => 'EXT:mask/' . $rootPath . '/Layout/',
            'partials' => 'EXT:mask/' . $rootPath . '/Partials/',
        ];
    }
}
