<?php
// файл конфигурации  - подключение к базе даных
require_once "config.php";
// файлы классов
require_once 'BinaryTree.php';
require_once 'ChangeBinaryTree.php';

// инициализация бинарного дерева - возвращвет данные о дереве из базы
if (isset($_GET['init-tree'])) {
    if (isset($_GET['level'])) {
        $tree = new BinaryTree($pdo, $_GET['level'], true);
        print_r($tree->getTree());
    }
}
// возвращает всех предков и потомков выбраной ячейки
if (isset($_GET['by-id'])) {
    if (isset($_GET['path']) && isset($_GET['level'])) {
        $tree = new BinaryTree($pdo, $_GET['level']);
        print_r($tree->getUpperAndLowerBinariesById($_GET['by-id'], $_GET['path']));
    }
}
// проверяет можно ли переместить на выбранную ячейку другие ячейки
if (isset($_GET['check-binary'])) {
    if (isset($_GET['path']) && $_GET['level']) {
        $tree = new ChangeBinaryTree($pdo, $_GET['level']);
        print_r($tree->checkBinary($_GET['path']));
    }
}
// возвращает количество потомков выбранной ячейки
if (isset($_GET['count-children'])) {
    if (isset($_GET['path']) && $_GET['level'] && $_GET['to-level'] && $_GET['to-left']) {
        $tree = new ChangeBinaryTree($pdo, $_GET['level']);
        print_r($tree->getCountChildren($_GET['path'], $_GET['to-level'], $_GET['to-left']));
    }
}
// возвращает из базы изменное бинарное дерево в соответствии с выбранными ячейками для переноса
if (isset($_POST['change_tree'])) {
    if (isset($_POST['level']) && isset($_POST['change_param'])) {
        $tree = new ChangeBinaryTree($pdo, $_POST['level'], $_POST['change_param']);
        $tree->changeBinaryTree();
        print_r($tree->getTree());
    }
}
