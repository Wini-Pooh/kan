// Глобальные переменные  
let titleClickCount = 0;
let titleClickTimer = null;
let saveTimeouts = {};
let taskId = null;
let csrfToken = null;

// Универсальная функция для API запросов
function makeApiRequest(url, data, method = 'PUT') {
    console.log(`Отправка ${method} запроса на ${url}`, data);
    
    return fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin',
        body: JSON.stringify(data)
    })
    .then(response => {
        console.log(`${method} ${url} - Status: ${response.status}, Content-Type: ${response.headers.get('content-type')}`);
        
        if (!response.ok) {
            // Если получили HTML вместо JSON (страница ошибки)
            if (response.headers.get('content-type')?.includes('text/html')) {
                return response.text().then(html => {
                    console.error('Получен HTML ответ:', html.substring(0, 500));
                    throw new Error(`HTTP ${response.status}: Сервер вернул HTML страницу ошибки`);
                });
            }
            throw new Error(`HTTP error! status: ${response.status} - ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (!data.success) {
            throw new Error(data.message || 'Ошибка API');
        }
        return data;
    });
}

// Специальная функция для сохранения контента
function saveContentToApi(content) {
    return makeApiRequest(`/api/tasks/${taskId}/content`, { content: content }, 'POST');
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    // Получаем переменные из data-атрибутов
    const taskHeader = document.querySelector('.task-header-fixed');
    if (taskHeader) {
        taskId = taskHeader.getAttribute('data-task-id');
        csrfToken = taskHeader.getAttribute('data-csrf-token');
    }
    
    // Проверяем, что переменные получены
    if (!taskId || !csrfToken) {
        console.error('taskId или csrfToken не определены. Убедитесь, что они определены в шаблоне Blade.');
        return;
    }
    
    // Инициализируем обработчики событий
    initializeEventHandlers();
    initializeDragAndDrop();
    makeEditableElementsEditable();
    
    // Автосохранение при любых изменениях
    initializeAutoSave();
    
    // Сохранение при закрытии страницы
    initializeBeforeUnload();
});

// Инициализация сохранения при закрытии страницы
function initializeBeforeUnload() {
    console.log('Инициализация сохранения при закрытии страницы...');
    
    // Сохранение при попытке закрыть страницу
    window.addEventListener('beforeunload', function(e) {
        // Проверяем, есть ли несохраненные изменения
        const canvas = document.getElementById('contentCanvas');
        if (canvas && canvas.innerHTML.trim() !== '') {
            console.log('Принудительное сохранение при закрытии страницы');
            
            // Синхронный запрос для сохранения (не рекомендуется, но необходимо для beforeunload)
            if (taskId && csrfToken) {
                navigator.sendBeacon(`/tasks/${taskId}/content`, new Blob([JSON.stringify({
                    content: canvas.innerHTML,
                    _token: csrfToken
                })], { type: 'application/json' }));
            }
        }
    });
    
    // Также сохраняем при потере фокуса страницы
    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'hidden') {
            console.log('Страница скрыта, запускаем сохранение');
            // Очищаем все таймеры debounce и сохраняем немедленно
            Object.keys(saveTimeouts).forEach(key => {
                clearTimeout(saveTimeouts[key]);
                delete saveTimeouts[key];
            });
            saveContent();
        }
    });
}

// Инициализация автосохранения
function initializeAutoSave() {
    console.log('Инициализация автосохранения...');
    
    // Автосохранение для редактируемого контента
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('editable-content') || e.target.contentEditable === 'true') {
            console.log('Событие input на редактируемом элементе:', e.target);
            debounce(saveContent, 1000, 'auto-save');
        }
    });
    
    // Автосохранение при изменении значений в формах
    document.addEventListener('change', function(e) {
        if (e.target.type === 'date' || e.target.type === 'select-one') {
            console.log('Событие change на элементе формы:', e.target);
            debounce(saveContent, 500, 'auto-save');
        }
    });
}

// Debounce функция для автосохранения
function debounce(func, delay, key) {
    console.log(`Debounce вызван для ключа: ${key}, задержка: ${delay}ms`);
    
    if (saveTimeouts[key]) {
        clearTimeout(saveTimeouts[key]);
        console.log(`Очищен предыдущий таймер для ключа: ${key}`);
    }
    
    saveTimeouts[key] = setTimeout(() => {
        console.log(`Выполнение функции после debounce для ключа: ${key}`);
        func();
        delete saveTimeouts[key];
    }, delay);
}

// Инициализация обработчиков событий
function initializeEventHandlers() {
    // Закрытие меню при клике вне их
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.status-container')) {
            const statusMenu = document.getElementById('statusMenu');
            if (statusMenu && !statusMenu.classList.contains('d-none')) {
                statusMenu.classList.add('d-none');
            }
        }
        
        if (!e.target.closest('.assignee-container')) {
            const assigneeMenu = document.getElementById('assigneeMenu');
            if (assigneeMenu && !assigneeMenu.classList.contains('d-none')) {
                assigneeMenu.classList.add('d-none');
            }
        }
        
        if (!e.target.closest('.time-container')) {
            const datePickerContainer = document.getElementById('datePickerContainer');
            if (datePickerContainer && !datePickerContainer.classList.contains('d-none')) {
                datePickerContainer.classList.add('d-none');
            }
        }
    });
}

// Функция для показа статуса сохранения
function showSaveStatus(element, status) {
    // Показываем статус в углу экрана
    showGlobalSaveStatus(status);
    
    // Также оставляем локальный индикатор
    const statusIndicator = element.querySelector('.save-status') || createSaveIndicator(element);
    
    statusIndicator.className = 'save-status';
    
    if (status === 'saving') {
        statusIndicator.className += ' saving';
        statusIndicator.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    } else if (status === 'saved') {
        statusIndicator.className += ' saved';
        statusIndicator.innerHTML = '<i class="fas fa-check"></i>';
        setTimeout(() => {
            statusIndicator.innerHTML = '';
        }, 2000);
    } else if (status === 'error') {
        statusIndicator.className += ' error';
        statusIndicator.innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
    }
}

// Глобальный индикатор сохранения
function showGlobalSaveStatus(status) {
    let globalIndicator = document.getElementById('globalSaveStatus');
    
    if (!globalIndicator) {
        globalIndicator = document.createElement('div');
        globalIndicator.id = 'globalSaveStatus';
        globalIndicator.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 15px;
            border-radius: 5px;
            z-index: 1000;
            font-size: 14px;
            font-weight: 500;
            display: none;
            transition: all 0.3s ease;
        `;
        document.body.appendChild(globalIndicator);
    }
    
    if (status === 'saving') {
        globalIndicator.style.cssText += `
            background-color: #fbbf24;
            color: #92400e;
            display: block;
        `;
        globalIndicator.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Сохранение...';
    } else if (status === 'saved') {
        globalIndicator.style.cssText += `
            background-color: #10b981;
            color: white;
            display: block;
        `;
        globalIndicator.innerHTML = '<i class="fas fa-check"></i> Сохранено';
        setTimeout(() => {
            globalIndicator.style.display = 'none';
        }, 2000);
    } else if (status === 'error') {
        globalIndicator.style.cssText += `
            background-color: #ef4444;
            color: white;
            display: block;
        `;
        globalIndicator.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Ошибка сохранения';
        setTimeout(() => {
            globalIndicator.style.display = 'none';
        }, 5000);
    }
}

function createSaveIndicator(element) {
    const indicator = document.createElement('span');
    indicator.className = 'save-status';
    indicator.style.marginLeft = '8px';
    element.appendChild(indicator);
    return indicator;
}

// Показать уведомление
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);
    
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// ===== ФУНКЦИИ ДЛЯ РЕДАКТИРОВАНИЯ ЗАГОЛОВКА =====

// Редактирование названия задачи
function editTaskTitle() {
    titleClickCount++;
    
    if (titleClickCount === 1) {
        titleClickTimer = setTimeout(() => {
            titleClickCount = 0;
        }, 300);
    } else if (titleClickCount === 2) {
        clearTimeout(titleClickTimer);
        titleClickCount = 0;
        
        const titleElement = document.getElementById('taskTitle');
        const inputElement = document.getElementById('taskTitleInput');
        
        if (titleElement && inputElement) {
            inputElement.value = titleElement.textContent;
            titleElement.classList.add('d-none');
            inputElement.classList.remove('d-none');
            inputElement.focus();
            inputElement.select();
        }
    }
}

function handleTitleKeyPress(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
        saveTaskTitle();
    } else if (event.key === 'Escape') {
        cancelTitleEdit();
    } else {
        // Автосохранение при вводе
        debounce(saveTaskTitleAuto, 1000, 'title-auto');
    }
}

function saveTaskTitle() {
    const titleElement = document.getElementById('taskTitle');
    const inputElement = document.getElementById('taskTitleInput');
    
    if (!titleElement || !inputElement) return;
    
    const newTitle = inputElement.value.trim();
    
    if (newTitle === '') {
        showNotification('Название задачи не может быть пустым', 'error');
        return;
    }
    
    // Обновляем UI
    titleElement.textContent = newTitle;
    titleElement.classList.remove('d-none');
    inputElement.classList.add('d-none');
    
    // Отправляем на сервер
    if (taskId && csrfToken) {
        makeApiRequest(`/api/tasks/${taskId}`, { title: newTitle })
            .then(data => {
                showNotification('Название сохранено');
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Ошибка сохранения: ' + error.message, 'error');
            });
    }
}

function saveTaskTitleAuto() {
    const inputElement = document.getElementById('taskTitleInput');
    if (!inputElement) return;
    
    const newTitle = inputElement.value.trim();
    if (newTitle === '') return;
    
    if (taskId && csrfToken) {
        showSaveStatus(inputElement.parentElement, 'saving');
        
        makeApiRequest(`/api/tasks/${taskId}`, { title: newTitle })
            .then(data => {
                showSaveStatus(inputElement.parentElement, 'saved');
                const titleElement = document.getElementById('taskTitle');
                if (titleElement) {
                    titleElement.textContent = newTitle;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showSaveStatus(inputElement.parentElement, 'error');
            });
    }
}

function cancelTitleEdit() {
    const titleElement = document.getElementById('taskTitle');
    const inputElement = document.getElementById('taskTitleInput');
    
    if (titleElement && inputElement) {
        titleElement.classList.remove('d-none');
        inputElement.classList.add('d-none');
    }
}

// ===== ФУНКЦИИ ДЛЯ СТАТУСА/ПРИОРИТЕТА =====

function toggleStatusMenu() {
    const statusMenu = document.getElementById('statusMenu');
    if (statusMenu) {
        statusMenu.classList.toggle('d-none');
    }
}

function changePriority(priority) {
    const statusSquare = document.getElementById('statusSquare');
    const statusMenu = document.getElementById('statusMenu');
    
    if (statusSquare) {
        statusSquare.setAttribute('data-priority', priority);
    }
    
    if (statusMenu) {
        statusMenu.classList.add('d-none');
    }
    
    // Отправляем на сервер
    if (taskId && csrfToken) {
        makeApiRequest(`/api/tasks/${taskId}`, { priority: priority })
            .then(data => {
                showNotification('Приоритет изменен');
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Ошибка сохранения: ' + error.message, 'error');
            });
    }
}

// ===== ФУНКЦИИ ДЛЯ ДАТЫ =====

function toggleDatePicker() {
    const datePickerContainer = document.getElementById('datePickerContainer');
    if (datePickerContainer) {
        datePickerContainer.classList.toggle('d-none');
    }
}

function saveStartDate() {
    const startDateInput = document.getElementById('startDateInput');
    if (!startDateInput) return;
    
    const startDate = startDateInput.value;
    
    if (taskId && csrfToken) {
        makeApiRequest(`/api/tasks/${taskId}`, { start_date: startDate })
            .then(data => {
                showNotification('Дата начала сохранена');
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Ошибка сохранения: ' + error.message, 'error');
            });
    }
}

function saveDueDate() {
    const dueDateInput = document.getElementById('dueDateInput');
    if (!dueDateInput) return;
    
    const dueDate = dueDateInput.value;
    
    if (taskId && csrfToken) {
        makeApiRequest(`/api/tasks/${taskId}`, { due_date: dueDate })
            .then(data => {
                showNotification('Дата завершения сохранена');
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Ошибка сохранения: ' + error.message, 'error');
            });
    }
}

function clearAllDates() {
    const startDateInput = document.getElementById('startDateInput');
    const dueDateInput = document.getElementById('dueDateInput');
    
    if (startDateInput) startDateInput.value = '';
    if (dueDateInput) dueDateInput.value = '';
    
    if (taskId && csrfToken) {
        fetch(`/api/tasks/${taskId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ start_date: null, due_date: null })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Даты очищены');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Ошибка сохранения', 'error');
        });
    }
}

function closeDatePicker() {
    const datePickerContainer = document.getElementById('datePickerContainer');
    if (datePickerContainer) {
        datePickerContainer.classList.add('d-none');
    }
}

// ===== ФУНКЦИИ ДЛЯ ИСПОЛНИТЕЛЯ =====

function toggleAssigneeMenu() {
    const assigneeMenu = document.getElementById('assigneeMenu');
    if (assigneeMenu) {
        assigneeMenu.classList.toggle('d-none');
    }
}

function changeAssignee(userId) {
    const assigneeMenu = document.getElementById('assigneeMenu');
    if (assigneeMenu) {
        assigneeMenu.classList.add('d-none');
    }
    
    // Отправляем на сервер
    if (taskId && csrfToken) {
        makeApiRequest(`/api/tasks/${taskId}`, { assigned_to: userId })
            .then(data => {
                showNotification('Исполнитель изменен');
                // Обновляем UI
                updateAssigneeUI(data.task);
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Ошибка сохранения: ' + error.message, 'error');
            });
    }
}

// ===== ФУНКЦИИ ДЛЯ КОНТЕНТА =====

function openImageUpload() {
    const imageUpload = document.getElementById('imageUpload');
    if (imageUpload) {
        imageUpload.setAttribute('multiple', 'multiple');
        imageUpload.click();
    }
}

function openVideoUpload() {
    const videoUpload = document.getElementById('videoUpload');
    if (videoUpload) {
        videoUpload.click();
    }
}

function openFileUpload() {
    const fileUpload = document.getElementById('fileUpload');
    if (fileUpload) {
        fileUpload.click();
    }
}

function handleImageUpload(event) {
    const files = event.target.files;
    
    for (let file of files) {
        if (file && file.type.startsWith('image/')) {
            uploadFileToServer(file);
        }
    }
    
    event.target.value = '';
}

function handleVideoUpload(event) {
    const file = event.target.files[0];
    
    if (file && file.type.startsWith('video/')) {
        uploadFileToServer(file);
    }
    
    event.target.value = '';
}

function handleFileUpload(event) {
    const file = event.target.files[0];
    
    if (file) {
        uploadFileToServer(file);
    }
    
    event.target.value = '';
}

// Универсальная функция загрузки файла на сервер
function uploadFileToServer(file) {
    if (!taskId || !csrfToken) {
        showNotification('Ошибка: не найдены данные задачи', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('file', file);

    showGlobalSaveStatus('saving');
    
    fetch(`/api/tasks/${taskId}/upload`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showGlobalSaveStatus('saved');
            showNotification(`Файл "${data.file_name}" успешно загружен`);
            
            // Обновляем содержимое страницы
            location.reload();
        } else {
            throw new Error(data.message || 'Неизвестная ошибка');
        }
    })
    .catch(error => {
        console.error('Error uploading file:', error);
        showGlobalSaveStatus('error');
        showNotification(`Ошибка загрузки файла: ${error.message}`, 'error');
    });
}

function addTextBlock() {
    const canvas = document.getElementById('contentCanvas');
    if (!canvas) return;
    
    const blockId = 'block_' + Date.now();
    const textBlock = document.createElement('div');
    textBlock.className = 'content-block text-block';
    textBlock.setAttribute('data-type', 'text');
    textBlock.setAttribute('data-id', blockId);
    
    textBlock.innerHTML = `
        <div class="block-content">
            <div class="text-content editable" contenteditable="true">Начните печатать...</div>
        </div>
        <div class="block-toolbar">
            <button class="block-btn" onclick="formatText('bold')" title="Жирный">
                <i class="fas fa-bold"></i>
            </button>
            <button class="block-btn" onclick="formatText('italic')" title="Курсив">
                <i class="fas fa-italic"></i>
            </button>
            <button class="block-btn" onclick="formatText('underline')" title="Подчеркнутый">
                <i class="fas fa-underline"></i>
            </button>
            <button class="block-btn delete-btn" onclick="deleteBlock(this)" title="Удалить">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
    
    // Удаляем пустой canvas если он есть
    const emptyCanvas = canvas.querySelector('.empty-canvas');
    if (emptyCanvas) {
        emptyCanvas.remove();
    }
    
    canvas.appendChild(textBlock);
    
    // Фокусируемся на новом блоке
    const textContent = textBlock.querySelector('.text-content');
    if (textContent) {
        textContent.focus();
        // Выделяем весь текст в contenteditable элементе
        if (window.getSelection && document.createRange) {
            const selection = window.getSelection();
            const range = document.createRange();
            range.selectNodeContents(textContent);
            selection.removeAllRanges();
            selection.addRange(range);
        }
    }
    
    makeEditableElementsEditable();
    
    // Запускаем автосохранение после добавления блока
    debounce(saveContent, 1000, 'content');
}

function addImageGalleryBlock(images) {
    const canvas = document.getElementById('contentCanvas');
    if (!canvas) return;
    
    const blockId = 'gallery_' + Date.now();
    const galleryBlock = document.createElement('div');
    galleryBlock.className = 'content-block gallery-block';
    galleryBlock.setAttribute('data-type', 'gallery');
    galleryBlock.setAttribute('data-id', blockId);
    
    let galleryHTML = `
        <div class="block-content">
            <div class="gallery-container">
                <div class="gallery-grid">
    `;
    
    images.forEach((image, index) => {
        galleryHTML += `
            <div class="gallery-item" onclick="openImageModal('${image.src}', '${image.name}')">
                <img src="${image.src}" alt="${image.name}" />
                <div class="image-overlay">
                    <i class="fas fa-expand"></i>
                </div>
            </div>
        `;
    });
    
    galleryHTML += `
                </div>
                <div class="gallery-caption editable" contenteditable="true" placeholder="Добавить подпись к галерее..."></div>
            </div>
        </div>
        <div class="block-toolbar">
            <button class="block-btn" onclick="addMoreImages(this)" title="Добавить изображения">
                <i class="fas fa-plus"></i>
            </button>
            <button class="block-btn delete-btn" onclick="deleteBlock(this)" title="Удалить">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
    
    galleryBlock.innerHTML = galleryHTML;
    
    // Удаляем пустой canvas если он есть
    const emptyCanvas = canvas.querySelector('.empty-canvas');
    if (emptyCanvas) {
        emptyCanvas.remove();
    }
    
    canvas.appendChild(galleryBlock);
    debounce(saveContent, 1000, 'content');
}

function addImageBlock(src, filename) {
    const canvas = document.getElementById('contentCanvas');
    if (!canvas) return;
    
    const blockId = 'block_' + Date.now();
    const imageBlock = document.createElement('div');
    imageBlock.className = 'content-block image-block';
    imageBlock.setAttribute('data-type', 'image');
    imageBlock.setAttribute('data-id', blockId);
    
    imageBlock.innerHTML = `
        <div class="block-content">
            <div class="image-container" onclick="openImageModal('${src}', '${filename}')">
                <img src="${src}" alt="${filename}" class="block-image">
                <div class="image-overlay">
                    <i class="fas fa-expand"></i>
                </div>
            </div>
            <div class="image-caption editable" contenteditable="true" placeholder="Добавить подпись..."></div>
        </div>
        <div class="block-toolbar">
            <button class="block-btn delete-btn" onclick="deleteBlock(this)" title="Удалить">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
    
    // Удаляем пустой canvas если он есть
    const emptyCanvas = canvas.querySelector('.empty-canvas');
    if (emptyCanvas) {
        emptyCanvas.remove();
    }
    
    canvas.appendChild(imageBlock);
    debounce(saveContent, 1000, 'content');
}

function openImageModal(src, filename) {
    // Создаем модальное окно для просмотра изображения
    const modal = document.createElement('div');
    modal.className = 'image-modal';
    modal.onclick = function() { this.remove(); };
    
    modal.innerHTML = `
        <div class="modal-content" onclick="event.stopPropagation()">
            <span class="close-modal" onclick="this.parentElement.parentElement.remove()">&times;</span>
            <img src="${src}" alt="${filename}" class="modal-image">
            <div class="modal-caption">${filename}</div>
        </div>
    `;
    
    document.body.appendChild(modal);
}

function addMoreImages(button) {
    const galleryBlock = button.closest('.gallery-block');
    const galleryGrid = galleryBlock.querySelector('.gallery-grid');
    
    // Создаем временный input для выбора файлов
    const tempInput = document.createElement('input');
    tempInput.type = 'file';
    tempInput.accept = 'image/*';
    tempInput.multiple = true;
    tempInput.style.display = 'none';
    
    tempInput.onchange = function(event) {
        const files = event.target.files;
        
        for (let file of files) {
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const galleryItem = document.createElement('div');
                    galleryItem.className = 'gallery-item';
                    galleryItem.onclick = function() { openImageModal(e.target.result, file.name); };
                    
                    galleryItem.innerHTML = `
                        <img src="${e.target.result}" alt="${file.name}" />
                        <div class="image-overlay">
                            <i class="fas fa-expand"></i>
                        </div>
                    `;
                    
                    galleryGrid.appendChild(galleryItem);
                    debounce(saveContent, 1000, 'content');
                };
                reader.readAsDataURL(file);
            }
        }
        
        tempInput.remove();
    };
    
    document.body.appendChild(tempInput);
    tempInput.click();
}

function formatText(command) {
    document.execCommand(command, false, null);
}

function deleteBlock(button) {
    const block = button.closest('.content-block');
    if (block) {
        block.remove();
        
        // Проверяем, остались ли блоки
        const canvas = document.getElementById('contentCanvas');
        if (canvas && canvas.children.length === 0) {
            showEmptyState();
        }
        
        debounce(saveContent, 500, 'content');
    }
}

function showEmptyState() {
    const canvas = document.getElementById('contentCanvas');
    if (!canvas) return;
    
    canvas.innerHTML = `
        <div class="empty-canvas">
            <div class="empty-icon">
                <i class="fas fa-plus-circle"></i>
            </div>
            <h3>Добавьте контент</h3>
            <p>Нажмите на иконки внизу, чтобы добавить текст, изображения или файлы</p>
        </div>
    `;
}

function makeEditableElementsEditable() {
    const editableElements = document.querySelectorAll('.text-content.editable, .image-caption.editable, .gallery-caption.editable');
    
    editableElements.forEach(element => {
        element.addEventListener('input', handleContentInput);
        element.addEventListener('paste', handleContentPaste);
        element.addEventListener('focus', function() {
            if (this.textContent === 'Начните печатать...' || 
                this.textContent === 'Добавить подпись...' ||
                this.textContent === 'Добавить подпись к галерее...') {
                this.textContent = '';
            }
        });
        element.addEventListener('blur', function() {
            if (this.textContent.trim() === '') {
                if (this.classList.contains('text-content')) {
                    this.textContent = 'Начните печатать...';
                } else if (this.classList.contains('gallery-caption')) {
                    this.textContent = 'Добавить подпись к галерее...';
                } else {
                    this.textContent = 'Добавить подпись...';
                }
            }
        });
    });
}

function handleContentInput(e) {
    debounce(saveContent, 2000, 'content');
}

function handleContentPaste(e) {
    e.preventDefault();
    const text = e.clipboardData.getData('text/plain');
    document.execCommand('insertText', false, text);
}

function saveContent(retryCount = 0) {
    const editableContent = document.querySelector('.editable-content');
    if (!editableContent) {
        console.log('Редактируемый контент не найден');
        return;
    }
    
    // Получаем только текстовое содержимое, исключая HTML файлов
    const textContent = editableContent.innerText || editableContent.textContent || '';
    console.log('Запуск автосохранения текста...');
    
    if (taskId && csrfToken) {
        showSaveStatus(editableContent, 'saving');
        console.log('Отправка запроса на сохранение контента');
        
        saveContentToApi(textContent)
            .then(data => {
                console.log('Контент успешно сохранен');
                showSaveStatus(editableContent, 'saved');
            })
            .catch(error => {
                console.error('Ошибка запроса:', error);
                
                // Повторная попытка для определенных ошибок
                if (retryCount < 3 && (
                    error.message.includes('server has gone away') ||
                    error.message.includes('HTTP 500') ||
                    error.message.includes('Network') ||
                    error.message.includes('timeout')
                )) {
                    console.log(`Повторная попытка ${retryCount + 1}/3 через 2 секунды...`);
                    setTimeout(() => {
                        saveContent(retryCount + 1);
                    }, 2000);
                } else {
                    showSaveStatus(editableContent, 'error');
                }
            });
    } else {
        console.log('Не хватает taskId или csrfToken для сохранения:', { taskId, csrfToken });
    }
}

// ===== DRAG & DROP ФУНКЦИОНАЛЬНОСТЬ =====

function initializeDragAndDrop() {
    const canvas = document.getElementById('contentCanvas');
    if (!canvas) return;
    
    // Предотвращаем стандартное поведение браузера
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        canvas.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });
    
    // Подсветка при наведении
    ['dragenter', 'dragover'].forEach(eventName => {
        canvas.addEventListener(eventName, highlight, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        canvas.addEventListener(eventName, unhighlight, false);
    });
    
    // Обработка сброса файлов
    canvas.addEventListener('drop', handleDrop, false);
}

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

function highlight() {
    const canvas = document.getElementById('contentCanvas');
    if (canvas) {
        canvas.classList.add('drag-over');
    }
}

function unhighlight() {
    const canvas = document.getElementById('contentCanvas');
    if (canvas) {
        canvas.classList.remove('drag-over');
    }
}

function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    
    handleFiles(files);
}

function handleFiles(files) {
    // Просто загружаем все файлы на сервер
    Array.from(files).forEach(file => {
        uploadFileToServer(file);
    });
}

function addVideoBlock(src, filename) {
    const canvas = document.getElementById('contentCanvas');
    if (!canvas) return;
    
    const blockId = 'block_' + Date.now();
    const videoBlock = document.createElement('div');
    videoBlock.className = 'content-block video-block';
    videoBlock.setAttribute('data-type', 'video');
    videoBlock.setAttribute('data-id', blockId);
    
    videoBlock.innerHTML = `
        <div class="block-content">
            <video src="${src}" controls class="block-video"></video>
            <div class="video-caption editable" contenteditable="true" placeholder="Добавить подпись..."></div>
        </div>
        <div class="block-toolbar">
            <button class="block-btn delete-btn" onclick="deleteBlock(this)" title="Удалить">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
    
    // Удаляем пустой canvas если он есть
    const emptyCanvas = canvas.querySelector('.empty-canvas');
    if (emptyCanvas) {
        emptyCanvas.remove();
    }
    
    canvas.appendChild(videoBlock);
    debounce(saveContent, 1000, 'content');
}

function addFileBlock(file) {
    const canvas = document.getElementById('contentCanvas');
    if (!canvas) return;
    
    const blockId = 'block_' + Date.now();
    const fileBlock = document.createElement('div');
    fileBlock.className = 'content-block file-block';
    fileBlock.setAttribute('data-type', 'file');
    fileBlock.setAttribute('data-id', blockId);
    
    const fileIcon = getFileIcon(file.type);
    const fileSize = formatFileSize(file.size);
    
    fileBlock.innerHTML = `
        <div class="block-content">
            <div class="file-info">
                <div class="file-icon">
                    <i class="${fileIcon}"></i>
                </div>
                <div class="file-details">
                    <h4>${file.name}</h4>
                    <p>${fileSize}</p>
                </div>
            </div>
        </div>
        <div class="block-toolbar">
            <button class="block-btn delete-btn" onclick="deleteBlock(this)" title="Удалить">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
    
    // Удаляем пустой canvas если он есть
    const emptyCanvas = canvas.querySelector('.empty-canvas');
    if (emptyCanvas) {
        emptyCanvas.remove();
    }
    
    canvas.appendChild(fileBlock);
    debounce(saveContent, 1000, 'content');
}

// ===== УТИЛИТАРНЫЕ ФУНКЦИИ =====

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function getFileIcon(mimeType) {
    if (mimeType.startsWith('image/')) return 'fas fa-image';
    if (mimeType.startsWith('video/')) return 'fas fa-video';
    if (mimeType.startsWith('audio/')) return 'fas fa-music';
    if (mimeType.includes('pdf')) return 'fas fa-file-pdf';
    if (mimeType.includes('word')) return 'fas fa-file-word';
    if (mimeType.includes('excel') || mimeType.includes('spreadsheet')) return 'fas fa-file-excel';
    if (mimeType.includes('powerpoint') || mimeType.includes('presentation')) return 'fas fa-file-powerpoint';
    if (mimeType.includes('zip') || mimeType.includes('rar')) return 'fas fa-file-archive';
    return 'fas fa-file';
}

function updateAssigneeUI(task) {
    // Здесь должна быть логика обновления UI исполнителя
    // В зависимости от структуры данных задачи
}

// Экспортируем функции в глобальную область видимости для совместимости с inline обработчиками
window.editTaskTitle = editTaskTitle;
window.handleTitleKeyPress = handleTitleKeyPress;
window.saveTaskTitle = saveTaskTitle;
window.saveTaskTitleAuto = saveTaskTitleAuto;
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
window.addTextBlock = addTextBlock;
window.formatText = formatText;
window.deleteBlock = deleteBlock;
window.saveContent = saveContent;
window.debounce = debounce;
window.openImageUpload = openImageUpload;
window.openVideoUpload = openVideoUpload;
window.openFileUpload = openFileUpload;
window.handleImageUpload = handleImageUpload;
window.handleVideoUpload = handleVideoUpload;
window.handleFileUpload = handleFileUpload;
window.addMoreImages = addMoreImages;
window.openImageModal = openImageModal;
