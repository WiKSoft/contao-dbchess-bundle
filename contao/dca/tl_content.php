<?php

use Contao\Backend;

/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_content']['palettes']['dbChess_list'] = '{type_legend},type,headline;{dbChess_list_element},dbChess_list_collection,'
        . 'dbChess_list_fields,dbChess_list_filter,dbChess_list_sortfields,dbChess_list_byorder,dbChess_list_jumpTo;{template_legend:hide},'
        . 'dbChess_list_template;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;{invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA']['tl_content']['palettes']['dbChess_download'] = '{type_legend},type,headline;{dbChess_list_element},dbChess_list_collection,'
        . 'dbChess_list_filter,dbChess_list_sortfields,dbChess_list_byorder,dbChess_dl_featured;{dwnconfig_legend},linkTitle,titleText;'
        . '{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;{invisible_legend:hide},invisible,start,stop';
/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_content']['fields']['dbChess_list_collection'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_content']['dbChess_list_collection'],
    'exclude' => true,
    'inputType' => 'checkboxWizard',
    'foreignKey' => 'tl_dbChess_collection.name',
    'eval' => array(
        'mandatory' => TRUE,
        'includeBlankOption' => FALSE,
        'submitOnChange' => FALSE,
        'multiple' => TRUE),
    'exclude' => true,
    'sql' => "blob NULL",
);

$GLOBALS['TL_DCA']['tl_content']['fields']['dbChess_list_fields'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_content']['dbChess_list_fields'],
    'exclude' => true,
    'inputType' => 'checkboxWizard',
    'options' => array('date', 'site', 'event', 'round', 'white', 'whiteelo', 'result', 'black', 'blackelo', 'eco', 'source', 'annotator', 'pgn'),
    'reference' => &$GLOBALS['TL_LANG']['tl_content']['dbChess_list_fields_option'],
    'eval' => array(
        'tl_class' => '',
        'multiple' => TRUE),
    'exclude' => true,
    'sql' => "blob NULL",
);

$GLOBALS['TL_DCA']['tl_content']['fields']['dbChess_list_filter'] = array
    (
    'label' => &$GLOBALS['TL_LANG']['tl_content']['dbChess_list_filter'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => array('tl_class' => 'long'),
    'sql' => "varchar(999) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_content']['fields']['dbChess_list_sortfields'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_content']['dbChess_list_sortfields'],
    'exclude' => true,
    'inputType' => 'checkboxWizard',
    'options' => array('event', 'site', 'date', 'round', 'result', 'white', 'black', 'eco', 'whiteelo', 'blackelo', 'annotator', 'source'),
    'reference' => &$GLOBALS['TL_LANG']['tl_content']['dbChess_list_fields_option'],
    'eval' => array(
        'tl_class' => 'w50 clr',
        'multiple' => TRUE),
    'exclude' => true,
    'sql' => "blob NULL",
);

$GLOBALS['TL_DCA']['tl_content']['fields']['dbChess_list_byorder'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_content']['dbChess_list_byorder'],
    'default' => 'a',
    'exclude' => true,
    'inputType' => 'radio',
    'options' => array('a', 'd'),
    'reference' => &$GLOBALS['TL_LANG']['tl_content']['dbChess_list_byorder_option'],
    'eval' => array(
        'tl_class' => 'w50'),
    'exclude' => true,
    'sql' => "varchar(1) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_content']['fields']['dbChess_list_template'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_content']['dbChess_list_template'],
    'default' => 'ce_dbChess_list_default',
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => array('tl_content_dbChess_list', 'get_dbChess_list_Templates'),
    'eval' => array(
        'tl_class' => ''),
    'sql' => "varchar(32) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_content']['fields']['dbChess_list_jumpTo'] = array
    (
    'label' => &$GLOBALS['TL_LANG']['tl_content']['dbChess_list_jumpTo'],
    'exclude' => true,
    'inputType' => 'pageTree',
    'foreignKey' => 'tl_page.title',
    'eval' => array(
        'tl_class' => 'clr',
        'mandatory' => false,
        'fieldType' => 'radio'),
    'sql' => "int(10) unsigned NOT NULL default '0'",
    'relation' => array('type' => 'hasOne', 'load' => 'eager')
);

$GLOBALS['TL_DCA']['tl_content']['fields']['dbChess_dl_featured'] = array
    (
    'label' => &$GLOBALS['TL_LANG']['tl_content']['dbChess_dl_featured'],
    'exclude' => true,
    'filter' => true,
    'inputType' => 'checkbox',
    'eval' => array('tl_class' => 'clr'),
    'sql' => "char(1) NOT NULL default ''"
);

class tl_content_dbChess_list extends Backend
{
    /**
     * Return all dbChess templates as array
     * @return array
     */
    public function get_dbChess_list_Templates()
    {
        return $this->getTemplateGroup('ce_dbChess_list');
    }
}
