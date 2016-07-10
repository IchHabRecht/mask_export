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

/**
 * @package mask
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ExtensionConfigurationAggregate extends AbstractAggregate implements PhpAwareInterface
{
    use PhpAwareTrait;

    /**
     * Adds ext_emconf.php information
     */
    protected function process()
    {
        $typo3Constraint = sprintf(
            '%s.0-%s.99',
            TYPO3_branch,
            TYPO3_branch
        );
        $this->addPhpFile(
            'ext_emconf.php',
<<<EOS
\$EM_CONF[\$_EXTKEY] = array(
    'title' => 'mask',
    'description' => '',
    'category' => 'fe',
    'author' => '',
    'author_email' => '',
    'author_company' => '',
    'state' => 'stable',
    'version' => '0.1.0',
    'constraints' => array(
        'depends' => array(
            'typo3' => '{$typo3Constraint}',
        ),
        'conflicts' => array(),
        'suggests' => array(),
    ),
);

EOS
        );
    }
}
