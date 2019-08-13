<?php

class ChangeBinaryTree extends BinaryTree
{
    private $changeParam;

    /**
     * ChangeBinaryTree constructor.
     * @param $pdo
     * @param $level
     * @param array $changeParam
     */
    public function __construct($pdo, $level, $changeParam = [])
    {
        $this->pdo = $pdo;
        $this->level = $level;
        $this->changeParam = $changeParam;
    }

    /**
     * Проверяет является ли возможным перенести на конкретную ячейку другие ячейки,
     * то есть есть ли у ячейки потомки.
     * @param $path
     */
    public function checkBinary($path)
    {
        $sql = "SELECT * FROM " . self::TABLE . " WHERE `path` LIKE '" . $path . ".%' ";
        $result = $this->pdo->query($sql);
        $count = $result->fetchColumn();

        if ($count >= 2) { // если потомков больше двух - запрещено перенос на эту ячейку
            print 'not allowed';
        } elseif ($count == 1) { // если один потомок, то возвращаем свободную позицию
            $position = 0;
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $position = $row['position'];
            }
            $position = $position == 1 ? 2 : 1;
            print $position;
        } else { // иначе перенос другой ячейки будет осуществлятся на первую позицию
            print '1';
        }
    }

    /**
     * Формирует массив изменённого бинарного дерева и записывает его в БД
     */
    public function changeBinaryTree()
    {
        $this->binaries[0][0] = [
            'id' => 1,
            'parent_id' => 0,
            'position' => 1,
            'path' => '1',
            'level' => 1
        ];
        $counter = 2;
        for ($i = 1; $i < $this->level; $i++) {
            for ($j = 0; $j < pow(self::BASE, $i); $j++) {
                $position = $j % 2 ? 2 : 1;
                if ($counter == $this->changeParam['fromID'] && $position == $this->changeParam['fromPosition']) { // если пустая ячейка
                    continue; // пропускает запись данных в массив, если исполняются условия для  изменённых параметров
                }

                $parent = $this->binaries[$i - 1][intdiv($j, 2)];
                if (empty($parent['id'])) { // если пустая ячейка родителя, то
                    $j++; // пропускаем две итерации, так как две ячейки наследника так же будут пустыми
                    continue; // пропускает запись данных в массив, если исполняются условия для  изменённых параметров
                } elseif ($i != $this->changeParam['toLevel']) { // заполняет массив, если уровень не равен уровню следующему за предыдущим
                    $this->binaries[$i][$j] = [ // заполняет массив данными
                        'id' => $counter,
                        'parent_id' => $parent['id'],
                        'position' => $j % 2 ? 2 : 1,
                        'path' => $parent['path'] . '.' . $counter,
                        'level' => $i + 1
                    ];
                    $counter++;
                }

                if (($parent['id'] == $this->changeParam['toID'] - $this->getCountChildren($this->changeParam['fromPath'], $this->changeParam['toLevel'], $this->changeParam['toLeft']))
                    && $position == $this->changeParam['toPosition']) { // заполняет массив, если совпадает новый родительский ID  ячеейки,
                    $this->binaries[$i][$j] = [                         // к которой присоединяется другие ячейки, с вычисляемыми параметрами
                        'id' => $counter,
                        'parent_id' => $parent['id'],
                        'position' => $j % 2 ? 2 : 1,
                        'path' => $parent['path'] . '.' . $counter,
                        'level' => $i + 1
                    ];
                    $counter++;
                }
            }
        }

        $this->truncateTable(); // очищает старое бинарное дерево
        $this->saveBinaryTree(); // записывает в БД новое изменное дерево
    }

    /**
     * Определяет количество потомков ячейки, которая будет переносится
     * в зависимости от того в каком направление будет осуществлятся перенос.
     * Это значение нужно для корректировки конечного положение ячейки.
     * @param $path
     * @param $level
     * @param $toLeft - направление переноса
     * @return int
     */
    public function getCountChildren($path, $level, $toLeft)
    {
        $sql = "SELECT COUNT(*) FROM " . self::TABLE . " WHERE `path` LIKE '" . $path . ".%' ";
        if ($toLeft == 'left') {
            if (substr($path, 0, 3) == '1.3') { // если переносится правая ветвь дерева, то
                $pathArray = explode('.', $path);
                if ($level == count($pathArray)) { // если переносится ячейка последнего уровня, то возвращаем 0
                    return 0;
                }
                // иначе подсчитываем количество потомков, но без потомков самого последнего уровня
                // это нужно для того, чтобы избежать избыточного смещения влево переносимых ячеек
                $sql = "SELECT COUNT(*) FROM " . self::TABLE . " WHERE `path` LIKE '" . $path . ".%' AND `level` != '" . $level . "'";
            } elseif (substr($path, 0, 3) == '1.2') { // та же ситуация только для левой ветви бинарного дерева
                $pathArray = explode('.', $path);
                if ($level == count($pathArray)) {
                    return 0;
                }

                $sql = "SELECT COUNT(*) FROM " . self::TABLE . " WHERE `path` LIKE '" . $path . ".%' AND `level` != '" . $level . "'";
            }
        }
        $result = $this->pdo->query($sql);
        return $result->fetchColumn() + 1;
    }
}