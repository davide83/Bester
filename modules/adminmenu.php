<?php
$adminmenu = "";
if (($session->getState()) && 
	(($user->getStatus() == "betmaster") || 
	 ($user->getStatus() == "administrator"))){
	$adminmenu = file_get_contents("tpl/adminmenu.inc");
	$adminmenu = replace("BetList", "<a href=\"betlist.php\">"._BET_LIST."</a>", $adminmenu);
	$adminmenu = replace("CategoryList", "<a href=\"categorylist.php\">"._CATEGORY_LIST."</a>", $adminmenu);
	$adminmenu = replace("UserList", "<a href=\"userlist.php\">"._USER_LIST."</a>", $adminmenu);
	$adminmenu = replace("TransactionList", "<a href=\"transactionlist.php\">"._TRANSACTION_LIST."</a>", $adminmenu);
	$adminmenu = replace("Logs", "<a href=\"logs.php\">Logs</a>", $adminmenu);
}
?>
