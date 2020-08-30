<?php
// Вспомогательный класс для построения пагинации

class Paginator {
    private static $instance = null;
    
    private function __construct() {}
    private function __clone() {}
    private function __wakeup() {}
    
    /**
     * Инициализирует инстанс
     * 
     * @return \Paginator\Paginator
     */
    public static function getInstance(): Paginator
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }
    
    /**
     * Создаст HTML сущность навигационного айтема
     * 
     * @param int $page         - Номер страница
     * @param int $cur_page     - Текущая страница
     */
    private static function getPaginationItem($page, $cur_page){
        $active_item_class  = ($cur_page == $page) ? "active" : "" ;
        $url                = preg_replace('/(&page=\w+)/', '', $_SERVER['REQUEST_URI']);
        echo '<li class="page-item '.$active_item_class.'"><a class="page-link" href="'.$url.'&page='.$page.'">'.$page.'</a></li>';
    }
    
    /**
     * Создаст кнопку "Назад"
     * 
     * @param int $page     - Порядковый номер страницы
     */
    private static function getPrevPageItem($page) {
        $url = preg_replace('/(&page=\w+)/', '', $_SERVER['REQUEST_URI']);
        echo '<li class="page-item"><a class="page-link" href="'.$url.'&page='.self::getPrevPage($page).'"> Назад </a></li>';
    }
    
    /**
     * Посчитает номер предыдущей страницы
     * 
     * @param   int $page       - Текущая страница
     * @return  int $prev_page  - Предыдущая страница
     */
    private static function getPrevPage($page) {
        if ($page == 1 || $page == 0) {
            $prev_page = 1;
        } else {
            $prev_page = $page - 1;
        }
        return $prev_page;
    }
    
    /**
     * Посчитает номер следующей страницы
     * 
     * @param   int $page       - Текущая страница
     * @return  int $next_page  - Следующая страница
     */
    private static function getNextPage($page) {
        if ($page == 1) {
            $next_page = 2;
        } else {
            $next_page = $page + 1;
        }
        return $next_page;
    }
    
    /**
     * Сгенерирует HTML сущность для кнопки "Вперед"
     * 
     * @param int $page     - Текущая страница
     */
    private static function getNextPageItem($page) {
        $url = preg_replace('/(&page=\w+)/', '', $_SERVER['REQUEST_URI']);
        echo '<li class="page-item"><a class="page-link" href="'.$url.'&page='.self::getNextPage($page).'"> Вперед </a></li>';
    }
    
    /**
     * Сгенерирует HTML сущность для кнопки "Вначало"
     */
    private static function getFirstPageItem() {
        $url = preg_replace('/(&page=\w+)/', '', $_SERVER['REQUEST_URI']);
        echo '<li class="page-item"><a class="page-link" href="'.$url.'&page=1"> Вначало </a></li>';
    }
    
    /**
     * Сгенерирует HTML сущность для кнопки "Вконец"
     * 
     * @param int $page     - Текущая страница
     */
    private static function getLastPageItem($page) {
        $url = preg_replace('/(&page=\w+)/', '', $_SERVER['REQUEST_URI']);
        echo '<li class="page-item"><a class="page-link" href="'.$url.'&page='.$page.'"> Вконец </a></li>';
    }
    
    /**
     * Отрисует навигацию с кнопками
     * 
     * @param int $nav_items_offset - Смещение страницы
     * @param int $counter          - Общее количество навигационных айтемов для вывода
     * @param int $page             - Текущая страница  
     * @param int $page_count       - Общее количество страниц
     */
    private static function renderPagination($nav_items_offset, $counter, $page, $page_count) {
        echo '<div class="clearfix"></div>
            <div style="margin: 20px 0px 0px 0px;">
            <div class="text-center">Страница '.$page.' из '.$page_count.' </div>
            <div class="text-center" style="margin: -10px 0px 0px 0px;"><nav aria-label="Page navigation"><ul class="pagination">';
        self::getFirstPageItem();
        if (($page - 1) <= $page_count) {
            self::getPrevPageItem($page);
        }
        for ($i = $nav_items_offset; $i <= $counter; $i++) {
            self::getPaginationItem($i+1, $page);
        }
        if (($page + 1) <= $page_count) {
            self::getNextPageItem($page);
        }
        self::getLastPageItem($page_count);
        echo '</ul></nav></div></div>';
    }
   
    /**
     * инициализирует вывод пагинации
     * 
     * @param int $count    - Количество записей
     * @param int $page     - Текущаяя страница
     */
    public static function getPagination($count, $page = null) {
        $page               = (null != $page) ? $page : 1; 
        $nav_items_offset   = 0;
        $page_count         = ceil($count / ITEMS_ON_PAGE_LIMIT);
        $nav_pages_count    = ceil($page_count / ITEMS_ON_PAGE_LIMIT);
        
        if ($page_count == 0 || $count <= ITEMS_ON_PAGE_LIMIT) {
            return true;
        }
        
        if ($page_count > ITEMS_ON_PAGE_LIMIT) {
            if (ITEMS_ON_PAGE_LIMIT < $page) {
                // Расчитаем количество отображаемых страниц с учетом смещения
                $nav_items_shift    = ceil($page / ITEMS_ON_PAGE_LIMIT);
                $counter            = $nav_items_shift * ITEMS_ON_PAGE_LIMIT;
                $nav_items_offset   = $counter - ITEMS_ON_PAGE_LIMIT;
            } else {
                // Расчитаем количество отображаемых страниц с учетом смещения
                $nav_items_shift    = ceil($page / ITEMS_ON_PAGE_LIMIT);
                $counter            = $nav_items_shift * ITEMS_ON_PAGE_LIMIT;
                $nav_items_offset   = $counter - ITEMS_ON_PAGE_LIMIT;
            }
            // Скорректируем количество страниц если подошли к концу списка
            if ($counter >= $page_count) {
                $counter = $page_count - 1;
            }
        } else {
            $counter = $page_count - 1;
        }
        
        self::renderPagination($nav_items_offset, $counter, $page, $page_count);
    }
}
?>