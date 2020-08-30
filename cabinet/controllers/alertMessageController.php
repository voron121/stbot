<?php 
/**
 * Класс для реализации вывода сообщений по гет параметрам
*/

class alertMessageController {
    public $messages = [
        'verificationError' => [
                'heading' => 'Ошибка верификации!',
                'message' => 'Канал добавлен но не верифицирован в системе. Пожалуйста, добавьте нашего бота и пройдите верификицию'
        ],
        'verificationSuccess' => [
                'heading' => 'Успех!',
                'message' => 'Канал успешно добавлен и верифицирован.'
        ],
        'channelDeleteSuccess' => [
                'heading' => 'Удалили!',
                'message' => 'Канал успешно удален!.'
        ],
        'channelDublicateWarning' => [
                'heading' => 'Канал уже добавлен!',
                'message' => 'Не возможно добавить канал т.к он уже есть в сервисе.'
        ],
        'postAddError' => [
                'heading' => 'Публикация не добавлена!',
                'message' => 'Не удалось добавить публикацию!'
        ],
        'postAddSuccess' => [
                'heading' => 'Публикация успешно добавлена!',
                'message' => 'Публикация успешно сохранена!'
        ],
        'postDeleteSuccess' => [
                'heading' => 'Публикация успешно удалена!',
                'message' => 'Публикация успешно удалена!'
        ],
        'postPublishSuccess' => [
                'heading' => 'Публикация успешно опубликована!',
                'message' => 'Публикация успешно опубликована в вашем телеграм-канале!'
        ],
        'postPublishError' => [
                'heading' => 'Публикация не опубликована!',
                'message' => 'Не удалось опубликовать запись!'
        ],
        'postUpdateSuccess' => [
            'heading' => 'Публикация сохранена!',
            'message' => 'Публикация успешно изменена!'
        ],
        'pollError' => [
                'heading' => 'Ошибка создания опроса!',
                'message' => 'Не удалось сохранить опрос.Введены не валидные даннные!'
        ],
        'pollAddError' => [
                'heading' => 'Опрос не сохранен!',
                'message' => 'Не удалось сохранить опрос!'
        ],
        'pollAddSuccess' => [
                'heading' => 'Опрос сохранен!',
                'message' => 'Опрос успешно создан!'
        ],
        'pollDeleteSuccess' => [
                'heading' => 'Опрос успешно удален!',
                'message' => 'Опрос успешно удален!'
        ],
        'pollPublishSuccess' => [
                'heading' => 'Опрос успешно опубликован!',
                'message' => 'Опрос успешно опубликован!'
        ],
        'pollPublishError' => [
                'heading' => 'Ошибка публикации!',
                'message' => 'Произошла ошибка при попытке опубликовать опрос!'
        ],
        'pollStopSuccess' => [
                'heading' => 'Опрос закрыт!',
                'message' => 'Опрос успешно закрыт!'
        ],
        'pollStopError' => [
                'heading' => 'Ошибка закрытия опроса',
                'message' => 'Произошла ошибка при попытке закрыть опрос на канале!'
        ],
        'passwordsNotMatchingError' => [
                'heading' => 'Пароли не совпадают!',
                'message' => 'Убедитесь что вы правильно ввели пароль и проверочный пароль!'
        ],
        'loginIsBusyError' => [
                'heading' => 'Логин занят',
                'message' => 'Такой логин уже занят! Пожалуйста выберите другой логин.'
        ],
        'passwordLongError' => [
                'heading' => 'Пароль слишком длинный!',
                'message' => 'Пароль привышает допустимую длину!'
        ],
        'passwordShortError' => [
                'heading' => 'Пароль слишком короткий!',
                'message' => 'Длина пароля должна быть больше 6-ти символов!'
        ],
        'invalidLoginError' => [
                'heading' => 'Ошибка логина!',
                'message' => 'Убедитесь что вы правильно ввели email!'
        ],
        'registrationCompleteError' => [
                'heading' => 'Ошибка активации учетной записи!',
                'message' => 'Произошла ошибка активации учетной записи! Ключ активации не верен'
        ],
        'registrationUndefineError' => [
                'heading' => 'Ошибка регистрации!',
                'message' => 'Произошла ошибка'
        ],
        'registrationSuccess' => [
                'heading' => 'Регистрация завершена!',
                'message' => 'Пожалуйста, пройдите по ссылке в письме, для активации учетной записи. Письмо мы вам выслали на указанный вами email!'
        ],
        'registrationCompleteSuccess' => [
                'heading' => 'Активация учетной записи завершена!',
                'message' => 'Вы можете войти в сервис используя свой логин и пароль!'
        ],
        'UndefineError' => [
                'heading' => 'Ошибка!',
                'message' => 'Что-то пошло не так!'
        ],
        'userNotFoundError' => [
                'heading' => 'Ошибка авторизации!',
                'message' => 'Пользователь с таким логином не найден!'
        ],
        'userPasswordError' => [
                'heading' => 'Ошибка авторизации!',
                'message' => 'Введенный пароль не верный!'
        ],
        'userActivateError' => [
                'heading' => 'Ошибка авторизации!',
                'message' => 'Пользователь не активирован. Пожалуйста, перейдите по ссылке в письме для активации вашего аккаунта!'
        ],
        'addVKAccountSuccess' => [
                'heading' => 'Успех!',
                'message' => 'Аккаунт ВК успешно добавлен!!'
        ],
        'addVKAccountError' => [
                'heading' => 'Ошибка!',
                'message' => 'Не удалось добавить аккаунт ВК!'
        ],
        'addVKAccountBusyError' => [
                'heading' => 'Ошибка!',
                'message' => 'Этот аккаунт ВК подключен у другого пользователя!'
        ],
        'addVKGroupError' => [
                'heading' => 'Ошибка!',
                'message' => 'Не удалось добавить сообщество ВК!'
        ],
        'addVKGroupSaveError' => [
                'heading' => 'Ошибка!',
                'message' => 'Не удалось сохранить сообщество ВК!'
        ],
        'addVKGroupSuccess' => [
                'heading' => 'Успех!',
                'message' => 'Сообщество ВК успешно добавлено!'
        ],
        'deleteVKGroupSuccess' => [
                'heading' => 'Успех!',
                'message' => 'Сообщество ВК успешно удалено!'
        ],
        'addVKGroupImportRuleSuccess' => [
                'heading' => 'Успех!',
                'message' => 'Правило импорта успешно добавлено!'
        ],
        'addVKGroupImportRuleError' => [
                'heading' => 'Ошибка!',
                'message' => 'Не удалось сохранить правило импорта!'
        ],
        'deleteVKGroupImportRuleSuccess' => [
                'heading' => 'Успех!',
                'message' => 'Правило успешно удалено!'
        ],
        'changeStateImportRuleSuccess'  => [
            'heading' => 'Успех!',
            'message' => 'Состояние правила успешно изменено!'
        ],
        'editVKGroupImportRuleSuccess'  => [
            'heading' => 'Успех!',
            'message' => 'Изменения сохраненны!'
        ],
        'editVKGroupImportRuleError'  => [
            'heading' => 'Ошибка!',
            'message' => 'Не удалось сохранить изменения правила импорта!'
        ],
        'deleteRSSSuccess'  => [
            'heading' => 'Успех!',
            'message' => 'RSS фид успешно удален!'
        ],
        'addRSSError'  => [
            'heading' => 'Ошибка!',
            'message' => 'Не удалось сохранить RSS фид!'
        ],
        'addRSSIssetError'  => [
            'heading' => 'Ошибка!',
            'message' => 'Такой RSS фид уже добавлен!'
        ],
        'addRSSSuccess'  => [
            'heading' => 'Успех!',
            'message' => 'Успешно сохранили RSS фид!'
        ],
        'addRSSImportRuleSuccess'  => [
            'heading' => 'Успех!',
            'message' => 'Успешно сохранили правило импорта RSS!'
        ],
        'addRSSImportRuleError'  => [
            'heading' => 'Ошибка!',
            'message' => 'Ошибка сохранения правила испорта RSS!'
        ],
        'deleteRSSImportRuleSuccess'  => [
            'heading' => 'Успех!',
            'message' => 'Успешно удалили правило импорта RSS!'
        ],
        'addRSSIssetValidationError'  => [
            'heading' => 'Ошибка!',
            'message' => 'RSS фид не валиден! Вы можете проверить валидность фида на сайте <a href="http://feedvalidator.org">http://feedvalidator.org</a> '
        ],
        'postAddFile1Error'  => [
            'heading' => 'Ошибка!',
            'message' => 'Количество добавляемых файлов привышает допустимое ограничение!'
        ],
        'postAddFile2Error'  => [
            'heading' => 'Ошибка!',
            'message' => 'Обнаружен фаил с не поддерживаемым типом данных!'
        ],
        'postAddFile3Error'  => [
            'heading' => 'Ошибка!',
            'message' => 'Общий размер всех файлов привышает допустимое ограничение!'
        ],
        'postAddFile4Error'  => [
            'heading' => 'Ошибка!',
            'message' => 'Привышение дисковой квоты для вашего тарифного плана!'
        ],
    ];

    protected function getHumanizationErrorType($type) {
        $types = [
            "channel"           => "для каналов",
            "vkaccount"         => "для подключенных аккаунтов ВК",
            "vkgroups"          => "для пабликов ВК",
            "vkgroupsimport"    => "для правил импорта пабликов ВК",
            "rss"               => "для подключенных RSS лент",
            "rssimport"         => "для правил импорта RSS лент"
        ];
        if (isset($types[$type])) {
            return $types[$type];
        }
        return false;
    }
    
    protected function getLimitErrorMessage() {
        $message_txt  = "Вы привысили лимит ";
        $message_txt .= $this->getHumanizationErrorType($_GET["template"]);
        $message_txt .= " в вашем тарифном плане.<br>";
        $message_txt .= " Вы можете изменить ваш тарифный план на более высокий для увеличения лимитов.";
        $message = ['heading' => 'Превышение лимита для вашего тарифного плана!', 'message' => $message_txt];
        return $message;
    }
    
    public function getMessageCSSClass($message_code) {
        $css = 'alert-success';
        if (true == strpos($message_code, 'Error')) {
                $css = 'alert-danger';
        } elseif (true == strpos($message_code, 'Warning')) {
                $css = 'alert-warning';
        }
        return $css;
    }

    public function getMessage($message_code) {
        $message = ['heading' => 'Не известная ошибка', 'message' => 'Не известная ошибка'];
        if ("LimitError" == $message_code) {
            $message = $this->getLimitErrorMessage();
        } else {
            if (isset($this->messages[$message_code])) {
                $message = $this->messages[$message_code];
            }
        }
        return $message;
    }
}

?>