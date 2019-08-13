<?php

class BinaryTree
{
    const BASE = 2;
    const TABLE = 'binaries'; // таблица в базе даных

    public $pdo;
    public $level;
    protected $binaries = [];

    /**
     * BinaryTree constructor.
     * @param $pdo
     * @param $level
     * @param bool $init
     */
    public function __construct($pdo, $level, $init = false)
    {
        $this->pdo = $pdo;
        $this->level = $level;

        $this->checkLevelMismatch(); // определяет расхождение в уровнях сохраненного бинарного дерева и нового
        $this->checkInit($init); // проверяет нужно ли делать начальную инициализацию бинарного дерева
    }

    /**
     * Возвращает всех родителей и всех потомков ячейки по её ID
     * @param $id
     * @param $path
     * @return false|string
     */
    public function getUpperAndLowerBinariesById($id, $path)
    {
        if ($id == 1) { // если выбранная ячейка самая первая(корень дерева), то возвращает все записи кроме первой
            $sql = "SELECT * FROM " . self::TABLE . " WHERE `path` != 1 ORDER BY `path`";
        } else {
            $sql = "SELECT * FROM " . self::TABLE . " WHERE `path` LIKE '%." . $id . ".%' "; // все потомки

            while ($path != '1') { // все родители, кроме первого
                $path = substr($path, 0, strrpos($path, '.'));
                $sql .= "OR `path` = '" . $path . "' ";
            }
            $sql .= "OR `path` = '1' ORDER BY `path`"; // и также первый родитель(корень дерева)
        }
        return $this->convertToJSON($this->pdo->query($sql)); // конвертирует в JSON-строку результат для возврата AJAX-запросу
    }

    /**
     * Возвращает массив данных бинарного дерева
     * @return false|string
     */
    public function getTree()
    {
        $sql = "SELECT * FROM " . self::TABLE;
        return $this->convertToJSON($this->pdo->query($sql)); // конвертирует в JSON-строку результат для возврата AJAX-запросу
    }

    /**
     * Конвертирует в JSON-строку данные
     * @param $result
     * @return false|string
     */
    private function convertToJSON($result)
    {
        $resultArray = [];
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $resultArray[] = $row;
        }
        return json_encode($resultArray);
    }

    /**
     * Определяет расхождение в уровнях сохраненного бинарного дерева и нового.
     * Например, если пользователь выберет другой уровень бинарного дерева для инициализации
     */
    private function checkLevelMismatch()
    {
        $count = $this->getCountBinaries();
        // если количество записей в БД отлично от количества ячеек нового дерева, тогда очищаем таблицу в БД
        if ($count != 0 && $count != (pow(self::BASE, $this->level) - 1)) $this->truncateTable();
    }

    /**
     * Очищает таблицу в БД
     */
    protected function truncateTable()
    {
        $sql = "TRUNCATE TABLE " . self::TABLE . "";
        $this->pdo->query($sql);
    }

    /**
     * Делает проверку нужно ли инициализировать новое бинарное дерево
     * @param $init
     */
    private function checkInit($init)
    {
        if ($init) $this->initBinaryTree();
    }

    /**
     * Возвращает количество ячеек
     * @return mixed
     */
    private function getCountBinaries()
    {
        $sql = "SELECT COUNT(*) FROM " . self::TABLE;
        $count = $this->pdo->query($sql);
        return $count->fetchColumn();
    }

    /**
     * Инициализирует новое бинарное дерево
     */
    private function initBinaryTree()
    {
        $this->binaries[0][0] = [
            'id' => 1,
            'parent_id' => 0,
            'position' => 1,
            'path' => '1',
            'level' => 1
        ];
        $counter = 2;
        for ($i = 1; $i < $this->level; $i++) { // заполняет уровни дерева
            for ($j = 0; $j < pow(self::BASE, $i); $j++) { // заполняет ячейки дерева
                $this->binaries[$i][$j] = [
                    'id' => $counter,
                    'parent_id' => $this->getParentId($counter),
                    'position' => $this->getPosition($counter),
                    'path' => $this->calculatePath($counter),
                    'level' => $i + 1
                ];
                $counter++;
            }
        }

        $this->truncateTable(); // очищает таблицу
        $this->saveBinaryTree(); // сохраняет данные о дереве в БД
    }

    /**
     * Сохраняет данные о дереве в БД
     */
    protected function saveBinaryTree()
    {
        $sql = "INSERT INTO " . self::TABLE . " (`id`, `parent_id`, `position`, `path`, `level`) VALUES ";
        $values = [];
        for ($i = 0; $i < $this->level; $i++) {
            for ($j = 0; $j < pow(self::BASE, $i); $j++) {
                if (!empty($this->binaries[$i][$j])) {
                    $values[] = "('"
                        . $this->binaries[$i][$j]['id'] . "', '"
                        . $this->binaries[$i][$j]['parent_id'] . "', '"
                        . $this->binaries[$i][$j]['position'] . "', '"
                        . $this->binaries[$i][$j]['path'] . "', '"
                        . $this->binaries[$i][$j]['level']
                        . "')";
                }
            }
        }
        $sql .= implode(', ', $values) . ';';
        $this->pdo->query($sql);
    }

    /**
     * Последние три метода работают только на основании закономерностей правильного бинарного дерева и
     * реализовывают чёткий алгоритм определения следующих ячеек бинарного дерева, а также других их
     * параметров таких как путь, уровень, позиция, ID родителя.
     */

    /**
     * Возвращает ID родительской ячейки
     * @param $id
     * @return float|int
     */
    private function getParentId($id)
    {
        return $this->getPosition($id) % 2 ? $id / 2 : ($id - 1) / 2;
    }

    /**
     * Возвращает позицию ячейки относительно родителя
     * @param $id
     * @return int
     */
    private function getPosition($id)
    {
        return $id % 2 ? 2 : 1;
    }

    /**
     * Возвращает путь ячейки в дереве
     * @param $parent_id
     * @return string
     */
    private function calculatePath($parent_id)
    {
        $path = '';
        while ($parent_id >= 1) { // формирует путь из родительских ID с конца
            $path .= $parent_id . '.';
            $parent_id = $this->getParentId($parent_id);
        }
        $pathElements = explode('.', substr($path, 0, strlen($path) - 1));
        $pathElements = array_reverse($pathElements); // раворачивает путь, чтобы корневой родитель был вначале пути
        $path = implode('.', $pathElements);
        return $path;
    }
}