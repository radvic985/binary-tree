$(document).ready(function () {
    $('.init-tree').click(function () {
        let level = parseInt($('#level').val());
        $.get("binary.php?init-tree=yes&level=" + level, function (data, status) {
            if (status === 'success') {
                $('.table').html(fillTable(data, level)); // заполняет таблицу данныаи из БД
                drawBinaryLines(level); // рисует соединительные линии

                $('.change-binary-tree, .change-param').show(); // показывает кнопку для изменения бинарного дерева и поля для ввода ID ячейки для переноса
            }
        });


    });

    $('.change-binary-tree').click(function () { //
        $('.lines').hide();
        let newLevel = 0;
        let changeParams = {};
        let from = $('#from').val();
        let to = $('#to').val();

        if (from == '' || to == '') {
            alert('You have to fill the "From" and "To" fields');
            return;
        }

        if (from == 1) {
            alert('You can not move the 1st binary! Choose another one.');
            return;
        }

        let toPath = $('#binary' + to).parent().data('path');
        let splitPath = toPath.split('.');
        if (splitPath.indexOf(from) != -1) { // проверяет является ли ячейка для переноса одним из родителей ячейки, к которой будет перенос
            alert('You can not move the binary to his child! Choose another one.');
            return;
        }

        let level = parseInt($('#level').val());
        $.get("binary.php?check-binary=yes&level=" + level + "&path=" + $('#binary' + to).parent().data('path'), function (data, status) {
            if (status == 'success') {
                if (data == 'not allowed') { // проверяет возможность присоединения к ячейке других ячеек
                    alert('You can not move the binaries to the ' + to + ' binary! Choose another one.');
                    $('#to').addClass('to-danger');
                    return;
                } else {
                    newLevel = getNewLevel(level, from, to); // новый уровень изменённого бинарного дерева
                    changeParams = getChangeParams(from, to); // формирование параметров для изменения
                    changeParams.toPosition = data;
                }

                let params = {
                    change_tree: 'yes',
                    level: newLevel,
                    change_param: changeParams,
                }

                let parentId = 0;
                $.get("binary.php?count-children=yes&path=" + changeParams.fromPath + '&level=' + newLevel + '&to-level=' + changeParams.toLevel + '&to-left=' + checkIfChangeLeft(from, to), function (data, status) {
                    if (status == 'success') {
                        parentId = parseInt(changeParams.toID) - parseInt(data); // определяет изменённый родительский ID ячейки, к которой будет перенос
                        $.post("binary.php", params, function (data, status) {
                            if (status === 'success') {
                                fillChangedTable(data, newLevel, changeParams, parentId); // формирование изменного бинарного дерева с помощью таблицы
                                $('.change-binary-tree, .change-param').hide();
                                $('#from, #to').val('');
                            }
                        });
                    }
                });
            }
        });
    });

    $('.table').on('click', '.binary', function () {
        $('.binary').removeClass('binary-selected');
        $(this).addClass('binary-selected');

        let level = parseInt($('#level').val());
        // определяет все ячейки, которые являются родителями и потомками для конкретной ячейки
        $.get("binary.php?by-id=" + $(this).text() + "&level=" + level + "&path=" + $(this).parent().data('path'), function (data, status) {
            if (status === 'success') {
                let binaries = JSON.parse(data);
                let binariesArray = Object.keys(binaries).map((key) => binaries[key]);

                $('.binary').removeClass('upper-lower');
                $(binariesArray).each(function () { // перебирает всех родителей и потомков и добавляет клас для их отображения
                    $('#binary' + $(this)[0].id).addClass('upper-lower');
                });
            }
        });
    });

    $('#from').change(function () {
        $('.binary').removeClass('from-binary');
        $('#binary' + $(this).val()).addClass('from-binary');
    });

    $('#to').change(function () {
        $('.binary').removeClass('to-binary');
        $('#binary' + $(this).val()).addClass('to-binary');
        $(this).removeClass('to-danger');
    });

    $('#level').change(function () {
        if ($(this).val() > 5) {
            $('.lines').hide();
        }
    });
});

/**
 * Заполняет таблицу в виде бинарного дерева данными из базы даных
 * @param data - данные из БД
 * @param level - количество уровней дерева
 * @returns {string} - возвращает таблицу в виде строки
 */
function fillTable(data, level) {
    let binaries = JSON.parse(data);
    let binariesArray = Object.keys(binaries).map((key) => binaries[key]);
    let table = '<tbody>';
    let counter = 0;
    for (let i = 0; i < level; i++) { // уровни дерева как строки таблицы
        table += '<tr>';
        for (let j = 0; j < Math.pow(2, i); j++) { // элементы дерева как столбцы таблицы
            // формирует отображение правильного бинарного дерева с помощью атрибутов colspan
            // соответственно соотношению количества элементов на каждом уровне
            if (typeof binariesArray[counter] === 'undefined') {
                table += '<td colspan="' + Math.pow(2, (level - i - 1)) + '"></td>';
            } else if (binariesArray[counter]['id'] < 10) {
                table += '<td colspan="' + Math.pow(2, (level - i - 1)) + '" data-id="' + binariesArray[counter]["id"] + '" data-parent-id="' + binariesArray[counter]["parent_id"] + '" data-path="' + binariesArray[counter]["path"] + '" data-position="' + binariesArray[counter]["position"] + '" data-level="' + binariesArray[counter]["level"] + '"><span id="binary' + binariesArray[counter]["id"] + '" class="binary" style="padding:5px 10px">' + binariesArray[counter]['id'] + '</span></td>';
            } else {
                table += '<td colspan="' + Math.pow(2, (level - i - 1)) + '" data-id="' + binariesArray[counter]["id"] + '" data-parent-id="' + binariesArray[counter]["parent_id"] + '" data-path="' + binariesArray[counter]["path"] + '" data-position="' + binariesArray[counter]["position"] + '" data-level="' + binariesArray[counter]["level"] + '"><span id="binary' + binariesArray[counter]["id"] + '" class="binary">' + binariesArray[counter]['id'] + '</span></td>';
            }
            counter++;
        }
        table += '</tr>';
    }
    table += '</tbody>';
    return table;
}

/**
 * Заполняет таблицу в виде изменного бинарного дерева данными из базы даных
 * @param data - данные из БД
 * @param newLevel - увеличенный уровень изменного бинарного дерева
 * @param changeParams - параметры по изменнию дерева
 * @param parentId - переопределённый родительский ID ячейки, к которой присоединяется новая ячейка
 */
function fillChangedTable(data, newLevel, changeParams, parentId) {
    let binaries = JSON.parse(data);
    let binariesArray = Object.keys(binaries).map((key) => binaries[key]);

    $('.table tbody').html('');
    let tr = '';
    let counter = 1;
    let position = 0;
    for (let i = 0; i < newLevel; i++) {
        tr += '<tr>';
        for (let j = 0; j < Math.pow(2, i); j++) {
            position = j % 2 ? 2 : 1;
            if ((counter == changeParams.fromID && position == changeParams.fromPosition) ||
                ($('.table tr:last-child td:nth-child(' + (parseInt(j / 2) + 1) + ')').data('parent-id') == 'none') ||
                (i == changeParams.toLevel && $('.table tr:last-child td:nth-child(' + (parseInt(j / 2) + 1) + ')').data('id') != parentId)) {
                // пустая ячейка в таблице если такая же ячейка пустая в изменноном бинарном дереве(соответственно выполнению условий ввыше)
                tr += '<td colspan="' + Math.pow(2, (newLevel - i - 1)) + '" data-parent-id="' + 'none' + '"></td>';
                continue;
            } else if ($('.table tr:last-child td:nth-child(' + (parseInt(j / 2) + 1) + ')').data('id') == parentId && position == (changeParams.toPosition == 1 ? 2 : 1)) {
                // пустая ячейка в таблице если такая же ячейка пустая в изменноном бинарном дереве(соответственно выполнению условий ввыше)
                tr += '<td colspan="' + Math.pow(2, (newLevel - i - 1)) + '" data-parent-id="' + 'none' + '"></td>';
                continue;
            } else {
                if (counter < 10) {
                    // добавляет стиль для улучшенного отображения круга ячейки(без стиля ячейка овальная, а все другие круглые)
                    tr += '<td colspan="' + Math.pow(2, (newLevel - i - 1)) + '" data-path="' + binariesArray[counter - 1]["path"] + '" data-id="' + binariesArray[counter - 1]["id"] + '" data-parent-id="' + binariesArray[counter - 1]["parent_id"] + '" data-postion="' + binariesArray[counter - 1]["position"] + '" data-level="' + binariesArray[counter - 1]["level"] + '"><span id="binary' + binariesArray[counter - 1]["id"] + '" class="binary" style="padding:5px 10px">' + binariesArray[counter - 1]["id"] + '</span></td>';
                } else {
                    tr += '<td colspan="' + Math.pow(2, (newLevel - i - 1)) + '" data-path="' + binariesArray[counter - 1]["path"] + '" data-id="' + binariesArray[counter - 1]["id"] + '" data-parent-id="' + binariesArray[counter - 1]["parent_id"] + '" data-position="' + binariesArray[counter - 1]["position"] + '" data-level="' + binariesArray[counter - 1]["level"] + '"><span id="binary' + binariesArray[counter - 1]["id"] + '" class="binary">' + binariesArray[counter - 1]['id'] + '</span></td>';
                }
                counter++;
            }
        }
        tr += '</tr>';
        $('.table tbody').append(tr);
        tr = '';
    }
}

/**
 * Пересчитывает новый уровень изменного бинарного дерева
 * @param level - предыдущий уровень
 * @param from - ячейка, которую переносят
 * @param to - ячейка, к которой присоединяют другую ячейку(с её наследниками)
 * @returns {number} - возвращает новый уровень
 */
function getNewLevel(level, from, to) {
    let fromLevel = $('#binary' + from).parent().data('level');
    let toLevel = $('#binary' + to).parent().data('level');
    let moveCount = level - parseInt(fromLevel) + 1; // количество переносимых уровней
    let newLevel = moveCount + parseInt(toLevel); // новый уровень

    return newLevel > level ? newLevel : level; // проверяет больше ли новый уровень предыдущего и возвращает большее значение
}

/**
 * Возвращает объект с параметрами для изменения бинарного дерева
 * @param from - ячейка, которую переносят
 * @param to - ячейка, к которой присоединяют другую ячейку(с её наследниками)
 * @returns {{toID: *, toLeft: string, fromPath: jQuery, toLevel: jQuery, fromPosition: jQuery, toPosition: jQuery, fromID: *}}
 */
function getChangeParams(from, to) {
    return {
        'fromID': from,
        'fromPath': $('#binary' + from).parent().data('path'),
        'fromPosition': $('#binary' + from).parent().data('position'),
        'toID': to,
        'toPosition': $('#binary' + to).parent().data('position'),
        'toLevel': $('#binary' + to).parent().data('level'),
        'toLeft': checkIfChangeLeft(from, to), //свойство для определения в какую сторону будет перенесена ячейка
    }
}

/**
 * Функция для определения в какую сторону будет перенесена ячейка с её наследниками
 * @param from - ячейка, которую переносят
 * @param to - ячейка, к которой присоединяют другую ячейку(с её наследниками)
 * @returns {string} - возвращает сторону переноса ячейки
 */
function checkIfChangeLeft(from, to) {
    let levelFrom = $('#binary' + from).parent().data('level'); //уровень ячейки переноса
    let toPath = $('#binary' + to).parent().data('path'); // путь ячейки, к которой будет перенос
    let splitPath = toPath.split('.'); // массив предков ячейки, к которой будет перенос

    let toLeft = 'right';
    if (splitPath[levelFrom - 1] < from) toLeft = 'left'; // сравниваем уровни ячеек и определяем направление

    return toLeft;
}

/**
 * Рисует вспомогательные(соединительные) линии для бинарного дерева для лучшего отображения дерева
 * Работает только для уровней не выше 5, и только для правильного(неизменённого) бинарного дерева
 * @param level
 */
function drawBinaryLines(level) {
    if (level <= 5) { // Работает только для уровней не выше 5
        let parentId = 0;
        let binaryId = 0;
        $('.table tr:not(:first-child) td').each(function () { // выбирает все ячейки кроме первой
            parentId = $(this).data('parent-id');
            binaryId = $(this).text();
            let line = createLine($("#binary" + parentId), $("#binary" + binaryId)); // принимает родителя и потомка для определения координат ячеек и т.д.
            $('div.text-center').append(line); // добавляет на страницу линию, сооединяющую две ячейки из выборки
        });
    }
}

/**
 * Определяет координаты ячеек, длину их соединительной линии, и угол её наклона
 * @param el1 - ячека родителя
 * @param el2 - ячейка потомка
 * @returns {string} - возвращает блок со стилями описывающими линию, которая соединяет две ячейки
 */
function createLine(el1, el2) {
    let dx1 = el1.offset().left + el1.width();
    let dy1 = el1.offset().top + el1.height() * 1.5;
    let dx2 = el2.offset().left + el2.width();
    let dy2 = el2.offset().top;
    let length = Math.sqrt(((dx2 - dx1) * (dx2 - dx1)) + ((dy2 - dy1) * (dy2 - dy1)));
    let cx = ((dx1 + dx2) / 2) - (length / 2);
    let cy = ((dy1 + dy2) / 2) - (2 / 2);
    let angle = Math.atan2((dy1 - dy2), (dx1 - dx2)) * (180 / Math.PI);

    return "<div class='lines' style='left:" + cx + "px; top:" + cy + "px; width:" + length + "px; -webkit-transform:rotate(" + angle + "deg); transform:rotate(" + angle + "deg);position: absolute; height: 1px; border: 2px solid #555555; background-color: #555555;'></div>";
};