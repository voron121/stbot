<?php
require_once __DIR__ . '/../../controllers/inlinebuttonsController.php';
$buttons_controller = new InlineButtonsController();
$buttons    = [];
$btn        = "";
// TODO: перенести обработку кнопок в контроллер. Передавать в параметре json кнопок
if (isset($post)) {
    $buttons    = json_decode($post->buttons, true);
    $btn        = $post->buttons;
}
?>

<!-- ----------------------------------------------------------------------- -->

<div class="col-md-12 buttons_list_wraper">
    <h4>Кнопки:</h4>
    <input type="hidden" name="buttons" value='<?=$btn;?>'/>
    <div class="buttons_list">
        <?php if (!empty($buttons)):?>
            <?php foreach($buttons as $button):?>
                <?=$buttons_controller->renderButton($button);?>
            <?php endforeach;?>
        <?php endif;?>
    </div>
    <div class="clearfix"></div> 
    <hr>
    <div class="btn btn-default pull-right" id="add_inline_button">Добавить ряд кнопок</div>
    <div class="clearfix"></div> 
</div>

<!-- ----------------------------------------------------------------------- -->

<div class="modal fade" id="inlineModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">Добавить кнопку</h4>
            </div>
            <div class="modal-body">
                <div class="message"></div>
                <div>
                    <div class="input-group col-md-12">
                        <select class="form-control" name="buttons_mode">
                            <option>Выберите тип кнопки:</option>
                            <option value="link">Кнопка с ссылкой</option>
                            <option value="counter">Кнопка с счетчиком</option>
                        </select>
                    </div>
                    <hr> 
                    <div class="input-group col-md-12 buttons_list_editor"></div>
                    <hr>
                    <div class="pull-right btn btn-success" id="create_inline_button">Добавить</div>
                    <div class="clearfix"></div> 
                </div>
            </div>
            <div class="clearfix"></div> 
        </div>
    </div>
</div>

<!-- ----------------------------------------------------------------------- -->

<div class="hidden">
    
    <div class="counter_button"> 
        <p>Текст кнопки:</p>
        <input type="text" class="form-control validation_input" placeholder="Текст кнопки:" name="button_text" id="TelegramButtonText"/>
        <input type="hidden" name="button_callback" value=""/>
    </div>
    
    <div class="link_button">
        <p>Текст кнопки:</p>
        <input type="text" class="form-control validation_input" placeholder="Текст кнопки:" name="button_text" id="TelegramButtonText"/>
        <p>Ссылка:</p>
        <input type="text" class="form-control validation_input" placeholder="Ссылка:" name="button_url"/>
    </div>
    
    <div class="buttons_row" data-row="">
        <div class="inline_button_add add_button" data-toggle="modal" data-target="#inlineModal"  data-row="">
            <div class="action_btn"><i class="glyphicon glyphicon-plus"></i></div>
        </div>
        <div class="inline_button_add delete_row" data-row="">
            <div class="action_btn"><i class="glyphicon glyphicon-trash"></i></div>
        </div>
    </div>
    
    <div class="edit_window">
        <div>
            <h4 class="modal-title" id="myModalLabel">Изменить кнопку</h4>
            <hr>
            <div class="input-group col-md-12 buttons_list_editor">
                <label for="button_text">Текст кнопки:</label>
                <input type="text" class="form-control validation_input" placeholder="Текст кнопки:" name="button_text"/>
                <div class="clearfix"></div> 
                <label for="button_url">Ссылка:</label>
                <input type="text" class="form-control validation_input" placeholder="Ссылка:" name="button_url"/>
            </div>
            <hr>
            <div class="pull-right btn btn-default" id="edit_inline_button">Сохранить</div> 
            <div class="pull-right btn btn-default" id="close_edit_inline_button">Отменить</div>
            <div class="clearfix"></div> 
        </div>
    </div>
    
    <div class="inline_button_wrap">
        <div class="inline_button">
            <div class="remove_button">x</div>
            <div class="text"></div>
        </div>
    </div>
</div>

<script src="views/inlinebuttons/js/inlinebuttons.js"></script> 