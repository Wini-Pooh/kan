// Мобильная адаптивность для канбан-доски

document.addEventListener('DOMContentLoaded', function() {
    // Проверяем, находимся ли мы на странице канбан-доски
    const kanbanBoard = document.getElementById('kanban-board');
    if (!kanbanBoard) return;
    
    // Инициализируем функции адаптации для мобильных устройств
    initMobileKanban();
    
    // Добавляем обработчик изменения размера окна
    window.addEventListener('resize', debounce(function() {
        updateMobileKanban();
    }, 250));
});

// Функция для инициализации мобильного вида канбан-доски
function initMobileKanban() {
    const isMobile = window.innerWidth < 768;
    
    if (isMobile) {
        enhanceColumnHeaders();
    } else {
        removeMobileEnhancements();
    }
}

// Функция обновления при изменении размера экрана
function updateMobileKanban() {
    const isMobile = window.innerWidth < 768;
    
    if (isMobile) {
        enhanceColumnHeaders();
    } else {
        removeMobileEnhancements();
    }
}

// Функция для улучшения заголовков колонок на мобильных устройствах

// Функция для улучшения заголовков колонок на мобильных устройствах
function enhanceColumnHeaders() {
    // Проверяем, находимся ли мы на странице канбан-доски
    const kanbanBoard = document.getElementById('kanban-board');
    if (!kanbanBoard) return;
    
    const columnHeaders = document.querySelectorAll('.column-header');
    if (!columnHeaders || columnHeaders.length === 0) return;
    
    columnHeaders.forEach(header => {
        // Добавляем индикатор свайпа для перетаскивания
        if (!header.querySelector('.swipe-hint')) {
            const swipeHint = document.createElement('div');
            swipeHint.className = 'swipe-hint';
            swipeHint.innerHTML = '<i class="fas fa-arrows-alt-h"></i>';
            swipeHint.style.fontSize = '10px';
            swipeHint.style.opacity = '0.5';
            swipeHint.style.marginLeft = '5px';
            
            // Проверяем наличие элемента .column-info
            const columnInfo = header.querySelector('.column-info');
            if (columnInfo) {
                columnInfo.appendChild(swipeHint);
            } else {
                // Если элемент .column-info не найден, добавляем непосредственно в заголовок
                header.appendChild(swipeHint);
            }
        }
    });
}

// Удаление мобильных улучшений при возврате на десктоп

// Удаление мобильных улучшений при возврате на десктоп
function removeMobileEnhancements() {
    // Удаляем индикаторы свайпа
    const swipeHints = document.querySelectorAll('.swipe-hint');
    swipeHints.forEach(hint => hint.remove());
}

// Хелпер-функция debounce для ограничения частоты вызовов
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
