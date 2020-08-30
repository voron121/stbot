<?php
// Защита от запуска темплета вне контекста админ панели
if (TEMPLATE_CHECK != 1) {
    die('');
}
?>
<link rel="stylesheet" href="/cabinet/views/css/main_page.css">
<style>
    body {
       background: #179cde;
    } 
</style>
<div class="text-center col-md-12">
    <div class="auth_form">
        <form class="register-form" method="post" action="/cabinet/helpers/registration.php?action=registration">
            <h2>Регистрация:</h2>
            <div class="auth_message"></div>
            <div>
                <div class="text-left">
                    <p>Введите ваш email:</p>
                </div>
                <div class="input-group">
                    <span class="input-group-addon" id="basic-addon1">
                        <i class="glyphicon glyphicon-envelope"></i> 
                    </span>
                    <input name="u_login" type="text" class="form-control" placeholder="Введите ваш email" aria-describedby="basic-addon1">
                </div>
            </div>
            <div>
                <div class="text-left">
                    <p>Придумайте пароль:</p>
                </div>
                <div class="input-group">
                    <span class="input-group-addon" id="basic-addon2">
                        <i class="glyphicon glyphicon-eye-open"></i>
                    </span>
                    <input name="u_password" type="password" class="form-control" placeholder="Пароль" aria-describedby="basic-addon2">
                </div>
            </div>
            <div>
                <div class="text-left">
                    <p>Повторите пароль:</p>
                </div>
                <div class="input-group">
                    <span class="input-group-addon" id="basic-addon2">
                        <i class="glyphicon glyphicon-eye-open"></i>
                    </span>
                    <input name="u_password_retry" type="password" class="form-control" placeholder="Повторите пароль" aria-describedby="basic-addon2">
                </div>
            </div>
            <input type="submit" class="btn btn-success" value="Регистрация">
        </form>		
    </div>
</div>

<script>
    $(".alert").appendTo($(".auth_message"))
</script>
