<?php

use Contao\Backend;

/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['dbChess_index'] = '{title_legend},name,headline,type;{dbChess_index_element},dbChess_index_collection,dbChess_index_fields,'
        . 'dbChess_index_sort,dbChess_index_byorder;{dbChess_index_detail},dbChess_index_detailFields,dbChess_index_exception,dbChess_index_jumpTo;{template_legend:hide},dbChess_index_template,dbChess_index_tag_buckets;';

/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['dbChess_index_collection'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['dbChess_index_collection'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'foreignKey' => 'tl_dbChess_collection.name',
    'eval' => array(
        'mandatory' => TRUE,
        'multiple' => TRUE),
    'exclude' => true,
    'sql' => "blob NULL",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['dbChess_index_fields'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['dbChess_index_fields'],
    'exclude' => true,
    'inputType' => 'select',
    'options' => array('date', 'site', 'event', 'round', 'white', 'black', 'whiteblack', 'result', 'whiteelo', 'blackelo', 'eco', 'source', 'annotator'),
    'reference' => &$GLOBALS['TL_LANG']['tl_module']['dbChess_index_fields_option'],
    'eval' => array(
        'tl_class' => '',
        'multiple' => FALSE),
    'exclude' => true,
    'sql' => "varchar(255) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['dbChess_index_sort'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['dbChess_index_sort'],
    'default' => 'f',
    'exclude' => true,
    'inputType' => 'radio',
    'options' => array('f', 'c'),
    'reference' => &$GLOBALS['TL_LANG']['tl_module']['dbChess_index_sort_option'],
    'eval' => array(
        'tl_class' => 'w50'),
    'exclude' => true,
    'sql' => "varchar(1) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['dbChess_index_byorder'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['dbChess_index_byorder'],
    'default' => 'a',
    'exclude' => true,
    'inputType' => 'radio',
    'options' => array('a', 'd'),
    'reference' => &$GLOBALS['TL_LANG']['tl_module']['dbChess_index_byorder_option'],
    'eval' => array(
        'tl_class' => 'w50'),
    'exclude' => true,
    'sql' => "varchar(1) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['dbChess_index_detailFields'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['dbChess_index_detailFields'],
    'exclude' => true,
    'inputType' => 'checkboxWizard',
    'options' => array('date', 'site', 'event', 'round', 'white', 'whiteelo', 'result', 'black', 'blackelo', 'eco', 'source', 'annotator'),
    'reference' => &$GLOBALS['TL_LANG']['tl_module']['dbChess_index_fields_option'],
    'eval' => array(
        'mandatory' => True,
        'tl_class' => '',
        'multiple' => TRUE),
    'exclude' => true,
    'sql' => "blob NULL",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['dbChess_index_exception'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['dbChess_index_exception'],
    'exclude' => true,
    'inputType' => 'listWizard',
    'eval' => array(
        'tl_class' => '',
        'multiple' => TRUE),
    'exclude' => true,
    'sql' => "blob NULL",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['dbChess_index_jumpTo'] = array
    (
    'label' => &$GLOBALS['TL_LANG']['tl_module']['dbChess_index_jumpTo'],
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

$GLOBALS['TL_DCA']['tl_module']['fields']['dbChess_index_template'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['dbChess_index_template'],
    'default' => 'mod_dbChess_index',
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => array('tl_module_dbChess_index', 'get_dbChess_index_Templates'),
    'eval' => array('tl_class' => 'w50'),
    'sql' => "varchar(32) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['dbChess_index_tag_buckets'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['dbChess_index_tag_buckets'],
	'default'                 => '4',
	'inputType'               => 'text',
	'eval'                    => array('maxlength'=>2, 'rgxp' => 'digit', 'tl_class'=>'w50'),
	'sql'                     => "smallint(5) unsigned NOT NULL default '4'"
);

class tl_module_dbChess_index extends Backend
{
    /**
     * Return all mod_dbChess_index templates as array
     * @return array
     */
    public function get_dbChess_index_Templates()
    {
        return $this->getTemplateGroup('mod_dbChess_index');
    }
}
