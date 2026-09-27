<?php

use Contao\DC_Table;

/**
 * Table tl_dbChess_eco
 */
$GLOBALS['TL_DCA']['tl_dbChess_eco'] = array
    (
    // Config
    'config' => array
        (
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'sql' => array
            (
            'keys' => array
                (
                'id' => 'primary'
            )
        ),
    ),
    // List
    'list' => array
        (
        'sorting' => array
            (
            'mode' => 2,
            'fields' => array('ecoCode'),
            'flag' => 1,
            'panelLayout' => 'filter;sort,search,limit'
        ),
        'label' => array
            (
            'fields' => array('ecoCode', 'ecoName'),
            'showColumns' => true,
            'format' => '%s'
        ),
        'global_operations' => array
            (
            'all' => array
                (
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            )
        ),
        'operations' => array
            (
            'edit' => array
                (
                'label' => &$GLOBALS['TL_LANG']['tl_dbChess_eco']['edit'],
                'href' => 'act=edit',
                'icon' => 'edit.gif'
            ),
            'delete' => array
                (
                'label' => &$GLOBALS['TL_LANG']['tl_dbChess_eco']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null) . '\'))return false;Backend.getScrollOffset()"'
            ),
            'show' => array
                (
                'label' => &$GLOBALS['TL_LANG']['tl_dbChess_eco']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif',
                'attributes' => 'style="margin-right:3px"'
            ),
        )
    ),
    // Palettes
    'palettes' => array
        (
        'default' => '{personal_legend_eco},ecoCode,ecoName'
    ),
    // Fields
    'fields' => array
        (
        'id' => array
            (
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ),
        'tstamp' => array
            (
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ),
        'ecoCode' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_dbChess_eco']['ecoCode'],
            'exclude' => true,
            'filter' => true,
            'search' => true,
            'sorting' => true,
            'flag' => 1,
            'inputType' => 'text',
            'eval' => array('decodeEntities' => true, 'maxlength' => 3, 'tl_class' => 'w50'),
            'sql' => "varchar(3) NOT NULL default ''"
        ),
        'ecoName' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_dbChess_eco']['ecoName'],
            'exclude' => true,
            'filter' => true,
            'search' => true,
            'sorting' => true,
            'flag' => 1,
            'inputType' => 'textarea',
            'eval' => array('decodeEntities' => true, 'tl_class' => 'clr'),
            'sql' => "text NULL"
        ),
    )
);
