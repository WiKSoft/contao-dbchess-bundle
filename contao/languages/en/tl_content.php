<?php

$GLOBALS['TL_LANG']['CTE']['schach'] = "Chess elements";
$GLOBALS['TL_LANG']['CTE']['dbChess_list'] = array('Chess database list','Lists chess games');
$GLOBALS['TL_LANG']['CTE']['dbChess_download'] = array('Chess database download','Generates a link to download a file.');

$GLOBALS['TL_LANG']['tl_content']['dbChess_list_element'] = 'List settings';
$GLOBALS['TL_LANG']['tl_content']['dbChess_list_collection'] =array('Game collections', 'Select the game collections to be listed.');
$GLOBALS['TL_LANG']['tl_content']['dbChess_list_fields'] =array('Game data', 'Select the game data to be displayed.');
$GLOBALS['TL_LANG']['tl_content']['dbChess_list_fields_option']['event'] = 'Event';
$GLOBALS['TL_LANG']['tl_content']['dbChess_list_fields_option']['site'] = 'Site';
$GLOBALS['TL_LANG']['tl_content']['dbChess_list_fields_option']['date'] = 'Date';
$GLOBALS['TL_LANG']['tl_content']['dbChess_list_fields_option']['round'] = 'Round';
$GLOBALS['TL_LANG']['tl_content']['dbChess_list_fields_option']['white'] = 'White';
$GLOBALS['TL_LANG']['tl_content']['dbChess_list_fields_option']['black'] = 'Black';
$GLOBALS['TL_LANG']['tl_content']['dbChess_list_fields_option']['result'] = 'Result';
$GLOBALS['TL_LANG']['tl_content']['dbChess_list_fields_option']['whiteelo'] = 'White Elo';
$GLOBALS['TL_LANG']['tl_content']['dbChess_list_fields_option']['blackelo'] = 'Black Elo';
$GLOBALS['TL_LANG']['tl_content']['dbChess_list_fields_option']['eco'] = 'ECO';
$GLOBALS['TL_LANG']['tl_content']['dbChess_list_fields_option']['source'] = 'Source';
$GLOBALS['TL_LANG']['tl_content']['dbChess_list_fields_option']['annotator'] = 'Annotator';
$GLOBALS['TL_LANG']['tl_content']['dbChess_list_fields_option']['pgn'] = 'Notation';
$GLOBALS['TL_LANG']['tl_content']['dbChess_list_filter'] = array('Filter list', 'Custom WHERE condition (SQL), e.g. "event=\'Internationales Schachmeisterturnier\' and site=\'Karlsbad\' and date LIKE \'%1907%\'" (enter without the double quotes!). Insert tags are supported, e.g. "date LIKE \'{{date::Y}}%\'" – please only use insert tags with fixed values, not ones that return visitor input or parts of the URL. Only administrators can edit this field.');
$GLOBALS['TL_LANG']['tl_content']['dbChess_list_sortfields'] =array('Sort list', 'Select how the list is sorted.');
$GLOBALS['TL_LANG']['tl_content']['dbChess_list_byorder'] =array('Sort order', 'Select the sort order of the list.');
$GLOBALS['TL_LANG']['tl_content']['dbChess_list_byorder_option']['a'] = 'ascending';
$GLOBALS['TL_LANG']['tl_content']['dbChess_list_byorder_option']['d'] = 'descending';

$GLOBALS['TL_LANG']['tl_content']['dbChess_list_template'] = array('Chess database list template','Here you can select the chess database list template.');
$GLOBALS['TL_LANG']['tl_content']['dbChess_list_jumpTo'] = array('Redirect page','Please choose the page to which visitors will be redirected when clicking a game.');
$GLOBALS['TL_LANG']['tl_content']['dbChess_dl_featured'] =array('Featured games only', 'For linked games, only the featured games are included in the download.');
$GLOBALS['TL_LANG']['tl_content']['dbChess_games_count'] = 'games';
