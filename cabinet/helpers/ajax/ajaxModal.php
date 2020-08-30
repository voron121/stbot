<!-- Modal -->
<div class="modal fade" id="ajaxModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Внимание</h4>
      </div>
      <div class="modal-body">
         <div class="ajmsg"></div>
      </div>
      <div class="modal-footer">
          <div class="show_action">
                <button type="button" class="btn btn-warning ajmodal" data-dismiss="modal" >Отменить</button>
                <button type="button" class="btn btn-success ajmodal delete_item_accept">Подтвердить</button>
          </div>
          <div class="close_action">
              <button type="button" class="btn btn-success ajmodal" onclick="location.reload()" data-dismiss="modal" >Закрыть</button>
          </div>
      </div>
    </div>
  </div>
</div>

<style>
    .ajmsg {
        font-size: 16px;
    }
    .close_action {
        display: none
    }
</style>