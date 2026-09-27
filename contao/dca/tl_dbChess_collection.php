<?php

use Contao\DC_Table;

/**
 * Table tl_dbChess_collection
 */
$GLOBALS['TL_DCA']['tl_dbChess_collection'] = array
    (
    // Config
    'config' => array
        (
        'dataContainer'     => DC_Table::class,
        'ctable'            => array('tl_dbChess_games'),
        'enableVersioning'  => true,
        'switchToEdit'      => true,
        'sql'               => array
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
            'fields' => array('name'),
            'flag' => 1,
            'panelLayout' => 'filter;sort,search,limit'
        ),
        'label' => array
            (
            'fields' => array('name'),
            'format' => '%s',
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
                'label' => &$GLOBALS['TL_LANG']['tl_dbChess_collection']['edit'],
                'href' => 'table=tl_dbChess_games',
                'icon' => 'edit.gif'
            ),
            'editheader' => array
                (
                'label' => &$GLOBALS['TL_LANG']['tl_dbChess_collection']['editheader'],
                'href' => 'act=edit',
                'icon' => 'header.gif',
            ),
            'copy' => array
                (
                'label' => &$GLOBALS['TL_LANG']['tl_dbChess_collection']['copy'],
                'href' => 'act=copy',
                'icon' => 'copy.gif',
            ),
            'delete' => array
                (
                'label' => &$GLOBALS['TL_LANG']['tl_dbChess_collection']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null) . '\'))return false;Backend.getScrollOffset()"'
            ),
            'show' => array
                (
                'label' => &$GLOBALS['TL_LANG']['tl_dbChess_collection']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif',
                'attributes' => 'style="margin-right:3px"'
            ),
        )
    ),
    // Palettes
    'palettes' => array
        (
        'default' => '{title_legend},name'
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
        'name' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_dbChess_collection']['name'],
            'inputType' => 'text',
            'exclude' => true,
            'sorting' => true,
            'flag' => 1,
            'search' => true,
            'eval' => array(
                'mandatory' => true,
                'unique' => true,
                'maxlength' => 255,
                'tl_class' => 'w50',
            ),
            'sql' => "varchar(255) NOT NULL default ''"
        )
    )
);
