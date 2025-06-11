function globalInitDragAndDrop(propertyId, propertyType = 'input') {
    if (!propertyId || !propertyType) {
        return;
    }

    const table = document.querySelector(`#tr_PROPERTY_${propertyId} table`);

    if (!table) {
        return;
    }

    const addButtonRow = table.querySelector('tbody > tr:has(input[type="button"]):not(:has(input[type="text"]))');
    // const addButtonRow = table.querySelector('tbody tr:has(input[type="button"])');

    // Стили для drag and drop
    const style = document.createElement('style');
    style.textContent = `
        .drag-handle {
            width: 20px;
            padding-right: 5px;
            vertical-align: middle;
            cursor: grab;
        }
        .drag-handle svg {
            fill: #888;
            transition: all 0.2s ease;
        }
        .drag-handle:hover svg {
            fill: #2196F3;
            transform: scale(1.1);
        }
        .draggable {
            cursor: move;
        }
        .dragging {
            opacity: 0.5;
            background: rgba(33, 150, 243, 0.1);
        }
        .drop-zone {
            min-height: 2px;
            background: #2196F3;
            margin: 2px 0;
        }
        .forbidden {
            background: rgba(255, 0, 0, 0.05);
        }
    `;
    document.head.appendChild(style);

    // Инициализация drag and drop
    function initDragAndDrop() {
        const rows = Array.from(table.querySelectorAll('tr'))
            .filter(row => row !== addButtonRow && (row.querySelector('input[type="text"]') || row.querySelector('select')));

        if (!rows) {
            return;
        }

        // Очищаем предыдущие обработчики
        rows.forEach(row => {
            ['dragstart', 'dragover', 'dragenter', 'dragleave', 'drop', 'dragend'].forEach(event => {
                row.removeEventListener(event, handleDragEvent);
            });
        });

        function rowHandle(row) {
            if (!row.querySelector('.drag-handle')) {
                const handle = document.createElement('span');
                handle.className = 'drag-handle';
                handle.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="currentColor"><path d="M0 0h24v24H0V0z" fill="none"></path><path d="M11 18c0 1.1-.9 2-2 2s-2-.9-2-2 .9-2 2-2 2 .9 2 2zm-2-8c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0-6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm6 4c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"></path></svg>
                `;
                row.querySelector('td').insertBefore(handle, row.querySelector('td').firstChild) //, row.firstChild);
            }

            row.draggable = true;
            row.classList.add('draggable');

            // Добавляем обработчики
            row.addEventListener('dragstart', handleDragEvent);
            row.addEventListener('dragover', handleDragEvent);
            row.addEventListener('dragenter', handleDragEvent);
            row.addEventListener('dragleave', handleDragEvent);
            row.addEventListener('drop', handleDragEvent);
            row.addEventListener('dragend', handleDragEvent);
        }

        // Добавляем handle и делаем строки перетаскиваемыми
        rows.forEach(row => {
            rowHandle(row)
        });

        // if (propertyType === 'element') {
        watch(
            table,
            (nodeElement) => {
                ['dragstart', 'dragover', 'dragenter', 'dragleave', 'drop', 'dragend'].forEach(event => {
                    nodeElement.removeEventListener(event, handleDragEvent);
                });

                rowHandle(nodeElement);
            }
        );
        // }

        // Обработчики для кнопки "Добавить"
        addButtonRow.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'none';
            this.classList.add('forbidden');
        });

        addButtonRow.addEventListener('dragleave', function() {
            this.classList.remove('forbidden');
        });

        addButtonRow.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('forbidden');
        });
    }

    function watch(nodeElement, callback) {
        const observer = new MutationObserver((mutationsList) => {
            for (const mutation of mutationsList) {
                if (mutation.type === 'childList') {
                    // Проверяем, были ли добавлены новые узлы
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            // console.log('Добавлен новый элемент:', node);

                            // Если нужно проверить конкретный элемент (например, tr в tbody)
                            if (node.matches('tbody > tr')) {
                                // console.log('Добавлена новая строка таблицы:', node);
                                callback(node);
                            }
                        }
                    });
                }
            }
        });

        // Начинаем наблюдение за изменениями в body (можно указать любой элемент)
        observer.observe(nodeElement, {
            childList: true,    // Отслеживать добавление/удаление дочерних элементов
            subtree: true       // Отслеживать изменения во всём поддереве
        });

        return observer;
    }

    // Переменные для drag and drop
    let draggedRow = null;
    let dropZone = null;

    function handleDragEvent(e) {
        const row = this;

        switch(e.type) {
            case 'dragstart':
                draggedRow = row;
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', row.id);
                setTimeout(() => row.classList.add('dragging'), 0);
                break;

            case 'dragover':
                if (row !== draggedRow) {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'move';

                    let children= Array.from(e.target.parentNode.parentNode.parentNode.children);
                    let nodeRow = e.target.parentNode.parentNode;
                    let nodeName = e.target.parentNode.nodeName
                    switch (nodeName) {
                        case 'TR':
                            nodeRow = e.target.parentNode
                            break;
                        case 'TD':
                            nodeRow = e.target.parentNode.parentNode
                            break;
                        case 'SPAN':
                            nodeRow = e.target.parentNode.parentNode.parentNode
                            break;
                    }

                    if (nodeRow.nodeName !== 'TR') {
                        nodeRow = nodeRow.parents('tr');
                    }

                    // console.log(e.target.parentNode)
                    // console.log(e.target.parentNode.nodeType)
                    // console.log(e.target.parentNode.nodeValue)
                    // console.log(e.target.parentNode.nodeName)
                    // console.log(e.target.parentNode.parentNode)
                    // console.log(e.target.parentNode.parentNode.parentNode)
                    // console.log(nodeRow)

                    if (children.indexOf(nodeRow) > children.indexOf(draggedRow)) {
                        // e.target.parentNode.parentNode.after(draggedRow);
                        nodeRow.after(draggedRow);
                    } else {
                        // e.target.parentNode.parentNode.before(draggedRow);
                        nodeRow.before(draggedRow);
                    }
                }
                break;

            case 'dragenter':
                e.preventDefault();
                break;

            case 'dragleave':
                // Ничего не делаем, чтобы не мешать работе drop zone
                break;

            case 'drop':
                e.preventDefault();
                if (row !== draggedRow) {
                    //     // Вставляем перетаскиваемую строку
                    //     if (table.contains(dropZone)) {
                    //         table.insertBefore(draggedRow, dropZone);
                    //         table.removeChild(dropZone);
                    //         updateInputNames();
                    //     }
                    updateInputNames2();
                }
                break;

            case 'dragend':
                row.classList.remove('dragging');
                // if (dropZone && table.contains(dropZone)) {
                //     table.removeChild(dropZone);
                // }
                updateInputNames2();
                draggedRow = null;
                dropZone = null;
                // addButtonRow.classList.remove('forbidden');
                break;
        }
    }

    function updateInputNames2() {
        const rows = Array.from(table.querySelectorAll('tr'))
            .filter(row => row !== addButtonRow);

        let identificators = [];
        rows.forEach((row, index) => {
            let input

            if (propertyType === 'select') {
                input = row.querySelector('select');
            } else {
                input = row.querySelector('input');
            }

            if (!input) {
                return;
            }

            const name = input.name;
            if (name.includes('[n')) {
                let match = name.match(/\[n(\d+)\]/);
                if (match[1]) {
                    identificators.push('n'+match[1])
                }
            } else if (name.includes('PROP')) {
                // input.name = name.match(/PROP\[11\]\[\d+\]/, `${index}`);
                let reg = new RegExp(`PROP\\[${propertyId}\\]\\[(\\d+)\\]`)
                let match = name.match(reg);
                if (match && match[1]) {
                    identificators.push(match[1]);
                }
            }
        });
        identificators = identificators.sort();

        let identificatorsIndex = 0
        rows.forEach((row, index) => {
            let input
            if (propertyType === 'select') {
                input = row.querySelector('select');
            } else {
                input = row.querySelector('input');
            }

            // console.log()

            if (!input) {
                return;
            }

            let inputsAll = row.querySelectorAll('input')
            let inputDescription
            if (propertyType === 'select' && inputsAll.length) {
                inputDescription = inputsAll[0]
            } else if (inputsAll.length > 1) {
                inputDescription = inputsAll[1]
            }

            const name = input.name;
            let reg, replaceValue

            if (propertyType === 'select') {
                reg = new RegExp(`PROP\\[${propertyId}\\]\\[.*?\\]\\[VALUE\\]`)
                replaceValue = `PROP[${propertyId}][${identificators[identificatorsIndex]}][VALUE]`
            } else {
                reg = new RegExp(`PROP\\[${propertyId}\\]\\[.*?\\]`)
                replaceValue = `PROP[${propertyId}][${identificators[identificatorsIndex]}]`
            }
            input.name = name.replace(reg, replaceValue);

            if (inputDescription) {
                let inputDescriptionName = inputDescription.name
                reg = new RegExp(`PROP\\[${propertyId}\\]\\[.*?\\]\\[DESCRIPTION\\]`)
                replaceValue = `PROP[${propertyId}][${identificators[identificatorsIndex]}][DESCRIPTION]`
                inputDescription.name = inputDescriptionName.replace(reg, replaceValue);
            }

            identificatorsIndex++;
        });
    }

    // TODO: future release
    // function checkKeyValueExists (className, keyName = 'VALUE') {
    //     reg = new RegExp(`PROP\\[${propertyId}\\]\\[.*?\\]\\[${keyName}\\]`)
    //     return reg.test(className)
    //     // className.match()
    // }

    // Обновление имен полей
    function updateInputNames() {
        const rows = Array.from(table.querySelectorAll('tr'))
            .filter(row => row !== addButtonRow && row.querySelector('input[type="text"]'));

        if (!rows) {
            return;
        }

        const lastRow = rows[rows.length-1];
        if (lastRow) {
            const index = rows.length
            let input
            if (propertyType === 'select') {
                input = lastRow.querySelector('select');
            } else {
                input = lastRow.querySelector('input');
            }

            if (!input) {
                return;
            }

            const name = input.name;
            if (name.includes('[n')) {
                input.name = name.replace(/\[n\d+\]/, `[n${index}]`);
            } else {
                let reg = new RegExp(`PROP\[${propertyId}\]\[(.*?)\]`)
                input.name = name.replace(reg, `[n${index}]`);
            }
            input.value = ''
        }
    }

    // Инициализация и обработка кнопки "Добавить"
    initDragAndDrop();

    const addButton = addButtonRow.querySelector('input[type="button"]');
    const originalOnClick = addButton.onclick;

    addButton.onclick = function() {
        originalOnClick.call(this);
        setTimeout(() => {
            initDragAndDrop();
            updateInputNames();
        }, 500);
    };

}