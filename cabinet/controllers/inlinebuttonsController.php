<?php 

class InlineButtonsController {
 /*
  * 
  */
    public function renderButton($button) {
        $btn    = '';
        $i      = 0;
        foreach ($button as $row) {
            $btn .= '<div class="buttons_row" data-row="'.$i.'">';
            foreach ($row as $item) { 
                $btn .= '<div class="inline_button_wrap">';
                $btn .= '<div class="inline_button" button-text="'.$item["text"].'"';
                if (isset($item["url"])) {
                    $btn .= ' button-url="'.$item["url"].'">';
                } else {
                    $btn .= ' button-callback="'.$item["callback_data"].'">';
                }
                $btn .= '<div class="remove_button">x</div>';
                $btn .= '<div class="text">'.$item["text"].'</div>';
                $btn .= '</div>';
                $btn .= '</div>';
            }
            // Кнопки "добавить" и "удалить"
            $btn .= '<div class="inline_button_add add_button" data-toggle="modal" data-target="#inlineModal" data-row="'.$i.'">';
            $btn .= '<div class="action_btn"><i class="glyphicon glyphicon-plus"></i></div>';
            $btn .= '</div>';
            
            $btn .= '<div class="inline_button_add delete_row" data-row="'.$i.'">';
            $btn .= '<div class="action_btn"><i class="glyphicon glyphicon-trash"></i></div></div>';
            $btn .= '</div>';
            $i++;
        } 
        return $btn;
    }
}

?>