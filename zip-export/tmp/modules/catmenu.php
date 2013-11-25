<?php
$catmenu = file_get_contents("tpl/catmenu.inc");
$categories = $db_mapper->getAllCategories();

$cat_list_part = getTemplatePart("CategoryList", $catmenu);
$cat_list_part = replace("CategoryName", _ALL_CATEGORIES, $cat_list_part);
$cat_list_part = replace("CategoryId", 0, $cat_list_part);
$cat_list_part = replace("NrBets", count($db_mapper->getActiveBets()), $cat_list_part);

foreach ($categories as $category){
	if($category->getNrBets() >= 1){
		$cat_list_part .= getTemplatePart("CategoryList", $catmenu);
		$cat_list_part = replace("NrBets", $category->getNrBets(), $cat_list_part);
		$cat_list_part = replace("CategoryName", $category->getCategoryName(), $cat_list_part);
		$cat_list_part = replace("CategoryId", $category->getCategoryId(), $cat_list_part);
	}
}

$catmenu = replace("CategoryList", $cat_list_part, $catmenu);
$catmenu = replace("Categories", _CATEGORIES.":", $catmenu);
?>
