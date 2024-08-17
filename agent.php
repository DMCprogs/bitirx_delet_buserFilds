<?
const ROW_DELETE_LIMIT=1000;
public static function DeleteOld()
{
    if (!\CModule::includeModule('sale')) {
        return false;
    }

    $rowCount = 0;
    $fuserRes = \Bitrix\Sale\Internals\FuserTable::getList(array(
        'select' => array('ID'),
        'filter' => array(
            'USER_ID' => null,
        ),
        'limit' => self::ROW_DELETE_LIMIT
    ));

   
    while ($fuser = $fuserRes->fetch())
    {
       \CSaleBasket::DeleteAll($fuser["ID"], false);
       \CSaleUser::Delete($fuser["ID"]);
       $rowCount++;
    }
    return $rowCount;
}

public static function DeleteOldAgent($agentID)
{
   

    if ($arAgent = \CAgent::GetById($agentID)->Fetch()) {
        $currentInterval = $arAgent['AGENT_INTERVAL'];
    } elseif ($arAgent = \CAgent::GetList([], ['NAME' => '\Sok\Core::DeleteOldAgent%'])->Fetch()) {
        $currentInterval = $arAgent['AGENT_INTERVAL'];
        $agentID = $arAgent['ID'];
    } else {
        return false;
    }

    if (!isset($GLOBALS['USER']) || !is_object($GLOBALS['USER'])) {
        $bTmpUser = true;
        $GLOBALS['USER'] = new \CUser;
    }

    $nRowsDeleted = self::DeleteOld();

    if ($nRowsDeleted == self::ROW_DELETE_LIMIT) {
        $currentInterval = max(floor($currentInterval / 2), 60);
        \CAgent::Update($agentID, ['AGENT_INTERVAL' => $currentInterval]);
    } elseif ($nRowsDeleted * 2 <= self::ROW_DELETE_LIMIT) {
        $currentInterval =3600;
        \CAgent::Update($agentID, ['AGENT_INTERVAL' => $currentInterval]);
    }

    if ($bTmpUser) {
        unset($GLOBALS['USER']);
    }

    return "\Sok\Core::DeleteOldAgent($agentID);";
}
?>