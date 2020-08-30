<?php
include(__DIR__ . '/../config.php');
include(__DIR__."/../core/coreTools.php");
include(__DIR__."/../core/libs/logger.php");
//-------------------------------------------------//
/*
 * минута   - 60
 * 15 минут - 900
 * час      - 3600
 * 12 часов - 43200
 * сутки    - 86400
 */

$robots = [
    "schedullers" => [
        "pollScheduler.php" => [
            "dir"       => __DIR__."/scheduler/", 
            "interval"  => 60
        ],
        "postScheduler.php" => [
            "dir"       => __DIR__."/scheduler/", 
            "interval"  => 60
        ],
    ],
    "cachers" => [
        "telegramGroupMetaCacher.php" => [
            "dir"       => __DIR__."/telegramParser/", 
            "interval"  => 3600
        ],
        "vkGroupMetaCacher.php" => [
            "dir"       => __DIR__."/vkParser/", 
            "interval"  => 3600
        ],
        "rssCacher.php" => [
            "dir"       => __DIR__."/rssPublisher/", 
            "interval"  => 3600
        ],
    ],
    "parsers" => [
        "rssParser.php" => [
            "dir"       => __DIR__."/rssPublisher/", 
            "interval"  => 3600
        ],
        "vkParser.php"  => [
            "dir"       => __DIR__."/vkParser/", 
            "interval"  => 3600
        ],
    ],
    "watchdogs" => [
        "schedulerWatchdog.php" => [
            "dir"       => __DIR__."/scheduler/", 
            "interval"  => 43200
        ],
    ],
    "debitBalance" => [
        "debitBalance.php" => [
            "dir"       => __DIR__."/debitBalance/", 
            "interval"  => 86400
        ],
    ]
];

//-------------------------------------------------//

$core_tools = new coreTools();
$core_tools::printColorMessage('Start robot starter');
Logger::getInstance()->robotActionWriteToLog('robotStarter', 'starterStart', 'Стартер роботов запущен');

$db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD);
$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
$db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
//-------------------------------------------------//

$query = "SELECT login FROM users WHERE active = 'Yes' AND is_paid = 'Yes' ";
$stmt = $db->query($query);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_OBJ);
//-------------------------------------------------//

$commands = []; // массив очередей на запуск
//-------------------------------------------------//

foreach ($robots as $robot_type => $robots) {
    foreach ($robots as $robot => $robot_options) {
        foreach ($users as $user) {
            $command    = $robot." login=".$user->login;
            $robot_lock = md5($command).".txt";
            $log_file   = $core_tools::createRobotLogFile($robot, $user->login); 
            if (true == $core_tools->mustStartRobot($robot_lock, $robot_options['interval'])) {
                $commands[] = "php ".$robot_options['dir'].$command ." > ".$log_file." 2>/dev/null &";
                //$commands[] = "php ".$robot_options['dir'].$command ;
            }
        }
    }
}

//-------------------------------------------------//

foreach ($commands as $command) {
    //echo $command;
    exec($command); 
}

$core_tools::printColorMessage('Finish robot starter');
Logger::getInstance()->robotActionWriteToLog('robotStarter', 'starterSuccess', 'Стартер роботов завершил работу');
?>
