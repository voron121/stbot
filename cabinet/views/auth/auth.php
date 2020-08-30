<?php
	// Защита от запуска темплета вне контекста админ панели
	if (TEMPLATE_CHECK != 1) { die('');}
?>
<link rel="stylesheet" href="/cabinet/views/css/main_page.css">
<style>
    body {
       background: #179cde;
    } 
</style>
<div class="text-center col-md-12">
    <div class="auth_form">
        <form class="register-form" method="post" action="/cabinet/helpers/auth.php?action=login">
            <div class="input-group">
                <h2>Кабинет</h2>
                <div class="auth_message"></div>
                <div>Ваш логин:</div>
                <div class="input-group">
                  <span class="input-group-addon" id="basic-addon1">
                        <i class="glyphicon glyphicon-user"></i> 
                  </span>
                  <input name="u_login" type="text" class="form-control" placeholder="Логин" aria-describedby="basic-addon1">
                </div>
                <div>Ваш пароль:</div>
                <div class="input-group">
                  <span class="input-group-addon" id="basic-addon2">
                    <i class="glyphicon glyphicon-eye-open"></i>   
                  </span>
                  <input name="u_password" type="password" class="form-control" placeholder="Пароль" aria-describedby="basic-addon2">
                </div>
                <br>
                <div class="col-sm-1"></div>
                <div class="col-sm-3">
                    <input type="submit" class="btn btn-success" value="Войти">
                </div>
                <div class="col-sm-1"></div>
                <div class="col-sm-3">
                    <a class="btn btn-default" href="index.php?template=registration">регистрация</a>
                </div>
                <div class="col-sm-2"></div>
            </div>
        </form>
    </div>
</div>

<script>
    $(".alert").appendTo($(".auth_message"))
</script>