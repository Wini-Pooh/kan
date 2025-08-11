// Функции для работы с задачей

// Переменная для отслеживания состояния блокировки
let isEditLocked = true;

// Переменная для отслеживания состояния выплывающего меню блокировки
let lockMenuTimeout = null;

document.addEventListener('DOMContentLoaded', function() {
    initTaskFunctions();
});

// Функции для управления выплывающим меню блокировки
window.showLockMenu = showLockMenu;
window.hideLockMenu = hideLockMenu;
window.keepLockMenuOpen = keepLockMenuOpen;

// Делаем функции глобальными для доступа из HTML onclick
window.editTaskTitle = editTaskTitle;
window.handleTitleKeyPress = handleTitleKeyPress;
window.saveTaskTitle = saveTaskTitle;
window.cancelTitleEdit = cancelTitleEdit;
window.toggleStatusMenu = toggleStatusMenu;
window.changePriority = changePriority;
window.changePriorityCompact = changePriorityCompact;
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
window.triggerDocumentUpload = triggerDocumentUpload;
window.toggleEditMode = toggleEditMode;
window.showImageModal = showImageModal;
window.handleAdditionalImageUpload = handleAdditionalImageUpload;
window.handleAdditionalDocumentUpload = handleAdditionalDocumentUpload;
window.addMoreImages = addMoreImages;
window.addMoreDocuments = addMoreDocuments;
window.deleteImageFromSwiper = deleteImageFromSwiper;
window.deleteDocumentFromGrid = deleteDocumentFromGrid;
window.swiperPrev = swiperPrev;
window.swiperNext = swiperNext;
window.swiperGoTo = swiperGoTo;
window.updateAssigneeAvatar = updateAssigneeAvatar;
window.archiveTask = archiveTask;
window.unarchiveTask = unarchiveTask;

// Переменная для отслеживания активного блока для добавления файлов
let currentActiveBlockForUpload = null;

function initTaskFunctions() {
    // Устанавливаем глобальную переменную для task ID
    const taskHeaderFixed = document.querySelector('.task-header-fixed');
    if (taskHeaderFixed) {
        window.taskId = taskHeaderFixed.dataset.taskId;
    }
    
    // Очищаем inline стили блоков при загрузке
    clearInlineBlockStyles();
    
    // Инициализация приоритета
    const statusSquare = document.getElementById('statusSquare');
    const prioritySelector = document.querySelector('.priority-selector-compact');
    let priority = 'low'; // значение по умолчанию
    
    if (statusSquare) {
        priority = statusSquare.dataset.priority;
        updateStatusSquare(priority);
    } else if (prioritySelector) {
        priority = prioritySelector.dataset.priority;
    } else {
        // Получаем из аватара исполнителя
        const assigneeAvatar = document.querySelector('.assignee-avatar-toolbar');
        if (assigneeAvatar) {
            priority = assigneeAvatar.dataset.priority || 'low';
        }
    }
    
    updateAssigneeAvatar(priority);
    updatePriorityCircles(priority); // Инициализация активного кружка

    // Инициализация мобильной адаптации
    initMobileAdaptation();

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
    
    // Инициализация заблокированного состояния
    initializeEditLock();
    
    // Инициализация кликов по изображениям
    const canvas = document.getElementById('contentCanvas');
    if (canvas) {
        initializeImageClicks(canvas);
    }
}

// Функция очистки inline стилей блоков при загрузке
function clearInlineBlockStyles() {
    const canvas = document.getElementById('contentCanvas');
    if (!canvas) return;
    
    // Очищаем inline стили всех блоков
    const allBlocks = canvas.querySelectorAll('.content-block');
    allBlocks.forEach(block => {
        // Убираем inline стили, которые могли быть добавлены JavaScript'ом
        block.style.removeProperty('display');
        block.style.removeProperty('pointer-events');
        
        // Убираем активный класс
        block.classList.remove('active');
        
        // Очищаем стили панелей инструментов
        const toolbar = block.querySelector('.block-toolbar');
        if (toolbar) {
            toolbar.style.removeProperty('display');
            toolbar.style.removeProperty('pointer-events');
        }
    });
}

// Функция для мобильной адаптации
function initMobileAdaptation() {
    // Проверяем, является ли устройство мобильным
    const isMobile = window.innerWidth <= 768;
    
    if (isMobile) {
        // Добавляем обработчики для мобильных устройств
        
        // Улучшаем работу с выпадающими меню на мобильных
        const statusMenu = document.getElementById('statusMenu');
        const datePickerContainer = document.getElementById('datePickerContainer');
        const assigneeMenu = document.getElementById('assigneeMenu');
        
        // Добавляем класс для мобильной версии
        document.body.classList.add('mobile-view');
        
        // Убираем принудительное позиционирование меню
        if (statusMenu) {
            statusMenu.style.position = '';
        }
        
        if (datePickerContainer) {
            datePickerContainer.style.position = '';
        }
        
        if (assigneeMenu) {
            assigneeMenu.style.position = '';
        }
        
        // Добавляем обработчик изменения ориентации
        window.addEventListener('orientationchange', function() {
            setTimeout(function() {
                // Закрываем все открытые меню при изменении ориентации
                document.querySelectorAll('.status-menu, .date-picker-container, .assignee-menu').forEach(menu => {
                    menu.classList.add('d-none');
                });
            }, 100);
        });
        
        // Добавляем поддержку свайпов для закрытия меню
        let startY = 0;
        let startX = 0;
        
        document.addEventListener('touchstart', function(e) {
            startY = e.touches[0].clientY;
            startX = e.touches[0].clientX;
        });
        
        document.addEventListener('touchend', function(e) {
            const endY = e.changedTouches[0].clientY;
            const endX = e.changedTouches[0].clientX;
            const diffY = startY - endY;
            const diffX = startX - endX;
            
            // Если это свайп вниз или в сторону больше чем на 50px, закрываем меню
            if (Math.abs(diffY) > 50 || Math.abs(diffX) > 50) {
                document.querySelectorAll('.status-menu, .date-picker-container, .assignee-menu').forEach(menu => {
                    if (!menu.classList.contains('d-none')) {
                        menu.classList.add('d-none');
                    }
                });
            }
        });
        
        // Логирование для отладки
        console.log('Mobile adaptation initialized');
        console.log('Screen width:', window.innerWidth);
    }
    
    // Обработчик изменения размера окна
    window.addEventListener('resize', function() {
        const currentIsMobile = window.innerWidth <= 768;
        
        if (currentIsMobile && !document.body.classList.contains('mobile-view')) {
            document.body.classList.add('mobile-view');
            console.log('Switched to mobile view');
        } else if (!currentIsMobile && document.body.classList.contains('mobile-view')) {
            document.body.classList.remove('mobile-view');
            console.log('Switched to desktop view');
        }
    });
}

// Функция для улучшенного позиционирования мобильных меню
function positionMobileMenu(menu, trigger) {
    if (!menu || !trigger) return;
    
    const triggerRect = trigger.getBoundingClientRect();
    const menuRect = menu.getBoundingClientRect();
    const viewportWidth = window.innerWidth;
    const viewportHeight = window.innerHeight;
    
    // Сбрасываем позиционирование
    menu.style.position = 'fixed';
    menu.style.top = '';
    menu.style.left = '';
    menu.style.right = '';
    menu.style.bottom = '';
    menu.style.transform = '';
    
    // Вычисляем оптимальную позицию
    let top = triggerRect.bottom + 8;
    let left = triggerRect.left + (triggerRect.width / 2) - (menuRect.width / 2);
    
    // Проверяем, помещается ли меню внизу
    if (top + menuRect.height > viewportHeight - 20) {
        // Показываем меню сверху
        top = triggerRect.top - menuRect.height - 8;
    }
    
    // Проверяем границы по горизонтали
    if (left < 10) {
        left = 10;
    } else if (left + menuRect.width > viewportWidth - 10) {
        left = viewportWidth - menuRect.width - 10;
    }
    
    // Устанавливаем позицию
    menu.style.top = Math.max(10, top) + 'px';
    menu.style.left = left + 'px';
    menu.style.zIndex = '9999';
}

// Специальная функция позиционирования календаря (всегда сверху)
function positionDatePickerMobile(menu, trigger) {
    if (!menu || !trigger) return;
    
    const triggerRect = trigger.getBoundingClientRect();
    const menuRect = menu.getBoundingClientRect();
    const viewportWidth = window.innerWidth;
    const viewportHeight = window.innerHeight;
    
    // Сбрасываем позиционирование
    menu.style.position = 'fixed';
    menu.style.top = '';
    menu.style.left = '';
    menu.style.right = '';
    menu.style.bottom = '';
    menu.style.transform = '';
    
    // Приоритет: всегда показываем календарь сверху от триггера
    let top = triggerRect.top - menuRect.height - 15; // увеличиваем отступ
    let left = triggerRect.left + (triggerRect.width / 2) - (menuRect.width / 2);
    
    // Если календарь выходит за верхний край экрана, центрируем его
    if (top < 20) {
        top = Math.max(20, (viewportHeight - menuRect.height) / 2);
    }
    
    // Проверяем границы по горизонтали
    if (left < 10) {
        left = 10;
    } else if (left + menuRect.width > viewportWidth - 10) {
        left = viewportWidth - menuRect.width - 10;
    }
    
    // Убеждаемся, что календарь не выходит за нижний край
    if (top + menuRect.height > viewportHeight - 20) {
        top = viewportHeight - menuRect.height - 20;
    }
    
    // Устанавливаем позицию
    menu.style.top = Math.max(20, top) + 'px';
    menu.style.left = left + 'px';
    menu.style.zIndex = '9999';
}

// Функция инициализации заблокированного состояния
function initializeEditLock() {
    // Устанавливаем начальное заблокированное состояние
    const taskHeader = document.getElementById('taskHeader');
    const taskContent = document.getElementById('taskContent');
    
    if (taskHeader) {
        taskHeader.classList.add('content-locked');
    }
    
    if (taskContent) {
        taskContent.classList.add('content-locked');
    }
    
    // Блокируем все иконки кроме замка
    const toolbarIcons = document.querySelectorAll('.bottom-toolbar-content .toolbar-icon:not(.lock-icon)');
    toolbarIcons.forEach(icon => {
        icon.classList.add('disabled');
    });
    
    // Убеждаемся, что кнопка блокировки остается активной
    const editLockToggle = document.getElementById('editLockToggle');
    if (editLockToggle) {
        editLockToggle.classList.remove('disabled');
    }
    
    // Отключаем редактирование контента
    disableContentEditing();
    
    // Обеспечиваем работу интерактивных элементов в заблокированном режиме
    setTimeout(() => {
        enableInteractiveElementsInLockedMode();
    }, 100);
}

// Функции для работы с названием задачи
function editTaskTitle() {
    // Проверяем, не заблокировано ли редактирование
    if (isEditLocked) {
        showNotification('Разблокируйте редактирование для изменения названия', 'warning');
        return;
    }
    
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
    // Проверяем, не заблокировано ли редактирование
    if (isEditLocked) {
        showNotification('Разблокируйте редактирование для изменения приоритета', 'warning');
        return;
    }
    
    const statusMenu = document.getElementById('statusMenu');
    if (statusMenu) {
        // Закрываем другие открытые меню
        document.querySelectorAll('.date-picker-container, .assignee-menu').forEach(menu => {
            menu.classList.add('d-none');
        });
        
        statusMenu.classList.toggle('d-none');
        
        // Улучшенное позиционирование для мобильных устройств
        if (!statusMenu.classList.contains('d-none') && window.innerWidth <= 768) {
            positionMobileMenu(statusMenu, document.getElementById('statusSquare'));
        }
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
            updateAssigneeAvatar(priority);
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

function updateAssigneeAvatar(priority) {
    const assigneeAvatar = document.querySelector('.assignee-avatar-toolbar');
    if (!assigneeAvatar) return;

    // Удаляем все атрибуты приоритета
    assigneeAvatar.removeAttribute('data-priority');
    
    // Устанавливаем новый приоритет
    assigneeAvatar.setAttribute('data-priority', priority);
}

// Компактная функция изменения приоритета (не закрывает меню участников)
function changePriorityCompact(priority) {
    const taskHeaderFixed = document.querySelector('.task-header-fixed');
    
    if (!taskHeaderFixed) return;

    const taskId = taskHeaderFixed.dataset.taskId;
    const csrfToken = taskHeaderFixed.dataset.csrfToken;

    // Обновляем визуальное отображение активного кружка
    updatePriorityCircles(priority);

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
            // Обновляем данные приоритета
            const statusSquare = document.getElementById('statusSquare');
            if (statusSquare) {
                statusSquare.dataset.priority = priority;
            }
            updateAssigneeAvatar(priority);
            showNotification('Приоритет задачи обновлен', 'success');
        } else {
            showNotification('Ошибка при обновлении приоритета', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Ошибка при обновлении приоритета', 'error');
    });

    // НЕ закрываем меню участников!
}

function updatePriorityCircles(selectedPriority) {
    console.log('Обновление приоритета:', selectedPriority);
    
    // Убираем класс active у всех кружков приоритетов
    document.querySelectorAll('.priority-circle-option').forEach(circle => {
        circle.classList.remove('active');
    });
    
    // Добавляем класс active к выбранному кружку
    const selectedCircle = document.querySelector(`.priority-circle-option[data-priority="${selectedPriority}"]`);
    console.log('Найденный кружок:', selectedCircle);
    
    if (selectedCircle) {
        selectedCircle.classList.add('active');
        console.log('Добавлен класс active к кружку приоритета:', selectedPriority);
    } else {
        console.warn('Кружок с приоритетом не найден:', selectedPriority);
    }
}

// Функции для работы с датами
function toggleDatePicker() {
    // Проверяем, не заблокировано ли редактирование
    if (isEditLocked) {
        showNotification('Разблокируйте редактирование для изменения дат', 'warning');
        return;
    }
    
    const datePickerContainer = document.getElementById('datePickerContainer');
    if (datePickerContainer) {
        // Закрываем другие открытые меню
        document.querySelectorAll('.status-menu, .assignee-menu').forEach(menu => {
            menu.classList.add('d-none');
        });
        
        datePickerContainer.classList.toggle('d-none');
        
        // Применяем улучшенное позиционирование для всех устройств
        if (!datePickerContainer.classList.contains('d-none')) {
            positionDatePickerMobile(datePickerContainer, document.querySelector('.time-icon'));
        }
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
    // Проверяем, не заблокировано ли редактирование
    if (isEditLocked) {
        showNotification('Разблокируйте редактирование для изменения исполнителя', 'warning');
        return;
    }
    
    const assigneeMenu = document.getElementById('assigneeMenu');
    if (assigneeMenu) {
        // Закрываем другие открытые меню
        document.querySelectorAll('.status-menu, .date-picker-container').forEach(menu => {
            menu.classList.add('d-none');
        });
        
        assigneeMenu.classList.toggle('d-none');
        
        // Улучшенное позиционирование для мобильных устройств
        if (!assigneeMenu.classList.contains('d-none') && window.innerWidth <= 768) {
            positionMobileMenu(assigneeMenu, document.querySelector('.assignee-container'));
        }
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
window.handleDocumentUpload = handleDocumentUpload;
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
        
        // Очищаем inline стили
        block.style.removeProperty('display');
        block.style.removeProperty('pointer-events');
        block.classList.remove('active');
        
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
    
    // Инициализируем клики по изображениям для просмотра
    initializeImageClicks(canvas);
}

// Функция инициализации кликов по изображениям (работает всегда)
function initializeImageClicks(container) {
    const images = container.querySelectorAll('.block-image, .gallery-item img, .image-container img, .swiper-slide img, img[onclick*="showImageModal"]');
    images.forEach(img => {
        // Убираем существующие обработчики
        img.removeEventListener('click', handleImageClick);
        
        // Добавляем новый обработчик
        img.addEventListener('click', handleImageClick);
        
        // Убеждаемся, что изображения кликабельны даже в заблокированном режиме
        img.style.cursor = 'pointer';
        img.style.pointerEvents = 'auto';
    });
}

// Обработчик клика по изображению (работает всегда, независимо от блокировки)
function handleImageClick(event) {
    event.preventDefault();
    event.stopPropagation();
    
    const img = event.target;
    const fileName = img.alt || img.title || 'Изображение';
    const imageSrc = img.src;
    
    // Показываем модальное окно независимо от состояния блокировки
    showImageModal(imageSrc, fileName);
    
    console.log('Показано модальное окно изображения:', fileName);
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
    // Проверяем, не заблокировано ли редактирование
    if (isEditLocked) {
        showNotification('Разблокируйте редактирование для добавления текста', 'warning');
        return;
    }
    
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
    
    // setActiveBlock(textBlock); // Убираем автоматическое добавление в активное состояние
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
       
    `;
    
    return textBlock;
}

// Запуск загрузки изображения
function triggerImageUpload() {
    // Проверяем, не заблокировано ли редактирование
    if (isEditLocked) {
        showNotification('Разблокируйте редактирование для загрузки файлов', 'warning');
        return;
    }
    
    const imageUploader = document.getElementById('imageUploader');
    imageUploader.click();
}

// Запуск загрузки документа
function triggerDocumentUpload() {
    // Проверяем, не заблокировано ли редактирование
    if (isEditLocked) {
        showNotification('Разблокируйте редактирование для загрузки файлов', 'warning');
        return;
    }
    
    const documentUploader = document.getElementById('documentUploader');
    documentUploader.click();
}

// Обработка загрузки изображения
function handleImageUpload(event) {
    const files = Array.from(event.target.files);
    if (!files.length) return;
    
    // Проверяем размер каждого файла (максимум 50MB)
    for (let file of files) {
        if (file.size > 50 * 1024 * 1024) {
            showNotification(`Файл "${file.name}" слишком большой. Максимальный размер: 50MB`, 'error');
            return;
        }
    }
    
    // Показываем индикатор загрузки
    showNotification(`Загрузка ${files.length} файл(ов)...`, 'info');
    
    // Создаем FormData для отправки файлов
    const formData = new FormData();
    files.forEach((file, index) => {
        formData.append(`files[${index}]`, file);
    });
    formData.append('_token', document.querySelector('[data-csrf-token]').dataset.csrfToken);
    formData.append('type', 'image');
    formData.append('multiple', 'true');
    
    // Отправляем файлы на сервер
    fetch(`/tasks/${window.taskId}/upload-multiple`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('[data-csrf-token]').dataset.csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(`${files.length} файл(ов) успешно загружено`, 'success');
            
            if (files.length === 1) {
                // Одно изображение - создаем обычный блок
                if (data.block_html) {
                    addUploadedFileBlock(data.block_html);
                } else {
                    addImageBlock(data.files[0].url, data.files[0].name);
                }
            } else {
                // Несколько изображений - создаем swiper блок
                createSwiperBlock(data.files);
            }
            scheduleAutoSave();
        } else {
            showNotification(data.message || 'Ошибка при загрузке файлов', 'error');
        }
    })
    .catch(error => {
        console.error('Upload error:', error);
        showNotification('Ошибка при загрузке файлов', 'error');
    });
    
    // Очищаем input для возможности повторной загрузки того же файла
    event.target.value = '';
}

// Обработка загрузки документа
function handleDocumentUpload(event) {
    const files = Array.from(event.target.files);
    if (!files.length) return;
    
    // Проверяем размер каждого файла (максимум 50MB)
    for (let file of files) {
        if (file.size > 50 * 1024 * 1024) {
            showNotification(`Файл "${file.name}" слишком большой. Максимальный размер: 50MB`, 'error');
            return;
        }
    }
    
    // Показываем индикатор загрузки
    showNotification(`Загрузка ${files.length} документ(ов)...`, 'info');
    
    // Создаем FormData для отправки файлов
    const formData = new FormData();
    files.forEach((file, index) => {
        formData.append(`files[${index}]`, file);
    });
    formData.append('_token', document.querySelector('[data-csrf-token]').dataset.csrfToken);
    formData.append('type', 'document');
    formData.append('multiple', 'true');
    
    // Отправляем файлы на сервер
    fetch(`/tasks/${window.taskId}/upload-multiple`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('[data-csrf-token]').dataset.csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(`${files.length} документ(ов) успешно загружено`, 'success');
            
            if (files.length === 1) {
                // Один документ - создаем обычный блок
                if (data.block_html) {
                    addUploadedDocumentBlock(data.block_html);
                } else {
                    // Fallback для совместимости
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                }
            } else {
                // Несколько документов - создаем grid блок
                createFileGridBlock(data.files);
            }
            scheduleAutoSave();
        } else {
            showNotification(data.message || 'Ошибка при загрузке документов', 'error');
        }
    })
    .catch(error => {
        console.error('Upload error:', error);
        showNotification('Ошибка при загрузке документов', 'error');
    });
    
    // Очищаем input для возможности повторной загрузки того же файла
    event.target.value = '';
}

// Добавление загруженного файлового блока
function addUploadedFileBlock(blockHtml) {
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
    
    // Создаем временный элемент для парсинга HTML
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = blockHtml;
    const fileBlock = tempDiv.firstElementChild;
    
    pageContent.appendChild(fileBlock);
    
    // setActiveBlock(fileBlock); // Убираем автоматическое добавление в активное состояние
    scheduleAutoSave();
}

// Добавление загруженного документа блока
function addUploadedDocumentBlock(blockHtml) {
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
    
    // Создаем временный элемент для парсинга HTML
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = blockHtml;
    const documentBlock = tempDiv.firstElementChild;
    
    pageContent.appendChild(documentBlock);
    
    // setActiveBlock(documentBlock); // Убираем автоматическое добавление в активное состояние
    scheduleAutoSave();
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
    
    // setActiveBlock(imageBlock); // Убираем автоматическое добавление в активное состояние
    scheduleAutoSave();
}

// Создание блока изображения
function createImageBlock(imageSrc, fileName) {
    const blockId = 'block_' + Date.now();
    const imageBlock = document.createElement('div');
    imageBlock.className = 'content-block image-block';
    imageBlock.id = blockId;
    imageBlock.dataset.files = JSON.stringify([{url: imageSrc, name: fileName, type: 'image'}]);
    
    imageBlock.innerHTML = `
        <div class="block-toolbar">
            <button class="block-btn drag-handle" title="Перетащить блок" data-block-id="${blockId}">
                <i class="fas fa-grip-vertical"></i>
            </button>
            <button class="block-btn add-more" onclick="convertToSwiperAndAddMore('${blockId}')" title="Добавить еще изображения">
                <i class="fas fa-plus"></i>
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
        <img src="${imageSrc}" alt="${fileName}" class="block-image">
        <div class="image-caption" contenteditable="true" data-placeholder="Добавить подпись к изображению..."></div>
    `;
    
    // Добавляем обработчик клика по изображению
    const img = imageBlock.querySelector('.block-image');
    if (img) {
        img.addEventListener('click', function(event) {
            event.preventDefault();
            event.stopPropagation();
            showImageModal(imageSrc, fileName);
        });
        img.style.cursor = 'pointer';
    }
    
    return imageBlock;
}

// ===== ФУНКЦИИ ДЛЯ РАБОТЫ С МНОЖЕСТВЕННЫМИ ИЗОБРАЖЕНИЯМИ (SWIPER) =====

// Создание swiper блока для множественных изображений
function createSwiperBlock(files) {
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
    
    const blockId = 'swiper_' + Date.now();
    const swiperBlock = document.createElement('div');
    swiperBlock.className = 'content-block swiper-block';
    swiperBlock.id = blockId;
    swiperBlock.dataset.files = JSON.stringify(files);
    
    const slidesHtml = files.map((file, index) => `
        <div class="swiper-slide" data-index="${index}">
            ${(file.type === 'image' || file.type.startsWith('image/')) ? 
                `<img src="${file.url}" alt="${file.name}" onclick="showImageModal('${file.url}', '${file.name}')">
                 <button class="image-delete-btn" onclick="deleteImageFromSwiper('${blockId}', ${index})" title="Удалить изображение">
                     <i class="fas fa-times"></i>
                 </button>` :
                `<video src="${file.url}" controls></video>
                 <button class="image-delete-btn" onclick="deleteImageFromSwiper('${blockId}', ${index})" title="Удалить видео">
                     <i class="fas fa-times"></i>
                 </button>`
            }
        </div>
    `).join('');
    
    swiperBlock.innerHTML = `
        <div class="block-toolbar">
            <button class="block-btn drag-handle" title="Перетащить блок" data-block-id="${blockId}">
                <i class="fas fa-grip-vertical"></i>
            </button>
            <button class="block-btn add-more" onclick="addMoreImages('${blockId}')" title="Добавить еще изображения">
                <i class="fas fa-plus"></i>
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
        <div class="swiper-container" data-current-slide="0">
            <div class="swiper-wrapper" style="transform: translateX(0%);">
                ${slidesHtml}
            </div>
            ${files.length > 1 ? `
                <button class="swiper-navigation swiper-prev" onclick="swiperPrev('${blockId}')">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="swiper-navigation swiper-next" onclick="swiperNext('${blockId}')">
                    <i class="fas fa-chevron-right"></i>
                </button>
                <div class="swiper-pagination">
                    ${files.map((_, index) => `
                        <span class="swiper-pagination-bullet ${index === 0 ? 'active' : ''}" onclick="swiperGoTo('${blockId}', ${index})"></span>
                    `).join('')}
                </div>
                <div class="swiper-counter">1 / ${files.length}</div>
            ` : ''}
        </div>
        <div class="image-caption" contenteditable="true" data-placeholder="Добавить подпись к галерее..."></div>
    `;
    
    pageContent.appendChild(swiperBlock);
    // setActiveBlock(swiperBlock); // Убираем автоматическое добавление в активное состояние
    
    return swiperBlock;
}

// Навигация swiper (работает всегда, независимо от блокировки)
function swiperPrev(blockId) {
    const block = document.getElementById(blockId);
    const container = block.querySelector('.swiper-container');
    const wrapper = block.querySelector('.swiper-wrapper');
    const slides = block.querySelectorAll('.swiper-slide');
    const currentSlide = parseInt(container.dataset.currentSlide);
    
    if (currentSlide > 0) {
        const newSlide = currentSlide - 1;
        swiperGoTo(blockId, newSlide);
    }
}

function swiperNext(blockId) {
    const block = document.getElementById(blockId);
    const container = block.querySelector('.swiper-container');
    const slides = block.querySelectorAll('.swiper-slide');
    const currentSlide = parseInt(container.dataset.currentSlide);
    
    if (currentSlide < slides.length - 1) {
        const newSlide = currentSlide + 1;
        swiperGoTo(blockId, newSlide);
    }
}

function swiperGoTo(blockId, slideIndex) {
    const block = document.getElementById(blockId);
    const container = block.querySelector('.swiper-container');
    const wrapper = block.querySelector('.swiper-wrapper');
    const slides = block.querySelectorAll('.swiper-slide');
    const bullets = block.querySelectorAll('.swiper-pagination-bullet');
    const counter = block.querySelector('.swiper-counter');
    
    if (slideIndex < 0 || slideIndex >= slides.length) return;
    
    // Обновляем transform
    const translateX = -slideIndex * 100;
    wrapper.style.transform = `translateX(${translateX}%)`;
    
    // Обновляем активные элементы
    bullets.forEach((bullet, index) => {
        bullet.classList.toggle('active', index === slideIndex);
    });
    
    // Обновляем счетчик
    if (counter) {
        counter.textContent = `${slideIndex + 1} / ${slides.length}`;
    }
    
    // Сохраняем текущий слайд
    container.dataset.currentSlide = slideIndex;
}

// ===== ФУНКЦИИ ДЛЯ РАБОТЫ С МНОЖЕСТВЕННЫМИ ДОКУМЕНТАМИ (GRID) =====

// Создание grid блока для множественных документов
function createFileGridBlock(files) {
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
    
    const blockId = 'file_grid_' + Date.now();
    const fileGridBlock = document.createElement('div');
    fileGridBlock.className = 'content-block file-grid-block';
    fileGridBlock.id = blockId;
    fileGridBlock.dataset.files = JSON.stringify(files);
    
    const filesHtml = files.map((file, index) => {
        const extension = file.name.split('.').pop().toLowerCase();
        const iconClass = getFileIconClass(extension);
        const fileSize = formatFileSize(file.size);
        
        return `
            <div class="file-grid-item new" data-index="${index}">
                <div class="file-grid-icon ${iconClass}">
                    <i class="fas ${getFileIcon(extension)}"></i>
                </div>
                <div class="file-grid-name" title="${file.name}">${file.name}</div>
                <div class="file-grid-size">${fileSize}</div>
                <div class="file-grid-actions">
                    <a href="${file.url}" download="${file.name}" class="file-grid-btn download" title="Скачать">
                        <i class="fas fa-download"></i>
                    </a>
                    <button class="file-grid-btn delete" onclick="deleteDocumentFromGrid('${blockId}', ${index})" title="Удалить">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
    }).join('');
    
    fileGridBlock.innerHTML = `
        <div class="block-toolbar">
            <button class="block-btn drag-handle" title="Перетащить блок" data-block-id="${blockId}">
                <i class="fas fa-grip-vertical"></i>
            </button>
            <button class="block-btn add-more" onclick="addMoreDocuments('${blockId}')" title="Добавить еще документы">
                <i class="fas fa-plus"></i>
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
        <div class="file-grid-header">
            <i class="fas fa-folder"></i>
            <h5 class="file-grid-title">Документы</h5>
            <span class="file-grid-count">${files.length}</span>
        </div>
        <div class="file-grid">
            ${filesHtml}
        </div>
    `;
    
    pageContent.appendChild(fileGridBlock);
    // setActiveBlock(fileGridBlock); // Убираем автоматическое добавление в активное состояние
    
    // Добавляем анимацию появления блока
    setTimeout(() => {
        fileGridBlock.classList.add('block-appear');
    }, 100);
    
    return fileGridBlock;
}

// Вспомогательные функции для определения типа файла
function getFileIconClass(extension) {
    const iconMap = {
        'pdf': 'pdf',
        'doc': 'doc', 'docx': 'doc',
        'xls': 'xls', 'xlsx': 'xls',
        'ppt': 'ppt', 'pptx': 'ppt',
        'zip': 'zip', 'rar': 'zip',
        'txt': 'txt'
    };
    return iconMap[extension] || 'default';
}

function getFileIcon(extension) {
    const iconMap = {
        'pdf': 'fa-file-pdf',
        'doc': 'fa-file-word', 'docx': 'fa-file-word',
        'xls': 'fa-file-excel', 'xlsx': 'fa-file-excel',
        'ppt': 'fa-file-powerpoint', 'pptx': 'fa-file-powerpoint',
        'zip': 'fa-file-archive', 'rar': 'fa-file-archive',
        'txt': 'fa-file-text'
    };
    return iconMap[extension] || 'fa-file';
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// ===== ФУНКЦИИ ДЛЯ ДОБАВЛЕНИЯ ФАЙЛОВ В СУЩЕСТВУЮЩИЕ БЛОКИ =====

// Добавление изображений в существующий swiper блок
function addMoreImages(blockId) {
    if (isEditLocked) {
        showNotification('Разблокируйте редактирование для загрузки файлов', 'warning');
        return;
    }
    
    currentActiveBlockForUpload = blockId;
    const additionalImageUploader = document.getElementById('additionalImageUploader');
    additionalImageUploader.click();
}

// Добавление документов в существующий grid блок
function addMoreDocuments(blockId) {
    if (isEditLocked) {
        showNotification('Разблокируйте редактирование для загрузки файлов', 'warning');
        return;
    }
    
    currentActiveBlockForUpload = blockId;
    const additionalDocumentUploader = document.getElementById('additionalDocumentUploader');
    additionalDocumentUploader.click();
}

// Обработка добавления изображений в существующий блок
function handleAdditionalImageUpload(event) {
    const files = Array.from(event.target.files);
    if (!files.length || !currentActiveBlockForUpload) return;
    
    // Проверяем размер файлов
    for (let file of files) {
        if (file.size > 50 * 1024 * 1024) {
            showNotification(`Файл "${file.name}" слишком большой. Максимальный размер: 50MB`, 'error');
            return;
        }
    }
    
    showNotification(`Добавление ${files.length} изображений...`, 'info');
    
    const formData = new FormData();
    files.forEach((file, index) => {
        formData.append(`files[${index}]`, file);
    });
    formData.append('_token', document.querySelector('[data-csrf-token]').dataset.csrfToken);
    formData.append('type', 'image');
    formData.append('block_id', currentActiveBlockForUpload);
    
    fetch(`/tasks/${window.taskId}/add-to-block`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('[data-csrf-token]').dataset.csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Изображения добавлены', 'success');
            
            const block = document.getElementById(currentActiveBlockForUpload);
            if (block) {
                // Проверяем, является ли блок обычным image-block
                if (block.classList.contains('image-block') && !block.classList.contains('swiper-block')) {
                    // Конвертируем в swiper блок
                    convertImageBlockToSwiper(currentActiveBlockForUpload, data.files);
                } else {
                    // Обновляем существующий swiper блок
                    updateSwiperBlock(currentActiveBlockForUpload, data.files);
                }
            }
            scheduleAutoSave();
        } else {
            showNotification(data.message || 'Ошибка при добавлении изображений', 'error');
        }
    })
    .catch(error => {
        console.error('Upload error:', error);
        showNotification('Ошибка при добавлении изображений', 'error');
    });
    
    event.target.value = '';
    currentActiveBlockForUpload = null;
}

// Обработка добавления документов в существующий блок
function handleAdditionalDocumentUpload(event) {
    const files = Array.from(event.target.files);
    if (!files.length || !currentActiveBlockForUpload) return;
    
    // Проверяем размер файлов
    for (let file of files) {
        if (file.size > 50 * 1024 * 1024) {
            showNotification(`Файл "${file.name}" слишком большой. Максимальный размер: 50MB`, 'error');
            return;
        }
    }
    
    showNotification(`Добавление ${files.length} документов...`, 'info');
    
    const formData = new FormData();
    files.forEach((file, index) => {
        formData.append(`files[${index}]`, file);
    });
    formData.append('_token', document.querySelector('[data-csrf-token]').dataset.csrfToken);
    formData.append('type', 'document');
    formData.append('block_id', currentActiveBlockForUpload);
    
    fetch(`/tasks/${window.taskId}/add-to-block`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('[data-csrf-token]').dataset.csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Документы добавлены', 'success');
            updateFileGridBlock(currentActiveBlockForUpload, data.files);
            scheduleAutoSave();
        } else {
            showNotification(data.message || 'Ошибка при добавлении документов', 'error');
        }
    })
    .catch(error => {
        console.error('Upload error:', error);
        showNotification('Ошибка при добавлении документов', 'error');
    });
    
    event.target.value = '';
    currentActiveBlockForUpload = null;
}

// Обновление swiper блока с новыми изображениями
function updateSwiperBlock(blockId, newFiles) {
    const block = document.getElementById(blockId);
    if (!block) return;
    
    const currentFiles = JSON.parse(block.dataset.files || '[]');
    const allFiles = [...currentFiles, ...newFiles];
    block.dataset.files = JSON.stringify(allFiles);
    
    const wrapper = block.querySelector('.swiper-wrapper');
    const pagination = block.querySelector('.swiper-pagination');
    const counter = block.querySelector('.swiper-counter');
    
    // Добавляем новые слайды
    newFiles.forEach((file, index) => {
        const slideIndex = currentFiles.length + index;
        const slide = document.createElement('div');
        slide.className = 'swiper-slide';
        slide.dataset.index = slideIndex;
        slide.innerHTML = (file.type === 'image' || file.type.startsWith('image/')) ? 
            `<img src="${file.url}" alt="${file.name}" onclick="showImageModal('${file.url}', '${file.name}')">
             <button class="image-delete-btn" onclick="deleteImageFromSwiper('${blockId}', ${slideIndex})" title="Удалить изображение">
                 <i class="fas fa-times"></i>
             </button>` :
            `<video src="${file.url}" controls></video>
             <button class="image-delete-btn" onclick="deleteImageFromSwiper('${blockId}', ${slideIndex})" title="Удалить видео">
                 <i class="fas fa-times"></i>
             </button>`;
        wrapper.appendChild(slide);
        
        // Добавляем пагинацию
        if (pagination) {
            const bullet = document.createElement('span');
            bullet.className = 'swiper-pagination-bullet';
            bullet.onclick = () => swiperGoTo(blockId, slideIndex);
            pagination.appendChild(bullet);
        }
    });
    
    // Обновляем счетчик
    if (counter) {
        const currentSlide = parseInt(block.querySelector('.swiper-container').dataset.currentSlide) + 1;
        counter.textContent = `${currentSlide} / ${allFiles.length}`;
    }
    
    // Добавляем навигацию если она не была добавлена ранее
    if (currentFiles.length === 1 && allFiles.length > 1) {
        addSwiperNavigation(block, blockId, allFiles.length);
    }
}

// Обновление file grid блока с новыми документами
function updateFileGridBlock(blockId, newFiles) {
    const block = document.getElementById(blockId);
    if (!block) return;
    
    const currentFiles = JSON.parse(block.dataset.files || '[]');
    const allFiles = [...currentFiles, ...newFiles];
    block.dataset.files = JSON.stringify(allFiles);
    
    const grid = block.querySelector('.file-grid');
    const countElement = block.querySelector('.file-grid-count');
    
    // Добавляем новые файлы
    newFiles.forEach((file, index) => {
        const fileIndex = currentFiles.length + index;
        const extension = file.name.split('.').pop().toLowerCase();
        const iconClass = getFileIconClass(extension);
        const fileSize = formatFileSize(file.size);
        
        const fileItem = document.createElement('div');
        fileItem.className = 'file-grid-item';
        fileItem.dataset.index = fileIndex;
        fileItem.innerHTML = `
            <div class="file-grid-icon ${iconClass}">
                <i class="fas ${getFileIcon(extension)}"></i>
            </div>
            <div class="file-grid-name">${file.name}</div>
            <div class="file-grid-size">${fileSize}</div>
            <div class="file-grid-actions">
                <a href="${file.url}" download="${file.name}" class="file-grid-btn download" title="Скачать">
                    <i class="fas fa-download"></i>
                </a>
                <button class="file-grid-btn delete" onclick="deleteDocumentFromGrid('${blockId}', ${fileIndex})" title="Удалить">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        grid.appendChild(fileItem);
    });
    
    // Обновляем счетчик
    if (countElement) {
        countElement.textContent = allFiles.length;
    }
}

// Добавление навигации к swiper блоку
function addSwiperNavigation(block, blockId, totalFiles) {
    const container = block.querySelector('.swiper-container');
    
    const navHTML = `
        <button class="swiper-navigation swiper-prev" onclick="swiperPrev('${blockId}')">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="swiper-navigation swiper-next" onclick="swiperNext('${blockId}')">
            <i class="fas fa-chevron-right"></i>
        </button>
        <div class="swiper-pagination">
            ${Array.from({length: totalFiles}, (_, index) => `
                <span class="swiper-pagination-bullet ${index === 0 ? 'active' : ''}" onclick="swiperGoTo('${blockId}', ${index})"></span>
            `).join('')}
        </div>
        <div class="swiper-counter">1 / ${totalFiles}</div>
    `;
    
    container.insertAdjacentHTML('beforeend', navHTML);
}

// ===== ФУНКЦИИ УДАЛЕНИЯ ОТДЕЛЬНЫХ ФАЙЛОВ ИЗ БЛОКОВ =====

// Удаление изображения из swiper блока
function deleteImageFromSwiper(blockId, imageIndex) {
    const block = document.getElementById(blockId);
    if (!block) return;
    
    const files = JSON.parse(block.dataset.files || '[]');
    if (imageIndex < 0 || imageIndex >= files.length) return;
    
    if (confirm('Удалить это изображение?')) {
        files.splice(imageIndex, 1);
        block.dataset.files = JSON.stringify(files);
        
        if (files.length === 0) {
            // Удаляем весь блок если файлов не осталось
            deleteBlock(blockId);
        } else {
            // Перестраиваем swiper
            rebuildSwiperBlock(blockId, files);
            scheduleAutoSave();
        }
    }
}

// Удаление документа из grid блока
function deleteDocumentFromGrid(blockId, fileIndex) {
    const block = document.getElementById(blockId);
    if (!block) return;
    
    const files = JSON.parse(block.dataset.files || '[]');
    if (fileIndex < 0 || fileIndex >= files.length) return;
    
    const fileItem = block.querySelector(`[data-index="${fileIndex}"]`);
    const fileName = files[fileIndex].name;
    
    if (confirm(`Удалить документ "${fileName}"?`)) {
        // Добавляем класс для анимации удаления
        if (fileItem) {
            fileItem.classList.add('removing');
            
            // Ждем окончания анимации перед удалением
            setTimeout(() => {
                files.splice(fileIndex, 1);
                block.dataset.files = JSON.stringify(files);
                
                if (files.length === 0) {
                    // Удаляем весь блок если файлов не осталось
                    deleteBlock(blockId);
                } else {
                    // Перестраиваем grid
                    rebuildFileGridBlock(blockId, files);
                    scheduleAutoSave();
                    showNotification('Документ удален', 'success');
                }
            }, 300);
        } else {
            // Fallback без анимации
            files.splice(fileIndex, 1);
            block.dataset.files = JSON.stringify(files);
            
            if (files.length === 0) {
                deleteBlock(blockId);
            } else {
                rebuildFileGridBlock(blockId, files);
                scheduleAutoSave();
            }
        }
    }
}

// Перестройка swiper блока после удаления
function rebuildSwiperBlock(blockId, files) {
    const block = document.getElementById(blockId);
    const container = block.querySelector('.swiper-container');
    
    // Удаляем старые элементы
    const wrapper = container.querySelector('.swiper-wrapper');
    const navigation = container.querySelectorAll('.swiper-navigation');
    const pagination = container.querySelector('.swiper-pagination');
    const counter = container.querySelector('.swiper-counter');
    
    navigation.forEach(nav => nav.remove());
    if (pagination) pagination.remove();
    if (counter) counter.remove();
    
    // Перестраиваем слайды
    const slidesHtml = files.map((file, index) => `
        <div class="swiper-slide" data-index="${index}">
            ${(file.type === 'image' || file.type.startsWith('image/')) ? 
                `<img src="${file.url}" alt="${file.name}" onclick="showImageModal('${file.url}', '${file.name}')">
                 <button class="image-delete-btn" onclick="deleteImageFromSwiper('${blockId}', ${index})" title="Удалить изображение">
                     <i class="fas fa-times"></i>
                 </button>` :
                `<video src="${file.url}" controls></video>
                 <button class="image-delete-btn" onclick="deleteImageFromSwiper('${blockId}', ${index})" title="Удалить видео">
                     <i class="fas fa-times"></i>
                 </button>`
            }
        </div>
    `).join('');
    
    wrapper.innerHTML = slidesHtml;
    wrapper.style.transform = 'translateX(0%)';
    container.dataset.currentSlide = '0';
    
    // Добавляем навигацию если файлов больше одного
    if (files.length > 1) {
        addSwiperNavigation(block, blockId, files.length);
    }
}

// Перестройка file grid блока после удаления
function rebuildFileGridBlock(blockId, files) {
    const block = document.getElementById(blockId);
    const grid = block.querySelector('.file-grid');
    const countElement = block.querySelector('.file-grid-count');
    
    // Перестраиваем файлы с анимацией
    const filesHtml = files.map((file, index) => {
        const extension = file.name.split('.').pop().toLowerCase();
        const iconClass = getFileIconClass(extension);
        const fileSize = formatFileSize(file.size);
        
        return `
            <div class="file-grid-item fadeInUp" data-index="${index}">
                <div class="file-grid-icon ${iconClass}">
                    <i class="fas ${getFileIcon(extension)}"></i>
                </div>
                <div class="file-grid-name" title="${file.name}">${file.name}</div>
                <div class="file-grid-size">${fileSize}</div>
                <div class="file-grid-actions">
                    <a href="${file.url}" download="${file.name}" class="file-grid-btn download" title="Скачать">
                        <i class="fas fa-download"></i>
                    </a>
                    <button class="file-grid-btn delete" onclick="deleteDocumentFromGrid('${blockId}', ${index})" title="Удалить">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
    }).join('');
    
    grid.innerHTML = filesHtml;
    
    // Обновляем счетчик с анимацией
    if (countElement) {
        countElement.style.transform = 'scale(1.2)';
        countElement.textContent = files.length;
        setTimeout(() => {
            countElement.style.transform = 'scale(1)';
        }, 200);
    }
    
    // Применяем анимацию к новым элементам
    setTimeout(() => {
        const newItems = grid.querySelectorAll('.file-grid-item');
        newItems.forEach((item, index) => {
            setTimeout(() => {
                item.classList.remove('fadeInUp');
            }, index * 50);
        });
    }, 100);
}

// Добавление нового листа
function addPageBreak() {
    // Проверяем, не заблокировано ли редактирование
    if (isEditLocked) {
        showNotification('Разблокируйте редактирование для добавления листов', 'warning');
        return;
    }
    
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

// Показ модального окна с изображением (работает всегда, независимо от блокировки)
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
    // Проверяем, не заблокировано ли редактирование
    if (isEditLocked) {
        showNotification('В режиме просмотра сохранение не требуется', 'info');
        return;
    }
    
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
        // Специальная обработка для цвета текста
        if (command === 'foreColor') {
            applyTextColor(value);
            return;
        }
        
        // Специальная обработка для очистки форматирования
        if (command === 'removeFormat') {
            removeTextFormatting();
            return;
        }
        
        // Выполняем команду форматирования
        const success = document.execCommand(command, false, value);
        
        if (!success) {
            console.warn(`Команда ${command} не выполнена, пробуем альтернативный метод`);
            
            // Альтернативные методы для некоторых команд
            switch (command) {
                case 'bold':
                    applyStyleToSelection('font-weight', 'bold');
                    break;
                case 'italic':
                    applyStyleToSelection('font-style', 'italic');
                    break;
                case 'underline':
                    applyStyleToSelection('text-decoration', 'underline');
                    break;
                case 'strikeThrough':
                    applyStyleToSelection('text-decoration', 'line-through');
                    break;
                default:
                    console.warn(`Альтернативный метод для ${command} не реализован`);
            }
        }
        
        // Планируем автосохранение
        scheduleAutoSave();
        
        console.log(`Применено форматирование: ${command}${value ? ' = ' + value : ''}`);
    } catch (error) {
        console.error('Ошибка форматирования:', error);
    }
}

// Функция для применения цвета текста
function applyTextColor(color) {
    const selection = window.getSelection();
    if (!selection.rangeCount || selection.isCollapsed) {
        console.log('Нет выделения для применения цвета');
        return;
    }
    
    const range = selection.getRangeAt(0);
    const selectedText = range.toString();
    
    console.log('Применяем цвет:', color, 'к тексту:', selectedText);
    
    // Создаем span с нужным цветом
    const span = document.createElement('span');
    span.style.color = color;
    span.style.cssText = `color: ${color} !important;`;
    
    try {
        // Оборачиваем выделенный текст в span
        range.surroundContents(span);
        
        // Очищаем выделение
        selection.removeAllRanges();
        
        console.log(`Цвет текста ${color} успешно применен`);
        scheduleAutoSave();
    } catch (error) {
        console.log('surroundContents failed, trying alternative method:', error.message);
        // Если surroundContents не работает (например, выделен частично узел),
        // извлекаем содержимое и создаем новый span
        try {
            const contents = range.extractContents();
            span.appendChild(contents);
            range.insertNode(span);
            
            selection.removeAllRanges();
            console.log(`Применен цвет текста (альтернативный метод): ${color}`);
            scheduleAutoSave();
        } catch (error2) {
            console.error('Альтернативный метод тоже не сработал:', error2);
            // Fallback к стандартному методу
            console.log('Пробуем fallback к execCommand');
            try {
                document.execCommand('styleWithCSS', false, true);
                document.execCommand('foreColor', false, color);
                console.log(`Применен цвет через execCommand: ${color}`);
                scheduleAutoSave();
            } catch (error3) {
                console.error('Все методы применения цвета провалились:', error3);
            }
        }
    }
}

// Функция для применения стилей к выделенному тексту
function applyStyleToSelection(property, value) {
    const selection = window.getSelection();
    if (!selection.rangeCount || selection.isCollapsed) return;
    
    const range = selection.getRangeAt(0);
    
    // Создаем span с нужным стилем
    const span = document.createElement('span');
    span.style[property] = value;
    
    try {
        range.surroundContents(span);
        selection.removeAllRanges();
        console.log(`Применен стиль: ${property} = ${value}`);
        scheduleAutoSave();
    } catch (error) {
        try {
            const contents = range.extractContents();
            span.appendChild(contents);
            range.insertNode(span);
            selection.removeAllRanges();
            console.log(`Применен стиль (альтернативный метод): ${property} = ${value}`);
            scheduleAutoSave();
        } catch (error2) {
            console.error('Ошибка применения стиля:', error2);
        }
    }
}

// Функция для удаления форматирования
function removeTextFormatting() {
    const selection = window.getSelection();
    if (!selection.rangeCount || selection.isCollapsed) return;
    
    const range = selection.getRangeAt(0);
    
    try {
        // Сначала пробуем стандартный метод
        const success = document.execCommand('removeFormat', false);
        
        if (!success) {
            // Альтернативный метод: извлекаем только текстовое содержимое
            const textContent = range.toString();
            const textNode = document.createTextNode(textContent);
            
            range.deleteContents();
            range.insertNode(textNode);
            
            selection.removeAllRanges();
            console.log('Форматирование удалено (альтернативный метод)');
        } else {
            console.log('Форматирование удалено (стандартный метод)');
        }
        
        scheduleAutoSave();
    } catch (error) {
        console.error('Ошибка удаления форматирования:', error);
    }
}

// Показать/скрыть панель форматирования
function toggleFormatMenu() {
    // Проверяем, не заблокировано ли редактирование
    if (isEditLocked) {
        showNotification('Разблокируйте редактирование для форматирования текста', 'warning');
        return;
    }
    
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
            <div class="format-group">
                <button class="format-btn format-dropdown-btn" data-group="text" title="Стили текста">
                    <i class="fas fa-font"></i>
                    <i class="fas fa-chevron-down format-arrow"></i>
                </button>
                <div class="format-dropdown" data-group="text">
                    <button class="format-btn" onclick="formatText('bold')" title="Жирный (Ctrl+B)">
                        <i class="fas fa-bold"></i> Жирный
                    </button>
                    <button class="format-btn" onclick="formatText('italic')" title="Курсив (Ctrl+I)">
                        <i class="fas fa-italic"></i> Курсив
                    </button>
                    <button class="format-btn" onclick="formatText('underline')" title="Подчеркивание (Ctrl+U)">
                        <i class="fas fa-underline"></i> Подчеркивание
                    </button>
                    <button class="format-btn" onclick="formatText('strikeThrough')" title="Зачеркивание">
                        <i class="fas fa-strikethrough"></i> Зачеркивание
                    </button>
                </div>
            </div>
            
            <div class="format-group">
                <button class="format-btn format-dropdown-btn" data-group="align" title="Выравнивание">
                    <i class="fas fa-align-left"></i>
                    <i class="fas fa-chevron-down format-arrow"></i>
                </button>
                <div class="format-dropdown" data-group="align">
                    <button class="format-btn" onclick="formatText('justifyLeft')" title="По левому краю">
                        <i class="fas fa-align-left"></i> По левому краю
                    </button>
                    <button class="format-btn" onclick="formatText('justifyCenter')" title="По центру">
                        <i class="fas fa-align-center"></i> По центру
                    </button>
                    <button class="format-btn" onclick="formatText('justifyRight')" title="По правому краю">
                        <i class="fas fa-align-right"></i> По правому краю
                    </button>
                    <button class="format-btn" onclick="formatText('justifyFull')" title="По ширине">
                        <i class="fas fa-align-justify"></i> По ширине
                    </button>
                </div>
            </div>
            
            <div class="format-group">
                <button class="format-btn format-dropdown-btn" data-group="size" title="Размер шрифта">
                    <i class="fas fa-text-height"></i>
                    <i class="fas fa-chevron-down format-arrow"></i>
                </button>
                <div class="format-dropdown" data-group="size">
                    <button class="format-btn" onclick="formatText('fontSize', '1')" title="Очень малый">
                        <span style="font-size: 10px;">Очень малый</span>
                    </button>
                    <button class="format-btn" onclick="formatText('fontSize', '2')" title="Малый">
                        <span style="font-size: 12px;">Малый</span>
                    </button>
                    <button class="format-btn" onclick="formatText('fontSize', '3')" title="Обычный">
                        <span style="font-size: 14px;">Обычный</span>
                    </button>
                    <button class="format-btn" onclick="formatText('fontSize', '4')" title="Средний">
                        <span style="font-size: 16px;">Средний</span>
                    </button>
                    <button class="format-btn" onclick="formatText('fontSize', '5')" title="Большой">
                        <span style="font-size: 18px;">Большой</span>
                    </button>
                    <button class="format-btn" onclick="formatText('fontSize', '6')" title="Очень большой">
                        <span style="font-size: 20px;">Очень большой</span>
                    </button>
                </div>
            </div>
            
            <div class="format-group">
                <button class="format-btn format-dropdown-btn" data-group="color" title="Цвет текста">
                    <i class="fas fa-palette"></i>
                    <i class="fas fa-chevron-down format-arrow"></i>
                </button>
                <div class="format-dropdown format-color-dropdown" data-group="color">
                    <button class="format-btn color-btn" onclick="formatText('foreColor', '#000000')" title="Черный">
                        <span class="color-dot" style="background: #000000;"></span> Черный
                    </button>
                    <button class="format-btn color-btn" onclick="formatText('foreColor', '#ff0000')" title="Красный">
                        <span class="color-dot" style="background: #ff0000;"></span> Красный
                    </button>
                    <button class="format-btn color-btn" onclick="formatText('foreColor', '#00aa00')" title="Зеленый">
                        <span class="color-dot" style="background: #00aa00;"></span> Зеленый
                    </button>
                    <button class="format-btn color-btn" onclick="formatText('foreColor', '#0000ff')" title="Синий">
                        <span class="color-dot" style="background: #0000ff;"></span> Синий
                    </button>
                    <button class="format-btn color-btn" onclick="formatText('foreColor', '#ff8800')" title="Оранжевый">
                        <span class="color-dot" style="background: #ff8800;"></span> Оранжевый
                    </button>
                    <button class="format-btn color-btn" onclick="formatText('foreColor', '#aa00aa')" title="Фиолетовый">
                        <span class="color-dot" style="background: #aa00aa;"></span> Фиолетовый
                    </button>
                </div>
            </div>
            
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
                <i class="fas fa-eraser"></i>
            </button>
        </div>
    `;
    
    // Позиционируем панель
    formatToolbar.style.cssText = `
        position: fixed;
        top: ${Math.max(10, rect.top - 60)}px;
        left: ${Math.min(rect.left, window.innerWidth - 300)}px;
        z-index: 9999;
        background: white;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        padding: 4px;
        white-space: nowrap;
        min-width: 280px;
        max-width: 350px;
    `;
    
    document.body.appendChild(formatToolbar);
    
    // Инициализируем обработчики для выпадающих меню
    initFormatDropdowns();
    
    // Автоматически скрываем панель через некоторое время или при клике вне её
    setTimeout(() => {
        document.addEventListener('click', hideFormatToolbarOnClick);
        document.addEventListener('selectionchange', checkSelection);
        document.addEventListener('keydown', handleFormatToolbarKeydown);
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
    document.removeEventListener('keydown', handleFormatToolbarKeydown);
}

// Инициализация выпадающих меню панели форматирования
function initFormatDropdowns() {
    const dropdownBtns = formatToolbar.querySelectorAll('.format-dropdown-btn');
    
    dropdownBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const group = this.dataset.group;
            const dropdown = formatToolbar.querySelector(`.format-dropdown[data-group="${group}"]`);
            
            // Закрываем все остальные выпадающие меню
            const allDropdowns = formatToolbar.querySelectorAll('.format-dropdown');
            allDropdowns.forEach(dd => {
                if (dd !== dropdown) {
                    dd.classList.remove('show');
                }
            });
            
            // Переключаем текущее меню
            dropdown.classList.toggle('show');
            
            // Обновляем стрелку
            const arrow = this.querySelector('.format-arrow');
            if (dropdown.classList.contains('show')) {
                arrow.style.transform = 'rotate(180deg)';
            } else {
                arrow.style.transform = 'rotate(0deg)';
            }
        });
        
        // Добавляем hover эффект
        btn.addEventListener('mouseenter', function() {
            this.style.background = '#f0f0f0';
        });
        
        btn.addEventListener('mouseleave', function() {
            this.style.background = '';
        });
    });
    
    // Закрытие выпадающих меню при клике на опцию
    const dropdownOptions = formatToolbar.querySelectorAll('.format-dropdown .format-btn');
    dropdownOptions.forEach(option => {
        option.addEventListener('click', function() {
            // Закрываем все выпадающие меню после выбора опции
            const allDropdowns = formatToolbar.querySelectorAll('.format-dropdown');
            allDropdowns.forEach(dd => {
                dd.classList.remove('show');
            });
            
            // Сбрасываем стрелки
            const allArrows = formatToolbar.querySelectorAll('.format-arrow');
            allArrows.forEach(arrow => {
                arrow.style.transform = 'rotate(0deg)';
            });
        });
    });
}

// Скрыть панель при клике вне её
function hideFormatToolbarOnClick(event) {
    if (formatToolbar) {
        const isClickInside = formatToolbar.contains(event.target);
        const isDropdownClick = event.target.closest('.format-dropdown-btn');
        
        if (!isClickInside) {
            hideFormatToolbar();
        } else if (!isDropdownClick) {
            // Если клик внутри панели, но не по кнопке выпадающего меню, закрываем все выпадающие меню
            const allDropdowns = formatToolbar.querySelectorAll('.format-dropdown');
            allDropdowns.forEach(dd => {
                dd.classList.remove('show');
            });
            
            // Сбрасываем стрелки
            const allArrows = formatToolbar.querySelectorAll('.format-arrow');
            allArrows.forEach(arrow => {
                arrow.style.transform = 'rotate(0deg)';
            });
        }
    }
}

// Проверить выделение текста
function checkSelection() {
    const selection = window.getSelection();
    if (!selection.rangeCount || selection.isCollapsed) {
        hideFormatToolbar();
    }
}

// Обработка клавиш для панели форматирования
function handleFormatToolbarKeydown(event) {
    if (event.key === 'Escape' && formatToolbar) {
        // Закрываем все выпадающие меню при нажатии Escape
        const allDropdowns = formatToolbar.querySelectorAll('.format-dropdown');
        const hasOpenDropdown = Array.from(allDropdowns).some(dd => dd.classList.contains('show'));
        
        if (hasOpenDropdown) {
            allDropdowns.forEach(dd => {
                dd.classList.remove('show');
            });
            
            // Сбрасываем стрелки
            const allArrows = formatToolbar.querySelectorAll('.format-arrow');
            allArrows.forEach(arrow => {
                arrow.style.transform = 'rotate(0deg)';
            });
            
            event.preventDefault();
            event.stopPropagation();
        } else {
            // Если нет открытых выпадающих меню, закрываем всю панель
            hideFormatToolbar();
        }
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
        
        // Если элемент находится в редактируемом контенте, применяем форматирование
        if (textContent && (event.ctrlKey || event.metaKey)) {
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
        
        // Глобальные горячие клавиши (обрабатываются системой KeyboardShortcuts)
        // Здесь мы оставляем только специфичные для форматирования текста
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

// Функция переключения режима блокировки/разблокировки
function toggleEditMode() {
    const taskHeader = document.getElementById('taskHeader');
    const taskContent = document.getElementById('taskContent');
    const lockIcon = document.getElementById('lockIcon');
    const editLockToggle = document.getElementById('editLockToggle');
    const toolbarIcons = document.querySelectorAll('.bottom-toolbar-content .toolbar-icon:not(.lock-icon)');
    
    // Отладочная информация
    console.log('toggleEditMode вызван, isEditLocked:', isEditLocked);
    console.log('editLockToggle элемент:', editLockToggle);
    console.log('Количество toolbar иконок для блокировки:', toolbarIcons.length);
    
    if (isEditLocked) {
        // Разблокировать редактирование
        isEditLocked = false;
        
        // Убираем класс заблокированного режима с body
        document.body.classList.remove('content-locked');
        
        // Обновляем заголовок
        if (taskHeader) {
            taskHeader.classList.remove('content-locked');
        }
        
        // Обновляем контент
        if (taskContent) {
            taskContent.classList.remove('content-locked');
        }
        
        // Обновляем иконку замка
        if (lockIcon) {
            lockIcon.classList.remove('fa-lock');
            lockIcon.classList.add('fa-lock-open');
        }
        
        if (editLockToggle) {
            editLockToggle.classList.add('unlocked');
            editLockToggle.title = 'Заблокировать редактирование';
        }
        
        // Разблокируем иконки панели инструментов
        toolbarIcons.forEach(icon => {
            icon.classList.remove('disabled');
        });
        
        // Включаем contenteditable для редактируемых элементов
        enableContentEditing();
        
        // Восстанавливаем видимость блоков
        restoreBlocksVisibility();
        
        showNotification('Режим редактирования включен', 'success');
        
    } else {
        // Заблокировать редактирование
        isEditLocked = true;
        
        // Добавляем класс заблокированного режима к body
        document.body.classList.add('content-locked');
        
        // Обновляем заголовок
        if (taskHeader) {
            taskHeader.classList.add('content-locked');
        }
        
        // Обновляем контент
        if (taskContent) {
            taskContent.classList.add('content-locked');
        }
        
        // Обновляем иконку замка
        if (lockIcon) {
            lockIcon.classList.remove('fa-lock-open');
            lockIcon.classList.add('fa-lock');
        }
        
        if (editLockToggle) {
            editLockToggle.classList.remove('unlocked');
            editLockToggle.title = 'Разблокировать редактирование';
        }
        
        // Блокируем иконки панели инструментов
        toolbarIcons.forEach(icon => {
            icon.classList.add('disabled');
        });
        
        // Убеждаемся, что кнопка блокировки остается активной
        if (editLockToggle) {
            editLockToggle.classList.remove('disabled');
        }
        
        // Отключаем contenteditable для всех редактируемых элементов
        disableContentEditing();
        
        // Обеспечиваем работу интерактивных элементов в заблокированном режиме
        enableInteractiveElementsInLockedMode();
        
        // Закрываем все открытые меню
        closeAllMenus();
        
        showNotification('Режим просмотра включен', 'info');
    }
}

// Функция восстановления видимости блоков
function restoreBlocksVisibility() {
    const canvas = document.getElementById('contentCanvas');
    if (!canvas) return;
    
    // Восстанавливаем видимость всех блоков
    const allBlocks = canvas.querySelectorAll('.content-block');
    allBlocks.forEach(block => {
        // Убираем inline стили, которые могли быть добавлены JavaScript'ом
        block.style.removeProperty('display');
        block.style.removeProperty('pointer-events');
        
        // Восстанавливаем видимость панелей инструментов
        const toolbar = block.querySelector('.block-toolbar');
        if (toolbar) {
            toolbar.style.removeProperty('display');
            toolbar.style.removeProperty('pointer-events');
        }
        
        // Убираем активный класс, если он есть
        block.classList.remove('active');
    });
}

// Функция для обеспечения работы интерактивных элементов в заблокированном режиме
function enableInteractiveElementsInLockedMode() {
    const canvas = document.getElementById('contentCanvas');
    if (!canvas) return;
    
    // Активируем изображения
    const images = canvas.querySelectorAll('img');
    images.forEach(img => {
        img.style.pointerEvents = 'auto';
        img.style.cursor = 'pointer';
    });
    
    // Активируем элементы слайдера
    const swiperElements = canvas.querySelectorAll('.swiper-navigation, .swiper-pagination-bullet, .swiper-prev, .swiper-next');
    swiperElements.forEach(element => {
        element.style.pointerEvents = 'auto';
        element.style.cursor = 'pointer';
    });
    
    // Активируем ссылки для скачивания
    const downloadLinks = canvas.querySelectorAll('a[href], .btn[href], .download-btn, .file-download-link');
    downloadLinks.forEach(link => {
        link.style.pointerEvents = 'auto';
        link.style.cursor = 'pointer';
    });
    
    // Активируем блоки файлов
    const fileBlocks = canvas.querySelectorAll('.swiper-block, .file-grid-block, .image-block, .document-block');
    fileBlocks.forEach(block => {
        block.style.pointerEvents = 'auto';
        
        // Но блокируем панели инструментов
        const toolbar = block.querySelector('.block-toolbar');
        if (toolbar) {
            toolbar.style.pointerEvents = 'none';
            toolbar.style.display = 'none';
        }
    });
}

// Функция закрытия всех открытых меню
function closeAllMenus() {
    const statusMenu = document.getElementById('statusMenu');
    const datePickerContainer = document.getElementById('datePickerContainer');
    const assigneeMenu = document.getElementById('assigneeMenu');
    
    if (statusMenu) {
        statusMenu.classList.add('d-none');
    }
    
    if (datePickerContainer) {
        datePickerContainer.classList.add('d-none');
    }
    
    if (assigneeMenu) {
        assigneeMenu.classList.add('d-none');
    }
    
    // Закрываем редактирование названия
    const titleElement = document.getElementById('taskTitle');
    const inputElement = document.getElementById('taskTitleInput');
    
    if (titleElement && inputElement && !inputElement.classList.contains('d-none')) {
        inputElement.classList.add('d-none');
        titleElement.classList.remove('d-none');
    }
    
    // Скрываем панель форматирования
    hideFormatToolbar();
}

// Функция отключения редактирования контента
function disableContentEditing() {
    // Отключаем contenteditable для всех редактируемых элементов
    const editableElements = document.querySelectorAll('[contenteditable]');
    editableElements.forEach(element => {
        element.setAttribute('contenteditable', 'false');
        element.removeAttribute('data-was-editable');
    });
    
    // Отключаем редактирование для текстовых блоков
    const textBlocks = document.querySelectorAll('.text-content, .editable-content, .content-block');
    textBlocks.forEach(block => {
        if (block.getAttribute('contenteditable') === 'true') {
            block.setAttribute('data-was-editable', 'true');
            block.setAttribute('contenteditable', 'false');
        }
    });
    
    // Предотвращаем все события редактирования
    const contentCanvas = document.getElementById('contentCanvas');
    if (contentCanvas) {
        contentCanvas.addEventListener('keydown', preventEditingEvents);
        contentCanvas.addEventListener('paste', preventEditingEvents);
        contentCanvas.addEventListener('input', preventEditingEvents);
    }
}

// Функция включения редактирования контента
function enableContentEditing() {
    // Включаем contenteditable для элементов, которые были редактируемыми
    const editableElements = document.querySelectorAll('[data-was-editable]');
    editableElements.forEach(element => {
        element.setAttribute('contenteditable', 'true');
        element.removeAttribute('data-was-editable');
    });
    
    // Включаем редактирование для текстовых блоков
    const textBlocks = document.querySelectorAll('.text-content, .editable-content');
    textBlocks.forEach(block => {
        if (block.classList.contains('text-content') || block.classList.contains('editable-content')) {
            block.setAttribute('contenteditable', 'true');
        }
    });
    
    // Восстанавливаем панели инструментов блоков и видимость блоков
    const canvas = document.getElementById('contentCanvas');
    if (canvas) {
        const toolbars = canvas.querySelectorAll('.block-toolbar');
        toolbars.forEach(toolbar => {
            toolbar.style.removeProperty('pointer-events');
            toolbar.style.removeProperty('display');
        });
        
        // Восстанавливаем видимость всех блоков
        const allBlocks = canvas.querySelectorAll('.content-block');
        allBlocks.forEach(block => {
            block.style.removeProperty('display');
            block.style.removeProperty('pointer-events');
        });
    }
    
    // Удаляем обработчики предотвращения редактирования
    const contentCanvas = document.getElementById('contentCanvas');
    if (contentCanvas) {
        contentCanvas.removeEventListener('keydown', preventEditingEvents);
        contentCanvas.removeEventListener('paste', preventEditingEvents);
        contentCanvas.removeEventListener('input', preventEditingEvents);
    }
}

// Функция предотвращения событий редактирования
function preventEditingEvents(event) {
    if (isEditLocked) {
        // Разрешаем только события выделения и копирования
        if (event.type === 'keydown') {
            const allowedKeys = ['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'Home', 'End', 'PageUp', 'PageDown'];
            const isCtrlC = event.ctrlKey && event.key === 'c';
            const isCtrlA = event.ctrlKey && event.key === 'a';
            
            if (!allowedKeys.includes(event.key) && !isCtrlC && !isCtrlA) {
                event.preventDefault();
                event.stopPropagation();
                return false;
            }
        } else {
            event.preventDefault();
            event.stopPropagation();
            return false;
        }
    }
}

// ===== ДОПОЛНИТЕЛЬНЫЕ ФУНКЦИИ ДЛЯ КОНВЕРТАЦИИ БЛОКОВ =====

// Конвертация одиночного изображения в swiper блок и добавление новых изображений
function convertToSwiperAndAddMore(blockId) {
    if (isEditLocked) {
        showNotification('Разблокируйте редактирование для загрузки файлов', 'warning');
        return;
    }
    
    currentActiveBlockForUpload = blockId;
    const additionalImageUploader = document.getElementById('additionalImageUploader');
    additionalImageUploader.click();
}

// Конвертация обычного image блока в swiper блок
function convertImageBlockToSwiper(blockId, newFiles) {
    const block = document.getElementById(blockId);
    if (!block) return;
    
    // Получаем данные текущего изображения
    const currentFiles = JSON.parse(block.dataset.files || '[]');
    const allFiles = [...currentFiles, ...newFiles];
    
    // Сохраняем подпись
    const caption = block.querySelector('.image-caption');
    const captionText = caption ? caption.innerHTML : '';
    
    // Получаем родительский элемент
    const parent = block.parentNode;
    
    // Создаем новый swiper блок
    const swiperBlock = document.createElement('div');
    swiperBlock.className = 'content-block swiper-block';
    swiperBlock.id = blockId; // Сохраняем тот же ID
    swiperBlock.dataset.files = JSON.stringify(allFiles);
    
    const slidesHtml = allFiles.map((file, index) => `
        <div class="swiper-slide" data-index="${index}">
            ${(file.type === 'video' || file.type.startsWith('video/')) ? 
                `<video src="${file.url}" controls></video>
                 <button class="image-delete-btn" onclick="deleteImageFromSwiper('${blockId}', ${index})" title="Удалить видео">
                     <i class="fas fa-times"></i>
                 </button>` :
                `<img src="${file.url}" alt="${file.name}" onclick="showImageModal('${file.url}', '${file.name}')">
                 <button class="image-delete-btn" onclick="deleteImageFromSwiper('${blockId}', ${index})" title="Удалить изображение">
                     <i class="fas fa-times"></i>
                 </button>`
            }
        </div>
    `).join('');
    
    swiperBlock.innerHTML = `
        <div class="block-toolbar">
            <button class="block-btn drag-handle" title="Перетащить блок" data-block-id="${blockId}">
                <i class="fas fa-grip-vertical"></i>
            </button>
            <button class="block-btn add-more" onclick="addMoreImages('${blockId}')" title="Добавить еще изображения">
                <i class="fas fa-plus"></i>
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
        <div class="swiper-container" data-current-slide="0">
            <div class="swiper-wrapper" style="transform: translateX(0%);">
                ${slidesHtml}
            </div>
            ${allFiles.length > 1 ? `
                <button class="swiper-navigation swiper-prev" onclick="swiperPrev('${blockId}')">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="swiper-navigation swiper-next" onclick="swiperNext('${blockId}')">
                    <i class="fas fa-chevron-right"></i>
                </button>
                <div class="swiper-pagination">
                    ${allFiles.map((_, index) => `
                        <span class="swiper-pagination-bullet ${index === 0 ? 'active' : ''}" onclick="swiperGoTo('${blockId}', ${index})"></span>
                    `).join('')}
                </div>
                <div class="swiper-counter">1 / ${allFiles.length}</div>
            ` : ''}
        </div>
       
    `;
}

// Функции для управления выплывающим меню блокировки
function showLockMenu() {
    // Очищаем таймаут скрытия, если он есть
    if (lockMenuTimeout) {
        clearTimeout(lockMenuTimeout);
        lockMenuTimeout = null;
    }
    
    const lockMenu = document.getElementById('lockMenu');
    if (lockMenu) {
        lockMenu.classList.remove('d-none');
        // Небольшая задержка для корректной анимации
        setTimeout(() => {
            lockMenu.classList.add('show');
        }, 10);
    }
}

function hideLockMenu() {
    // Устанавливаем небольшую задержку перед скрытием
    lockMenuTimeout = setTimeout(() => {
        const lockMenu = document.getElementById('lockMenu');
        if (lockMenu) {
            lockMenu.classList.remove('show');
            // Скрываем элемент после завершения анимации
            setTimeout(() => {
                lockMenu.classList.add('d-none');
            }, 300);
        }
    }, 150);
}

function keepLockMenuOpen() {
    // Очищаем таймаут скрытия, когда мышь находится над меню
    if (lockMenuTimeout) {
        clearTimeout(lockMenuTimeout);
        lockMenuTimeout = null;
    }
}
    
  

// Функция для обработки добавления изображений к одиночному блоку
window.convertToSwiperAndAddMore = convertToSwiperAndAddMore;