<?php
/*
 * Робот списывает средства со счета пользователя
 */
//--------------------------------------------------------//

include(__DIR__."/tools.php");
include(__DIR__ . "/../../core/coreTools.php");
include(__DIR__."/../../core/libs/logger.php");
//--------------------------------------------------------//

$tools  = new DebitBalanceTools();
$tarifs = $tools->getTarifsList();
$users  = $tools->getActiveUsers();
//--------------------------------------------------------//

if (true == coreTools::checkRobotLock($_SERVER['PHP_SELF'], "")) {
    echo "Blocked";
    Logger::getInstance()->robotActionWriteToLog('debitBalance', 'robotLock', 'Фаил заблокирован другой копией робота');
    exit;
}
//----------------------------------------------------------------------------//

coreTools::printColorMessage('Start debit balance');
Logger::getInstance()->robotActionWriteToLog('debitBalance', 'sessionStart', 'Робот обновления баланса приступил к работе');

foreach ($users as $user) {
    try {
        coreTools::printColorMessage('Processing user  '.$user->login); 
        $cost       = $tarifs[$user->subscription_id]["cost"];
        $tarif_name = $tarifs[$user->subscription_id]["name"];
        $tools->defundsUserBalance($cost, $user->id);
        if ($user->balance - $cost <= 0) {
            $tools->deactivateUser($user->id);
        }
        
        coreTools::printColorMessage('Update user balance. Set user '.$user->login.' new balance: '.($user->balance - $cost) , 'success');
        Logger::getInstance()->robotActionWriteToLog('debitBalance', 'updateBalance', 
            'Обновили баланс. Списали '.$cost.' USD согласно тарифному плану "'.$tarif_name.'". Новый баланс: '.($user->balance - $cost).' USD', 
            $user->login
        );
        // TODO: отправлять уведомления пользователям
        echo "---------------- \r\n";
    } catch(Exception $e) {
        Logger::getInstance()->robotActionWriteToLog('debitBalance', 'sessionError', $e->getMessage(), $user->login);
        coreTools::printColorMessage($e->getMessage(), 'error');
        continue;
    }
}
//----------------------------------------------------------------------------//

Logger::getInstance()->robotActionWriteToLog('debitBalance', 'sessionSuccess', 'Робот обновления баланса завершил работу');
coreTools::printColorMessage('Finish debit balance');
?>