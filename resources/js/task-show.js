// Функции для работы с задачей
document.addEventListener('DOMContentLoaded', function() {
    initTaskFunctions();
});

// Делаем функции глобальными для доступа из HTML onclick
window.editTaskTitle = editTaskTitle;
window.handleTitleKeyPress = handleTitleKeyPress;
window.saveTaskTitle = saveTaskTitle;
window.cancelTitleEdit = cancelTitleEdit;
window.toggleStatusMenu = toggleStatusMenu;
window.changePriority = changePriority;
window.toggleDatePicker = toggleDatePicker;
window.saveStartDate = saveStartDate;
window.saveDueDate = saveDueDate;
window.clearAllDates = clearAllDates;
window.closeDatePicker = closeDatePicker;
window.toggleAssigneeMenu = toggleAssigneeMenu;
window.changeAssignee = changeAssignee;
window.formatText = formatText;
window.toggleFormatMenu = toggleFormatMenu;
window.insertLink = insertLink;
window.insertList = insertList;
window.printTask = printTask;
window.downloadTaskPDF = downloadTaskPDF;
window.addTextBlock = addTextBlock;
window.triggerImageUpload = triggerImageUpload;

function initTaskFunctions() {
    // Инициализация приоритета
    const statusSquare = document.getElementById('statusSquare');
    if (statusSquare) {
        updateStatusSquare(statusSquare.dataset.priority);
    }

    // Закрытие меню при клике вне их
    document.addEventListener('click', function(event) {
        const statusMenu = document.getElementById('statusMenu');
        const statusSquare = document.getElementById('statusSquare');
        const datePickerContainer = document.getElementById('datePickerContainer');
        const timeIcon = document.querySelector('.time-icon');
        const assigneeMenu = document.getElementById('assigneeMenu');
        const assigneeContainer = document.querySelector('.assignee-container');

        // Закрытие меню статуса
        if (statusMenu && !statusSquare.contains(event.target)) {
            statusMenu.classList.add('d-none');
        }

        // Закрытие календаря
        if (datePickerContainer && !datePickerContainer.contains(event.target) && !timeIcon.contains(event.target)) {
            datePickerContainer.classList.add('d-none');
        }

        // Закрытие меню исполнителей
        if (assigneeMenu && !assigneeContainer.contains(event.target)) {
            assigneeMenu.classList.add('d-none');
        }
    });
}

// Функции для работы с названием задачи
function editTaskTitle() {
    const titleElement = document.getElementById('taskTitle');
    const inputElement = document.getElementById('taskTitleInput');
    
    if (titleElement && inputElement) {
        titleElement.classList.add('d-none');
        inputElement.classList.remove('d-none');
        inputElement.focus();
        inputElement.select();
    }
}

function handleTitleKeyPress(event) {
    if (event.key === 'Enter') {
        saveTaskTitle();
    } else if (event.key === 'Escape') {
        cancelTitleEdit();
    }
}

function saveTaskTitle() {
    const titleElement = document.getElementById('taskTitle');
    const inputElement = document.getElementById('taskTitleInput');
    const taskHeaderFixed = document.querySelector('.task-header-fixed');
    
    if (!titleElement || !inputElement || !taskHeaderFixed) return;

    const newTitle = inputElement.value.trim();
    const taskId = taskHeaderFixed.dataset.taskId;
    const csrfToken = taskHeaderFixed.dataset.csrfToken;

    // Отправка AJAX запроса
    fetch(`/tasks/${taskId}/title`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            title: newTitle || 'Без названия'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            titleElement.textContent = newTitle || 'Без названия';
            showNotification('Название задачи обновлено', 'success');
        } else {
            showNotification('Ошибка при обновлении названия', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Ошибка при обновлении названия', 'error');
    });

    // Возврат к отображению
    inputElement.classList.add('d-none');
    titleElement.classList.remove('d-none');
}

function cancelTitleEdit() {
    const titleElement = document.getElementById('taskTitle');
    const inputElement = document.getElementById('taskTitleInput');
    
    if (titleElement && inputElement) {
        inputElement.classList.add('d-none');
        titleElement.classList.remove('d-none');
        // Возврат к исходному значению
        inputElement.value = titleElement.textContent;
    }
}

// Функции для работы со статусом/приоритетом
function toggleStatusMenu() {
    const statusMenu = document.getElementById('statusMenu');
    if (statusMenu) {
        statusMenu.classList.toggle('d-none');
    }
}

function changePriority(priority) {
    const taskHeaderFixed = document.querySelector('.task-header-fixed');
    const statusSquare = document.getElementById('statusSquare');
    const statusMenu = document.getElementById('statusMenu');
    
    if (!taskHeaderFixed || !statusSquare) return;

    const taskId = taskHeaderFixed.dataset.taskId;
    const csrfToken = taskHeaderFixed.dataset.csrfToken;

    // Отправка AJAX запроса
    fetch(`/tasks/${taskId}/priority`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            priority: priority
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            statusSquare.dataset.priority = priority;
            updateStatusSquare(priority);
            showNotification('Приоритет задачи обновлен', 'success');
        } else {
            showNotification('Ошибка при обновлении приоритета', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Ошибка при обновлении приоритета', 'error');
    });

    // Закрытие меню
    if (statusMenu) {
        statusMenu.classList.add('d-none');
    }
}

function updateStatusSquare(priority) {
    const statusSquare = document.getElementById('statusSquare');
    if (!statusSquare) return;

    // Удаление всех классов приоритета
    statusSquare.className = 'status-square';
    
    // Добавление класса для текущего приоритета
    statusSquare.classList.add(priority);
}

// Функции для работы с датами
function toggleDatePicker() {
    const datePickerContainer = document.getElementById('datePickerContainer');
    if (datePickerContainer) {
        datePickerContainer.classList.toggle('d-none');
    }
}

function saveStartDate() {
    const startDateInput = document.getElementById('startDateInput');
    const taskHeaderFixed = document.querySelector('.task-header-fixed');
    
    if (!startDateInput || !taskHeaderFixed) return;

    const taskId = taskHeaderFixed.dataset.taskId;
    const csrfToken = taskHeaderFixed.dataset.csrfToken;
    const startDate = startDateInput.value;

    // Отправка AJAX запроса
    fetch(`/tasks/${taskId}/start-date`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            start_date: startDate
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Дата начала обновлена', 'success');
        } else {
            showNotification('Ошибка при обновлении даты начала', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Ошибка при обновлении даты начала', 'error');
    });
}

function saveDueDate() {
    const dueDateInput = document.getElementById('dueDateInput');
    const taskHeaderFixed = document.querySelector('.task-header-fixed');
    
    if (!dueDateInput || !taskHeaderFixed) return;

    const taskId = taskHeaderFixed.dataset.taskId;
    const csrfToken = taskHeaderFixed.dataset.csrfToken;
    const dueDate = dueDateInput.value;

    // Отправка AJAX запроса
    fetch(`/tasks/${taskId}/due-date`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            due_date: dueDate
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Дата окончания обновлена', 'success');
        } else {
            showNotification('Ошибка при обновлении даты окончания', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Ошибка при обновлении даты окончания', 'error');
    });
}

function clearAllDates() {
    const startDateInput = document.getElementById('startDateInput');
    const dueDateInput = document.getElementById('dueDateInput');
    
    if (startDateInput) {
        startDateInput.value = '';
        saveStartDate();
    }
    
    if (dueDateInput) {
        dueDateInput.value = '';
        saveDueDate();
    }
}

function closeDatePicker() {
    const datePickerContainer = document.getElementById('datePickerContainer');
    if (datePickerContainer) {
        datePickerContainer.classList.add('d-none');
    }
}

// Функции для работы с исполнителями
function toggleAssigneeMenu() {
    const assigneeMenu = document.getElementById('assigneeMenu');
    if (assigneeMenu) {
        assigneeMenu.classList.toggle('d-none');
    }
}

function changeAssignee(userId) {
    const taskHeaderFixed = document.querySelector('.task-header-fixed');
    const assigneeMenu = document.getElementById('assigneeMenu');
    
    if (!taskHeaderFixed) return;

    const taskId = taskHeaderFixed.dataset.taskId;
    const csrfToken = taskHeaderFixed.dataset.csrfToken;

    // Отправка AJAX запроса
    fetch(`/tasks/${taskId}/assignee`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            assignee_id: userId || null
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Исполнитель задачи обновлен', 'success');
            // Перезагрузка страницы для обновления информации об исполнителе
            location.reload();
        } else {
            showNotification('Ошибка при обновлении исполнителя', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Ошибка при обновлении исполнителя', 'error');
    });

    // Закрытие меню
    if (assigneeMenu) {
        assigneeMenu.classList.add('d-none');
    }
}

// Функция для показа уведомлений
function showNotification(message, type = 'info') {
    // Создание элемента уведомления
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} notification-toast`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        opacity: 0;
        transform: translateX(100%);
        transition: all 0.3s ease;
    `;
    notification.textContent = message;

    document.body.appendChild(notification);

    // Анимация появления
    setTimeout(() => {
        notification.style.opacity = '1';
        notification.style.transform = 'translateX(0)';
    }, 100);

    // Автоматическое удаление
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// === СИСТЕМА ЛИСТОВ A4 ===

// Делаем новые функции глобальными
window.handleImageUpload = handleImageUpload;
window.addPageBreak = addPageBreak;
window.saveContent = saveContent;
window.addContentBlock = addContentBlock;
window.deletePage = deletePage;
window.deleteBlock = deleteBlock;
window.moveBlockUp = moveBlockUp;
window.moveBlockDown = moveBlockDown;
window.showImageModal = showImageModal;
window.initContentDragAndDrop = initContentDragAndDrop;
window.initPageDragAndDrop = initPageDragAndDrop;
window.createTestBlock = createTestBlock;

let autoSaveTimeout;
let currentActiveBlock = null;

// Инициализация системы листов
document.addEventListener('DOMContentLoaded', function() {
    initA4System();
});

// Функция для обновления существующих элементов
function updateExistingElements(canvas) {
    console.log('Обновление существующих элементов...');
    
    // Обновляем существующие блоки
    const existingBlocks = canvas.querySelectorAll('.content-block');
    existingBlocks.forEach((block, index) => {
        // Убираем draggable
        block.draggable = false;
        
        // Устанавливаем ID если его нет
        if (!block.id) {
            block.id = 'block_' + Date.now() + '_' + index;
        }
        
        // Ищем drag handle или создаем его
        let dragHandle = block.querySelector('.drag-handle');
        if (!dragHandle) {
            // Если нет drag handle, создаем toolbar с ним
            let toolbar = block.querySelector('.block-toolbar');
            if (!toolbar) {
                toolbar = document.createElement('div');
                toolbar.className = 'block-toolbar';
                toolbar.innerHTML = `
                    <button class="block-btn drag-handle" data-block-id="${block.id}" title="Перетащить блок">
                        <i class="fas fa-grip-vertical"></i>
                    </button>
                    <button class="block-btn" onclick="moveBlockUp('${block.id}')" title="Переместить вверх">
                        <i class="fas fa-arrow-up"></i>
                    </button>
                    <button class="block-btn" onclick="moveBlockDown('${block.id}')" title="Переместить вниз">
                        <i class="fas fa-arrow-down"></i>
                    </button>
                    <button class="block-btn delete-btn" onclick="deleteBlock('${block.id}')" title="Удалить блок">
                        <i class="fas fa-trash"></i>
                    </button>
                `;
                block.insertBefore(toolbar, block.firstChild);
            }
            dragHandle = toolbar.querySelector('.drag-handle');
        }
        
        // Устанавливаем data-block-id
        if (dragHandle) {
            dragHandle.dataset.blockId = block.id;
            console.log('Обновлен drag handle для блока:', block.id);
        }
    });
    
    // Обновляем существующие страницы
    const existingPages = canvas.querySelectorAll('.a4-page');
    existingPages.forEach((page, index) => {
        // Убираем draggable
        page.draggable = false;
        
        // Устанавливаем data-page если его нет
        if (!page.dataset.page) {
            page.dataset.page = index + 1;
        }
        
        // Ищем page drag handle
        const pageDragHandle = page.querySelector('.page-drag-handle');
        if (pageDragHandle) {
            pageDragHandle.dataset.pageNumber = page.dataset.page;
            console.log('Обновлен page drag handle для страницы:', page.dataset.page);
        } else {
            console.log('Page drag handle не найден для страницы:', page.dataset.page);
        }
    });
    
    console.log('Существующие элементы обновлены');
}

function initA4System() {
    console.log('Инициализация A4 системы...');
    
    // Инициализация drag & drop для изображений
    const contentCanvas = document.getElementById('contentCanvas');
    if (contentCanvas) {
        console.log('Canvas найден, инициализируем drag & drop');
        
        // Обновляем существующие элементы
        updateExistingElements(contentCanvas);
        
        initDragAndDrop(contentCanvas);
        initContentDragAndDrop(contentCanvas);
        initPageDragAndDrop(contentCanvas);
    } else {
        console.error('Canvas не найден!');
    }

    // Инициализация автосохранения
    initAutoSave();
    
    // Инициализация форматирования текста
    initTextFormatting();

    // Обработка кликов по блокам контента
    document.addEventListener('click', function(event) {
        const contentBlock = event.target.closest('.content-block');
        if (contentBlock) {
            setActiveBlock(contentBlock);
        } else {
            clearActiveBlock();
        }
    });

    // Обработка ввода в текстовых блоках
    document.addEventListener('input', function(event) {
        if (event.target.classList.contains('text-content')) {
            scheduleAutoSave();
        }
    });

    // Отключение перетаскивания при редактировании текста
    document.addEventListener('focusin', function(event) {
        if (event.target.classList.contains('text-content') || event.target.classList.contains('image-caption')) {
            const contentBlock = event.target.closest('.content-block');
            if (contentBlock) {
                contentBlock.draggable = false;
            }
        }
    });

    // Включение перетаскивания после окончания редактирования
    document.addEventListener('focusout', function(event) {
        if (event.target.classList.contains('text-content') || event.target.classList.contains('image-caption')) {
            // НЕ устанавливаем draggable автоматически, это будет делаться через handle
            const contentBlock = event.target.closest('.content-block');
            if (contentBlock) {
                contentBlock.draggable = false; // Убеждаемся, что остается false
            }
        }
    });
}

// Добавление текстового блока
function addTextBlock() {
    const canvas = document.getElementById('contentCanvas');
    const pages = canvas.querySelectorAll('.a4-page');
    
    if (pages.length === 0) {
        createNewPage();
    }
    
    const lastPage = pages[pages.length - 1];
    const pageContent = lastPage.querySelector('.page-content');
    
    // Удаляем empty-page если есть
    const emptyPage = pageContent.querySelector('.empty-page');
    if (emptyPage) {
        emptyPage.remove();
    }
    
    const textBlock = createTextBlock();
    pageContent.appendChild(textBlock);
    
    // Фокус на новый текстовый блок
    const textContent = textBlock.querySelector('.text-content');
    textContent.focus();
    
    setActiveBlock(textBlock);
}

// Создание текстового блока
function createTextBlock() {
    const blockId = 'block_' + Date.now();
    const textBlock = document.createElement('div');
    textBlock.className = 'content-block text-block';
    textBlock.id = blockId;
    // Убираем draggable с блока, добавим его динамически
    
    textBlock.innerHTML = `
        <div class="block-toolbar">
            <button class="block-btn drag-handle" title="Перетащить блок" data-block-id="${blockId}">
                <i class="fas fa-grip-vertical"></i>
            </button>
            <button class="block-btn" onclick="moveBlockUp('${blockId}')" title="Переместить вверх">
                <i class="fas fa-arrow-up"></i>
            </button>
            <button class="block-btn" onclick="moveBlockDown('${blockId}')" title="Переместить вниз">
                <i class="fas fa-arrow-down"></i>
            </button>
            <button class="block-btn delete-btn" onclick="deleteBlock('${blockId}')" title="Удалить блок">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        <div class="text-content" contenteditable="true" data-placeholder="Введите текст..."></div>
    `;
    
    return textBlock;
}

// Запуск загрузки изображения
function triggerImageUpload() {
    const imageUploader = document.getElementById('imageUploader');
    imageUploader.click();
}

// Обработка загрузки изображения
function handleImageUpload(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    if (!file.type.startsWith('image/')) {
        showNotification('Пожалуйста, выберите файл изображения', 'error');
        return;
    }
    
    const reader = new FileReader();
    reader.onload = function(e) {
        addImageBlock(e.target.result, file.name);
    };
    reader.readAsDataURL(file);
    
    // Очищаем input для возможности повторной загрузки того же файла
    event.target.value = '';
}

// Добавление блока изображения
function addImageBlock(imageSrc, fileName) {
    const canvas = document.getElementById('contentCanvas');
    const pages = canvas.querySelectorAll('.a4-page');
    
    if (pages.length === 0) {
        createNewPage();
    }
    
    const lastPage = pages[pages.length - 1];
    const pageContent = lastPage.querySelector('.page-content');
    
    // Удаляем empty-page если есть
    const emptyPage = pageContent.querySelector('.empty-page');
    if (emptyPage) {
        emptyPage.remove();
    }
    
    const imageBlock = createImageBlock(imageSrc, fileName);
    pageContent.appendChild(imageBlock);
    
    setActiveBlock(imageBlock);
    scheduleAutoSave();
}

// Создание блока изображения
function createImageBlock(imageSrc, fileName) {
    const blockId = 'block_' + Date.now();
    const imageBlock = document.createElement('div');
    imageBlock.className = 'content-block image-block';
    imageBlock.id = blockId;
    // Убираем draggable с блока, добавим его динамически
    
    imageBlock.innerHTML = `
        <div class="block-toolbar">
            <button class="block-btn drag-handle" title="Перетащить блок" data-block-id="${blockId}">
                <i class="fas fa-grip-vertical"></i>
            </button>
            <button class="block-btn" onclick="moveBlockUp('${blockId}')" title="Переместить вверх">
                <i class="fas fa-arrow-up"></i>
            </button>
            <button class="block-btn" onclick="moveBlockDown('${blockId}')" title="Переместить вниз">
                <i class="fas fa-arrow-down"></i>
            </button>
            <button class="block-btn delete-btn" onclick="deleteBlock('${blockId}')" title="Удалить блок">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        <img src="${imageSrc}" alt="${fileName}" onclick="showImageModal('${imageSrc}', '${fileName}')">
        <div class="image-caption" contenteditable="true" data-placeholder="Добавить подпись к изображению..."></div>
    `;
    
    return imageBlock;
}

// Добавление нового листа
function addPageBreak() {
    createNewPage();
}

// Создание нового листа A4
function createNewPage() {
    const canvas = document.getElementById('contentCanvas');
    const pages = canvas.querySelectorAll('.a4-page');
    const pageNumber = pages.length + 1;
    
    const newPage = document.createElement('div');
    newPage.className = 'a4-page';
    newPage.dataset.page = pageNumber;
    // Убираем draggable с страницы, добавим его динамически
    
    newPage.innerHTML = `
        <div class="page-header">
            <span class="page-number">Лист ${pageNumber}</span>
            <div class="page-actions">
                <button class="page-btn page-drag-handle" title="Перетащить лист" data-page-number="${pageNumber}">
                    <i class="fas fa-grip-vertical"></i>
                </button>
                <button class="page-btn" onclick="deletePage(this)" title="Удалить лист">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        <div class="page-content" onclick="addContentBlock(event)">
            <div class="empty-page">
                <i class="fas fa-plus-circle"></i>
                <p>Нажмите здесь, чтобы добавить контент</p>
            </div>
        </div>
    `;
    
    canvas.appendChild(newPage);
    
    // Прокрутка к новому листу
    newPage.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// Добавление контента при клике на пустой лист
function addContentBlock(event) {
    // Проверяем, что клик был именно по empty-page
    if (event.target.closest('.empty-page')) {
        addTextBlock();
    }
}

// Удаление листа
function deletePage(button) {
    const page = button.closest('.a4-page');
    const canvas = document.getElementById('contentCanvas');
    const pages = canvas.querySelectorAll('.a4-page');
    
    // Не удаляем последний лист
    if (pages.length <= 1) {
        showNotification('Нельзя удалить последний лист', 'error');
        return;
    }
    
    if (confirm('Вы уверены, что хотите удалить этот лист? Все содержимое будет потеряно.')) {
        page.remove();
        renumberPages();
        scheduleAutoSave();
    }
}

// Перенумерация листов
function renumberPages() {
    const canvas = document.getElementById('contentCanvas');
    const pages = canvas.querySelectorAll('.a4-page');
    
    pages.forEach((page, index) => {
        const pageNumber = index + 1;
        page.dataset.page = pageNumber;
        const pageNumberSpan = page.querySelector('.page-number');
        pageNumberSpan.textContent = `Лист ${pageNumber}`;
    });
}

// Удаление блока
function deleteBlock(blockId) {
    const block = document.getElementById(blockId);
    if (block && confirm('Удалить этот блок?')) {
        block.remove();
        scheduleAutoSave();
        
        // Проверяем, остались ли блоки на странице
        const page = block.closest('.a4-page');
        if (page) {
            const pageContent = page.querySelector('.page-content');
            const remainingBlocks = pageContent.querySelectorAll('.content-block');
            
            if (remainingBlocks.length === 0) {
                // Добавляем empty-page если блоков не осталось
                const emptyPage = document.createElement('div');
                emptyPage.className = 'empty-page';
                emptyPage.onclick = addContentBlock;
                emptyPage.innerHTML = `
                    <i class="fas fa-plus-circle"></i>
                    <p>Нажмите здесь, чтобы добавить контент</p>
                `;
                pageContent.appendChild(emptyPage);
            }
        }
    }
}

// Перемещение блока вверх
function moveBlockUp(blockId) {
    const block = document.getElementById(blockId);
    const prevBlock = block.previousElementSibling;
    
    if (prevBlock && prevBlock.classList.contains('content-block')) {
        block.parentNode.insertBefore(block, prevBlock);
        scheduleAutoSave();
    }
}

// Перемещение блока вниз
function moveBlockDown(blockId) {
    const block = document.getElementById(blockId);
    const nextBlock = block.nextElementSibling;
    
    if (nextBlock && nextBlock.classList.contains('content-block')) {
        block.parentNode.insertBefore(nextBlock, block);
        scheduleAutoSave();
    }
}

// Установка активного блока
function setActiveBlock(block) {
    clearActiveBlock();
    currentActiveBlock = block;
    block.classList.add('active');
}

// Очистка активного блока
function clearActiveBlock() {
    if (currentActiveBlock) {
        currentActiveBlock.classList.remove('active');
        currentActiveBlock = null;
    }
}

// Показ модального окна с изображением
function showImageModal(imageSrc, fileName) {
    const modal = new bootstrap.Modal(document.getElementById('imageModal'));
    const modalImage = document.getElementById('modalImage');
    const modalTitle = document.querySelector('#imageModal .modal-title');
    
    modalImage.src = imageSrc;
    modalImage.alt = fileName;
    modalTitle.textContent = fileName || 'Изображение';
    
    modal.show();
}

// Сохранение контента
function saveContent() {
    const canvas = document.getElementById('contentCanvas');
    const content = canvas.innerHTML;
    const taskHeaderFixed = document.querySelector('.task-header-fixed');
    
    if (!taskHeaderFixed) return;
    
    const taskId = taskHeaderFixed.dataset.taskId;
    const csrfToken = taskHeaderFixed.dataset.csrfToken;
    
    showSaveStatus('saving');
    
    fetch(`/tasks/${taskId}/content`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            content: content
        })
    })
    .then(response => response.json())
    .then(data => {
        showSaveStatus('saved');
        showNotification('Контент успешно сохранен', 'success');
    })
    .catch(error => {
        console.error('Ошибка сохранения:', error);
        showSaveStatus('error');
        showNotification('Ошибка при сохранении контента', 'error');
    });
}

// Инициализация автосохранения
function initAutoSave() {
    // Автосохранение каждые 30 секунд
    setInterval(() => {
        const canvas = document.getElementById('contentCanvas');
        const hasContent = canvas.querySelectorAll('.content-block').length > 0;
        
        if (hasContent) {
            showAutoSaveIndicator();
            saveContent();
        }
    }, 30000);
}

// Планирование автосохранения
function scheduleAutoSave() {
    clearTimeout(autoSaveTimeout);
    autoSaveTimeout = setTimeout(() => {
        showAutoSaveIndicator();
        saveContent();
    }, 2000);
}

// Показ статуса сохранения
function showSaveStatus(status) {
    const saveStatus = document.getElementById('saveStatus');
    if (!saveStatus) return;
    
    saveStatus.className = `save-status ${status}`;
    saveStatus.classList.remove('d-none');
    
    const icon = saveStatus.querySelector('i');
    const text = saveStatus.querySelector('span');
    
    switch (status) {
        case 'saving':
            icon.className = 'fas fa-sync-alt fa-spin';
            text.textContent = 'Сохранение...';
            break;
        case 'saved':
            icon.className = 'fas fa-check-circle';
            text.textContent = 'Сохранено';
            setTimeout(() => {
                saveStatus.classList.add('d-none');
            }, 2000);
            break;
        case 'error':
            icon.className = 'fas fa-exclamation-circle';
            text.textContent = 'Ошибка';
            setTimeout(() => {
                saveStatus.classList.add('d-none');
            }, 3000);
            break;
    }
}

// Показ индикатора автосохранения
function showAutoSaveIndicator() {
    const indicator = document.getElementById('autoSaveIndicator');
    if (!indicator) return;
    
    indicator.classList.add('show');
    
    setTimeout(() => {
        indicator.classList.remove('show');
    }, 2000);
}

// Инициализация drag & drop
function initDragAndDrop(canvas) {
    canvas.addEventListener('dragover', function(e) {
        e.preventDefault();
        canvas.classList.add('drag-over');
    });
    
    canvas.addEventListener('dragleave', function(e) {
        e.preventDefault();
        canvas.classList.remove('drag-over');
    });
    
    canvas.addEventListener('drop', function(e) {
        e.preventDefault();
        canvas.classList.remove('drag-over');
        
        const files = e.dataTransfer.files;
        for (let file of files) {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    addImageBlock(event.target.result, file.name);
                };
                reader.readAsDataURL(file);
            }
        }
    });
}

// Инициализация drag & drop для блоков контента
function initContentDragAndDrop(canvas) {
    console.log('Инициализация drag & drop для блоков контента');
    let draggedBlock = null;
    let draggedFromPage = null;
    let dropIndicator = null;
    let isDragActive = false;

    // Общий обработчик dragover для всего canvas
    canvas.addEventListener('dragover', function(e) {
        // Всегда разрешаем drop
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
    });

    // Обработчики для активации drag через handle для БЛОКОВ
    canvas.addEventListener('mousedown', function(e) {
        const dragHandle = e.target.closest('.drag-handle');
        if (dragHandle) {
            console.log('Mousedown на drag handle:', dragHandle);
            const blockId = dragHandle.dataset.blockId;
            const block = document.getElementById(blockId);
            if (block) {
                // Активируем draggable только для этого блока
                block.draggable = true;
                isDragActive = true;
                console.log('Draggable активирован для блока:', blockId);
                
                // Добавляем визуальную индикацию
                block.classList.add('drag-ready');
                
                // Предотвращаем срабатывание других обработчиков
                e.stopPropagation();
            }
        }
    });

    canvas.addEventListener('mouseup', function(e) {
        // Деактивируем draggable для всех блоков только если drag не активен
        if (!isDragActive) {
            const allBlocks = canvas.querySelectorAll('.content-block');
            allBlocks.forEach(block => {
                block.draggable = false;
                block.classList.remove('drag-ready');
            });
            console.log('Draggable деактивирован для всех блоков');
        }
    });

    // Обработчик для прекращения drag при отпускании мыши в любом месте
    document.addEventListener('mouseup', function(e) {
        setTimeout(() => {
            if (!isDragActive) {
                const allBlocks = canvas.querySelectorAll('.content-block');
                allBlocks.forEach(block => {
                    block.draggable = false;
                    block.classList.remove('drag-ready');
                });
            }
        }, 100);
    });

    // Создание индикатора места вставки
    function createDropIndicator() {
        const indicator = document.createElement('div');
        indicator.className = 'drop-indicator';
        indicator.style.cssText = `
            height: 2px;
            background: #007bff;
            margin: 10px 0;
            border-radius: 1px;
            opacity: 0.8;
        `;
        return indicator;
    }

    // Обработка начала перетаскивания блока (только для блоков, не для страниц)
    canvas.addEventListener('dragstart', function(e) {
        console.log('Dragstart event triggered', e.target);
        
        const block = e.target.closest('.content-block');
        const page = e.target;
        
        // Убеждаемся, что это блок, а не страница
        if (block && block.draggable === true && !page.classList.contains('a4-page')) {
            console.log('Block dragstart для блока:', block);
            console.log('Element classes:', e.target.className);
            console.log('Parent classes:', e.target.parentElement ? e.target.parentElement.className : 'no parent');
            
            console.log('Начинаем перетаскивание блока через handle');
            draggedBlock = block;
            draggedFromPage = block.closest('.a4-page');
            block.style.opacity = '0.5';
            block.classList.add('dragging');
            isDragActive = true;
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/html', block.outerHTML);
            e.dataTransfer.setData('text/plain', block.id);
            e.stopPropagation(); // Предотвращаем всплытие
        } else if (block && block.draggable === false) {
            // Отменяем перетаскивание если блок не активирован для перетаскивания
            console.log('Отменяем перетаскивание - блок не активирован для перетаскивания');
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
    });

    // Обработка окончания перетаскивания блока
    canvas.addEventListener('dragend', function(e) {
        console.log('Dragend event triggered');
        const block = e.target.closest('.content-block');
        if (block && draggedBlock && block === draggedBlock) {
            block.style.opacity = '';
            block.classList.remove('dragging', 'drag-ready');
            block.draggable = false;
            console.log('Dragend: состояние блока сброшено');
        }
        if (dropIndicator && dropIndicator.parentNode) {
            dropIndicator.parentNode.removeChild(dropIndicator);
            dropIndicator = null;
            console.log('Dragend: индикатор удален');
        }
        draggedBlock = null;
        draggedFromPage = null;
        isDragActive = false;
        console.log('Drag operation completed');
    });

    // Обработка перетаскивания над элементами
    canvas.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        
        if (!draggedBlock || !isDragActive) return;

        const pageContent = e.target.closest('.page-content');
        if (!pageContent) return;
        
        console.log('Dragover на странице', pageContent);

        const afterElement = getDragAfterElement(pageContent, e.clientY);
        
        if (!dropIndicator) {
            dropIndicator = createDropIndicator();
        }

        if (afterElement == null) {
            pageContent.appendChild(dropIndicator);
            console.log('Добавляем индикатор в конец');
        } else {
            pageContent.insertBefore(dropIndicator, afterElement);
            console.log('Добавляем индикатор перед элементом', afterElement);
        }
    });

    // Обработка сброса блока
    canvas.addEventListener('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        console.log('Drop event triggered!', draggedBlock, dropIndicator);
        
        if (!draggedBlock || !dropIndicator || !isDragActive) {
            console.log('Условия для drop не выполнены');
            return;
        }

        const pageContent = dropIndicator.parentNode;
        if (pageContent && pageContent.classList.contains('page-content')) {
            console.log('Перемещаем блок в', pageContent);
            
            // Удаляем empty-page если есть
            const emptyPage = pageContent.querySelector('.empty-page');
            if (emptyPage) {
                emptyPage.remove();
            }

            // Перемещаем блок
            pageContent.insertBefore(draggedBlock, dropIndicator);
            
            // Удаляем индикатор
            dropIndicator.parentNode.removeChild(dropIndicator);
            dropIndicator = null;

            // Сбрасываем состояние
            draggedBlock.style.opacity = '';
            draggedBlock.classList.remove('dragging', 'drag-ready');
            draggedBlock.draggable = false;
            isDragActive = false;

            // Планируем автосохранение
            scheduleAutoSave();
            
            showNotification('Блок перемещен', 'success');
            console.log('Блок успешно перемещен');
        }
    });

    // Функция для определения позиции вставки
    function getDragAfterElement(container, y) {
        const draggableElements = [...container.querySelectorAll('.content-block:not(.dragging)')];
        
        return draggableElements.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            
            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            } else {
                return closest;
            }
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }
}

// Инициализация drag & drop для листов A4
function initPageDragAndDrop(canvas) {
    console.log('Инициализация drag & drop для листов');
    let draggedPage = null;
    let pageDropIndicator = null;

    // Общий обработчик dragover для всего canvas (для страниц)
    canvas.addEventListener('dragover', function(e) {
        // Всегда разрешаем drop для страниц тоже
        if (draggedPage) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
        }
    });

    // Обработчики для активации drag через handle для СТРАНИЦ
    canvas.addEventListener('mousedown', function(e) {
        const pageDragHandle = e.target.closest('.page-drag-handle');
        if (pageDragHandle) {
            console.log('Mousedown на page drag handle:', pageDragHandle);
            const pageNumber = pageDragHandle.dataset.pageNumber;
            const page = canvas.querySelector(`.a4-page[data-page="${pageNumber}"]`);
            if (page) {
                // Активируем draggable только для этой страницы
                page.draggable = true;
                console.log('Draggable активирован для страницы:', pageNumber);
                
                // Добавляем визуальную индикацию
                page.classList.add('drag-ready');
                
                // Предотвращаем срабатывание других обработчиков
                e.stopPropagation();
            }
        }
    });

    canvas.addEventListener('mouseup', function(e) {
        // Деактивируем draggable для всех страниц
        const allPages = canvas.querySelectorAll('.a4-page');
        allPages.forEach(page => {
            page.draggable = false;
            page.classList.remove('drag-ready');
        });
        console.log('Draggable деактивирован для всех страниц');
    });

    // Обработчик для прекращения drag при отпускании мыши в любом месте
    document.addEventListener('mouseup', function(e) {
        const allPages = canvas.querySelectorAll('.a4-page');
        allPages.forEach(page => {
            page.draggable = false;
            page.classList.remove('drag-ready');
        });
    });

    // Создание индикатора места вставки для листов
    function createPageDropIndicator() {
        const indicator = document.createElement('div');
        indicator.className = 'page-drop-indicator';
        indicator.style.cssText = `
            height: 4px;
            background: #28a745;
            margin: 20px auto;
            border-radius: 2px;
            width: 794px;
            opacity: 0.8;
            box-shadow: 0 0 10px rgba(40, 167, 69, 0.5);
        `;
        return indicator;
    }

    // Обработка начала перетаскивания листа (только для страниц, не для блоков)
    canvas.addEventListener('dragstart', function(e) {
        // Проверяем, что это именно страница и НЕ блок внутри неё
        const page = e.target;
        const isPageElement = page.classList.contains('a4-page');
        
        if (isPageElement && page.draggable === true) {
            console.log('Page dragstart для страницы:', page);
            console.log('Начинаем перетаскивание листа через handle');
            draggedPage = page;
            page.style.opacity = '0.6';
            page.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/html', page.outerHTML);
            e.dataTransfer.setData('text/plain', 'page-' + page.dataset.page);
            e.stopPropagation();
        } else if (isPageElement && page.draggable === false) {
            // Отменяем перетаскивание листа, если страница не активирована для перетаскивания
            console.log('Отменяем перетаскивание листа - страница не активирована для перетаскивания');
            e.preventDefault();
            e.stopPropagation();
        }
    }, true); // Используем capture phase для обработки до блоков

    // Обработка окончания перетаскивания листа
    canvas.addEventListener('dragend', function(e) {
        const page = e.target.closest('.a4-page');
        if (page && draggedPage && page === draggedPage) {
            page.style.opacity = '';
            page.classList.remove('dragging', 'drag-ready');
            page.draggable = false;
            console.log('Page dragend: состояние страницы сброшено');
        }
        if (pageDropIndicator && pageDropIndicator.parentNode) {
            pageDropIndicator.parentNode.removeChild(pageDropIndicator);
            pageDropIndicator = null;
            console.log('Page dragend: индикатор страницы удален');
        }
        if (draggedPage) {
            draggedPage = null;
            console.log('Page dragend: draggedPage сброшен');
        }
    });

    // Обработка перетаскивания листов
    canvas.addEventListener('dragover', function(e) {
        if (!draggedPage) return;
        
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        
        const page = e.target.closest('.a4-page');
        if (page && page !== draggedPage) {
            
            if (!pageDropIndicator) {
                pageDropIndicator = createPageDropIndicator();
            }

            const rect = page.getBoundingClientRect();
            const isAfter = e.clientY > rect.top + rect.height / 2;
            
            if (isAfter) {
                page.parentNode.insertBefore(pageDropIndicator, page.nextSibling);
            } else {
                page.parentNode.insertBefore(pageDropIndicator, page);
            }
            
            console.log('Dragover для страницы, показываем индикатор');
        }
    });

    // Обработка сброса листа
    canvas.addEventListener('drop', function(e) {
        console.log('Page drop event triggered', draggedPage, pageDropIndicator);
        
        if (!draggedPage || !pageDropIndicator) {
            console.log('Нет draggedPage или pageDropIndicator для drop страницы');
            return;
        }
        
        e.preventDefault();
        e.stopPropagation();
        
        console.log('Перемещаем страницу');
        
        // Перемещаем лист
        pageDropIndicator.parentNode.insertBefore(draggedPage, pageDropIndicator);
        
        // Удаляем индикатор
        pageDropIndicator.parentNode.removeChild(pageDropIndicator);
        pageDropIndicator = null;

        // Сбрасываем состояние страницы
        draggedPage.style.opacity = '';
        draggedPage.classList.remove('dragging', 'drag-ready');
        draggedPage.draggable = false;

        // Перенумеровываем листы
        renumberPages();
        
        // Планируем автосохранение
        scheduleAutoSave();
        
        showNotification('Лист перемещен', 'success');
        
        // Сбрасываем переменную
        draggedPage = null;
        
        console.log('Страница успешно перемещена');
    });
}

// Функция для тестирования drag & drop
function createTestBlock() {
    console.log('Создаем тестовый блок');
    const canvas = document.getElementById('contentCanvas');
    const pages = canvas.querySelectorAll('.a4-page');
    
    if (pages.length === 0) {
        createNewPage();
    }
    
    const lastPage = pages[pages.length - 1];
    const pageContent = lastPage.querySelector('.page-content');
    
    // Удаляем empty-page если есть
    const emptyPage = pageContent.querySelector('.empty-page');
    if (emptyPage) {
        emptyPage.remove();
    }
    
    const testBlock = createTextBlock();
    testBlock.querySelector('.text-content').textContent = 'Тестовый блок для перетаскивания';
    pageContent.appendChild(testBlock);
    
    console.log('Тестовый блок создан:', testBlock);
    console.log('HTML блока:', testBlock.outerHTML);
    
    // Проверим, есть ли drag handle
    const dragHandle = testBlock.querySelector('.drag-handle');
    console.log('Drag handle найден:', dragHandle);
}

// === ФОРМАТИРОВАНИЕ ТЕКСТА ===

// Переменные для отслеживания активного текстового элемента
let activeTextElement = null;
let formatToolbar = null;

// Функция для форматирования выделенного текста
function formatText(command, value = null) {
    if (!activeTextElement) return;
    
    // Сохраняем фокус
    activeTextElement.focus();
    
    try {
        // Выполняем команду форматирования
        document.execCommand(command, false, value);
        
        // Планируем автосохранение
        scheduleAutoSave();
        
        console.log(`Применено форматирование: ${command}${value ? ' = ' + value : ''}`);
    } catch (error) {
        console.error('Ошибка форматирования:', error);
    }
}

// Показать/скрыть панель форматирования
function toggleFormatMenu() {
    const selection = window.getSelection();
    if (!selection.rangeCount || selection.isCollapsed) {
        hideFormatToolbar();
        return;
    }
    
    const textContent = selection.anchorNode.parentElement.closest('.text-content, .image-caption');
    if (!textContent) {
        hideFormatToolbar();
        return;
    }
    
    activeTextElement = textContent;
    showFormatToolbar(selection);
}

// Показать панель форматирования
function showFormatToolbar(selection) {
    hideFormatToolbar(); // Удаляем предыдущую панель
    
    const range = selection.getRangeAt(0);
    const rect = range.getBoundingClientRect();
    
    formatToolbar = document.createElement('div');
    formatToolbar.className = 'format-toolbar';
    formatToolbar.innerHTML = `
        <div class="format-toolbar-content">
            <button class="format-btn" onclick="formatText('bold')" title="Жирный (Ctrl+B)">
                <i class="fas fa-bold"></i>
            </button>
            <button class="format-btn" onclick="formatText('italic')" title="Курсив (Ctrl+I)">
                <i class="fas fa-italic"></i>
            </button>
            <button class="format-btn" onclick="formatText('underline')" title="Подчеркивание (Ctrl+U)">
                <i class="fas fa-underline"></i>
            </button>
            <button class="format-btn" onclick="formatText('strikeThrough')" title="Зачеркивание">
                <i class="fas fa-strikethrough"></i>
            </button>
            <div class="format-divider"></div>
            <select class="format-select" onchange="formatText('fontSize', this.value)" title="Размер шрифта">
                <option value="">Размер</option>
                <option value="1">Очень малый</option>
                <option value="2">Малый</option>
                <option value="3">Обычный</option>
                <option value="4">Средний</option>
                <option value="5">Большой</option>
                <option value="6">Очень большой</option>
                <option value="7">Огромный</option>
            </select>
            <select class="format-select" onchange="formatText('foreColor', this.value)" title="Цвет текста">
                <option value="">Цвет</option>
                <option value="#000000">Черный</option>
                <option value="#ff0000">Красный</option>
                <option value="#00ff00">Зеленый</option>
                <option value="#0000ff">Синий</option>
                <option value="#ffff00">Желтый</option>
                <option value="#ff00ff">Фиолетовый</option>
                <option value="#00ffff">Голубой</option>
            </select>
            <div class="format-divider"></div>
            <button class="format-btn" onclick="formatText('justifyLeft')" title="По левому краю">
                <i class="fas fa-align-left"></i>
            </button>
            <button class="format-btn" onclick="formatText('justifyCenter')" title="По центру">
                <i class="fas fa-align-center"></i>
            </button>
            <button class="format-btn" onclick="formatText('justifyRight')" title="По правому краю">
                <i class="fas fa-align-right"></i>
            </button>
            <button class="format-btn" onclick="formatText('justifyFull')" title="По ширине">
                <i class="fas fa-align-justify"></i>
            </button>
            <div class="format-divider"></div>
            <button class="format-btn" onclick="insertList('ul')" title="Маркированный список">
                <i class="fas fa-list-ul"></i>
            </button>
            <button class="format-btn" onclick="insertList('ol')" title="Нумерованный список">
                <i class="fas fa-list-ol"></i>
            </button>
            <button class="format-btn" onclick="insertLink()" title="Вставить ссылку">
                <i class="fas fa-link"></i>
            </button>
            <button class="format-btn" onclick="formatText('removeFormat')" title="Очистить форматирование">
                <i class="fas fa-remove-format"></i>
            </button>
        </div>
    `;
    
    // Позиционируем панель
    formatToolbar.style.position = 'fixed';
    formatToolbar.style.top = (rect.top - 60) + 'px';
    formatToolbar.style.left = Math.min(rect.left, window.innerWidth - 400) + 'px';
    formatToolbar.style.zIndex = '9999';
    
    document.body.appendChild(formatToolbar);
    
    // Автоматически скрываем панель через некоторое время или при клике вне её
    setTimeout(() => {
        document.addEventListener('click', hideFormatToolbarOnClick);
        document.addEventListener('selectionchange', checkSelection);
    }, 100);
}

// Скрыть панель форматирования
function hideFormatToolbar() {
    if (formatToolbar && formatToolbar.parentNode) {
        formatToolbar.parentNode.removeChild(formatToolbar);
        formatToolbar = null;
    }
    
    document.removeEventListener('click', hideFormatToolbarOnClick);
    document.removeEventListener('selectionchange', checkSelection);
}

// Скрыть панель при клике вне её
function hideFormatToolbarOnClick(event) {
    if (formatToolbar && !formatToolbar.contains(event.target)) {
        hideFormatToolbar();
    }
}

// Проверить выделение текста
function checkSelection() {
    const selection = window.getSelection();
    if (!selection.rangeCount || selection.isCollapsed) {
        hideFormatToolbar();
    }
}

// Вставка ссылки
function insertLink() {
    const url = prompt('Введите URL ссылки:');
    if (url) {
        formatText('createLink', url);
    }
}

// Вставка списка
function insertList(type) {
    if (type === 'ul') {
        formatText('insertUnorderedList');
    } else if (type === 'ol') {
        formatText('insertOrderedList');
    }
}

// Инициализация обработчиков форматирования
function initTextFormatting() {
    // Обработчик выделения текста
    document.addEventListener('mouseup', function(event) {
        const textContent = event.target.closest('.text-content, .image-caption');
        if (textContent) {
            setTimeout(toggleFormatMenu, 10); // Небольшая задержка для обновления выделения
        }
    });
    
    // Обработчик горячих клавиш
    document.addEventListener('keydown', function(event) {
        const textContent = event.target.closest('.text-content, .image-caption');
        if (!textContent) return;
        
        if (event.ctrlKey || event.metaKey) {
            switch (event.key.toLowerCase()) {
                case 'b':
                    event.preventDefault();
                    activeTextElement = textContent;
                    formatText('bold');
                    break;
                case 'i':
                    event.preventDefault();
                    activeTextElement = textContent;
                    formatText('italic');
                    break;
                case 'u':
                    event.preventDefault();
                    activeTextElement = textContent;
                    formatText('underline');
                    break;
            }
        }
    });
    
    console.log('Форматирование текста инициализировано');
}

// === ФУНКЦИИ ПЕЧАТИ И PDF ===

/**
 * Печать задачи
 */
function printTask() {
    // Подготовка контента для печати
    preparePrintContent();
    
    // Запуск печати
    window.print();
}

/**
 * Скачивание задачи в PDF
 */
async function downloadTaskPDF() {
    const pdfBtn = document.querySelector('.pdf-btn');
    const taskHeaderFixed = document.querySelector('.task-header-fixed');
    
    if (!taskHeaderFixed) {
        showNotification('Ошибка: не удалось найти данные задачи', 'error');
        return;
    }

    const taskId = taskHeaderFixed.dataset.taskId;
    
    try {
        // Показываем индикатор загрузки
        pdfBtn.classList.add('loading');
        pdfBtn.innerHTML = '⏳';
        
        // Создаем ссылку для скачивания PDF
        const pdfUrl = `/tasks/${taskId}/pdf`;
        
        // Создаем скрытую ссылку и кликаем по ней для скачивания PDF
        const link = document.createElement('a');
        link.href = pdfUrl;
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        showNotification('PDF файл скачивается...', 'success');
        
    } catch (error) {
        console.error('Ошибка при создании PDF:', error);
        showNotification('Ошибка при создании PDF файла', 'error');
    } finally {
        // Возвращаем кнопку в исходное состояние через небольшую задержку
        setTimeout(() => {
            pdfBtn.classList.remove('loading');
            pdfBtn.innerHTML = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/><text x="7" y="16" font-size="8" fill="currentColor">PDF</text></svg>`;
        }, 1000);
    }
}

/**
 * Подготовка контента для печати
 */
function preparePrintContent() {
    // Создаем заголовок для печати
    const taskTitle = document.getElementById('taskTitle')?.textContent || 'Без названия';
    const taskContent = document.querySelector('.task-content');
    
    // Удаляем предыдущий заголовок печати если есть
    const existingHeader = document.querySelector('.print-header');
    if (existingHeader) {
        existingHeader.remove();
    }
    
    // Создаем новый заголовок для печати
    const printHeader = document.createElement('div');
    printHeader.className = 'print-header';
    printHeader.innerHTML = `
        <h1 class="print-title">${taskTitle}</h1>
        <div class="print-meta">
            <span>Дата печати: ${new Date().toLocaleDateString('ru-RU')}</span>
            <span>Время: ${new Date().toLocaleTimeString('ru-RU')}</span>
        </div>
    `;
    
    // Вставляем заголовок в начало контента
    if (taskContent) {
        taskContent.insertBefore(printHeader, taskContent.firstChild);
    }
}

// Функции архивирования
window.archiveTask = archiveTask;
window.unarchiveTask = unarchiveTask;

function archiveTask() {
    if (confirm('Вы уверены, что хотите архивировать эту задачу?')) {
        const taskHeaderFixed = document.querySelector('.task-header-fixed');
        const taskId = taskHeaderFixed.dataset.taskId;
        const csrfToken = taskHeaderFixed.dataset.csrfToken;

        fetch(`/tasks/${taskId}/archive`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Задача успешно архивирована', 'success');
                // Обновляем кнопку
                updateArchiveButton(true);
            } else {
                showNotification(data.message || 'Произошла ошибка', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Произошла ошибка при архивировании задачи', 'error');
        });
    }
}

function unarchiveTask() {
    if (confirm('Вы уверены, что хотите разархивировать эту задачу?')) {
        const taskHeaderFixed = document.querySelector('.task-header-fixed');
        const taskId = taskHeaderFixed.dataset.taskId;
        const csrfToken = taskHeaderFixed.dataset.csrfToken;

        fetch(`/tasks/${taskId}/unarchive`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Задача успешно разархивирована', 'success');
                // Обновляем кнопку
                updateArchiveButton(false);
            } else {
                showNotification(data.message || 'Произошла ошибка', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Произошла ошибка при разархивировании задачи', 'error');
        });
    }
}

function updateArchiveButton(isArchived) {
    const archiveBtn = document.querySelector('.archive-btn');
    if (archiveBtn) {
        if (isArchived) {
            archiveBtn.innerHTML = '<i class="fas fa-undo"></i>';
            archiveBtn.title = 'Разархивировать задачу';
            archiveBtn.onclick = unarchiveTask;
        } else {
            archiveBtn.innerHTML = '<i class="fas fa-archive"></i>';
            archiveBtn.title = 'Архивировать задачу';
            archiveBtn.onclick = archiveTask;
        }
    }
}