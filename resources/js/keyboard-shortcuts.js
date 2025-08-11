/**
 * Система горячих клавиш для Kanban приложения
 * 
 * @author GitHub Copilot
 * @version 1.0.0
 */

class KeyboardShortcuts {
    constructor() {
        this.shortcuts = new Map();
        this.isEnabled = true;
        this.currentActiveElement = null;
        
        this.init();
    }

    /**
     * Инициализация системы горячих клавиш
     */
    init() {
        this.registerShortcuts();
        this.attachEventListeners();
        this.createStatusIndicator();
    }

    /**
     * Регистрация всех горячих клавиш
     */
    registerShortcuts() {
        // Основные действия
        this.addShortcut('ctrl+e', this.toggleEditMode.bind(this), 'Переключить режим редактирования');
        this.addShortcut('ctrl+s', this.saveContent.bind(this), 'Сохранить содержимое');
        this.addShortcut('ctrl+p', this.printTask.bind(this), 'Печать задачи');
        this.addShortcut('ctrl+shift+p', this.downloadPDF.bind(this), 'Скачать PDF');
        this.addShortcut('escape', this.handleEscape.bind(this), 'Закрыть/Назад');

        // Добавление контента
        this.addShortcut('ctrl+shift+t', this.addTextBlock.bind(this), 'Добавить текстовый блок');
        this.addShortcut('ctrl+shift+i', this.addImage.bind(this), 'Добавить изображение');
        this.addShortcut('ctrl+shift+d', this.addDocument.bind(this), 'Добавить документ');
        this.addShortcut('ctrl+shift+n', this.addPageBreak.bind(this), 'Добавить новый лист');

        // Форматирование текста (дополнительные к существующим)
        this.addShortcut('ctrl+shift+l', this.insertLink.bind(this), 'Вставить ссылку');
        this.addShortcut('ctrl+shift+1', () => this.insertList('ul'), 'Маркированный список');
        this.addShortcut('ctrl+shift+2', () => this.insertList('ol'), 'Нумерованный список');
        this.addShortcut('ctrl+shift+f', this.showFormatToolbar.bind(this), 'Панель форматирования');

        // Управление приоритетами
        this.addShortcut('ctrl+1', () => this.setPriority('low'), 'Приоритет: Низкий');
        this.addShortcut('ctrl+2', () => this.setPriority('medium'), 'Приоритет: Средний');
        this.addShortcut('ctrl+3', () => this.setPriority('high'), 'Приоритет: Высокий');
        this.addShortcut('ctrl+4', () => this.setPriority('urgent'), 'Приоритет: Срочный');
        this.addShortcut('ctrl+5', () => this.setPriority('critical'), 'Приоритет: Критический');

        // Редактирование
        this.addShortcut('f2', this.editTitle.bind(this), 'Редактировать название');
        this.addShortcut('ctrl+d', this.openDatePicker.bind(this), 'Открыть календарь');

        // Навигация по блокам
        this.addShortcut('ctrl+up', this.moveBlockUp.bind(this), 'Переместить блок вверх');
        this.addShortcut('ctrl+down', this.moveBlockDown.bind(this), 'Переместить блок вниз');
        this.addShortcut('delete', this.deleteActiveBlock.bind(this), 'Удалить блок');
        this.addShortcut('tab', this.focusNextBlock.bind(this), 'Следующий блок');
        this.addShortcut('shift+tab', this.focusPrevBlock.bind(this), 'Предыдущий блок');

        // Просмотр файлов
        this.addShortcut('space', this.openImageModal.bind(this), 'Открыть изображение');
        this.addShortcut('left', this.swiperPrev.bind(this), 'Предыдущее изображение');
        this.addShortcut('right', this.swiperNext.bind(this), 'Следующее изображение');

        // Утилиты
        this.addShortcut('ctrl+shift+k', this.toggleShortcuts.bind(this), 'Включить/выключить горячие клавиши');
    }

    /**
     * Добавление нового сочетания клавиш
     * @param {string} combination - Комбинация клавиш
     * @param {function} callback - Функция для выполнения
     * @param {string} description - Описание действия
     */
    addShortcut(combination, callback, description) {
        this.shortcuts.set(combination, {
            callback,
            description,
            enabled: true
        });
    }

    /**
     * Подключение обработчиков событий
     */
    attachEventListeners() {
        document.addEventListener('keydown', this.handleKeyDown.bind(this));
        
        // Отслеживание активного элемента
        document.addEventListener('focusin', this.updateActiveElement.bind(this));
        document.addEventListener('click', this.updateActiveElement.bind(this));
    }

    /**
     * Обработка нажатий клавиш
     * @param {KeyboardEvent} event 
     */
    handleKeyDown(event) {
        if (!this.isEnabled) return;

        // Не обрабатываем сочетания в полях ввода (кроме специальных случаев)
        if (this.isInputElement(event.target) && !this.isSpecialShortcut(event)) {
            return;
        }

        const combination = this.getCombination(event);
        const shortcut = this.shortcuts.get(combination);

        if (shortcut && shortcut.enabled) {
            event.preventDefault();
            event.stopPropagation();
            
            try {
                shortcut.callback(event);
                this.showShortcutFeedback(combination, shortcut.description);
            } catch (error) {
                console.error(`Ошибка выполнения горячей клавиши ${combination}:`, error);
            }
        }
    }

    /**
     * Получение строки комбинации клавиш
     * @param {KeyboardEvent} event 
     * @returns {string}
     */
    getCombination(event) {
        const parts = [];
        
        if (event.ctrlKey) parts.push('ctrl');
        if (event.shiftKey) parts.push('shift');
        if (event.altKey) parts.push('alt');
        if (event.metaKey) parts.push('meta');
        
        const key = event.key.toLowerCase();
        
        // Специальные клавиши
        const specialKeys = {
            'arrowup': 'up',
            'arrowdown': 'down',
            'arrowleft': 'left',
            'arrowright': 'right',
            ' ': 'space'
        };
        
        parts.push(specialKeys[key] || key);
        
        return parts.join('+');
    }

    /**
     * Проверка, является ли элемент полем ввода
     * @param {Element} element 
     * @returns {boolean}
     */
    isInputElement(element) {
        const inputTypes = ['input', 'textarea', 'select'];
        const contentEditable = element.contentEditable === 'true';
        
        return inputTypes.includes(element.tagName.toLowerCase()) || contentEditable;
    }

    /**
     * Проверка специальных сочетаний, которые работают даже в полях ввода
     * @param {KeyboardEvent} event 
     * @returns {boolean}
     */
    isSpecialShortcut(event) {
        const combination = this.getCombination(event);
        const specialShortcuts = ['ctrl+s', 'escape', 'f2', 'ctrl+shift+h'];
        
        return specialShortcuts.includes(combination);
    }

    /**
     * Обновление активного элемента
     * @param {Event} event 
     */
    updateActiveElement(event) {
        this.currentActiveElement = event.target;
    }

    // === РЕАЛИЗАЦИЯ ДЕЙСТВИЙ ===

    toggleEditMode() {
        if (typeof window.toggleEditMode === 'function') {
            window.toggleEditMode();
        }
    }

    saveContent() {
        if (typeof window.saveContent === 'function') {
            window.saveContent();
        }
    }

    printTask() {
        if (typeof window.printTask === 'function') {
            window.printTask();
        }
    }

    downloadPDF() {
        if (typeof window.downloadTaskPDF === 'function') {
            window.downloadTaskPDF();
        }
    }

    handleEscape() {
        // Закрыть модальные окна
        const modals = document.querySelectorAll('.modal.show');
        if (modals.length > 0) {
            modals.forEach(modal => {
                const closeBtn = modal.querySelector('[data-bs-dismiss="modal"]');
                if (closeBtn) closeBtn.click();
            });
            return;
        }

        // Закрыть выпадающие меню
        const dropdowns = document.querySelectorAll('.dropdown-menu.show, .status-menu:not(.d-none), .date-picker-container:not(.d-none), .assignee-menu:not(.d-none)');
        if (dropdowns.length > 0) {
            dropdowns.forEach(dropdown => {
                dropdown.classList.add('d-none');
                dropdown.classList.remove('show');
            });
            return;
        }

        // Вернуться к канбан-доске
        const backBtn = document.querySelector('.back-to-kanban-btn');
        if (backBtn) {
            backBtn.click();
        }
    }

    addTextBlock() {
        if (!window.isEditLocked && typeof window.addTextBlock === 'function') {
            window.addTextBlock();
        }
    }

    addImage() {
        if (!window.isEditLocked && typeof window.triggerImageUpload === 'function') {
            window.triggerImageUpload();
        }
    }

    addDocument() {
        if (!window.isEditLocked && typeof window.triggerDocumentUpload === 'function') {
            window.triggerDocumentUpload();
        }
    }

    addPageBreak() {
        if (!window.isEditLocked && typeof window.addPageBreak === 'function') {
            window.addPageBreak();
        }
    }

    insertLink() {
        if (!window.isEditLocked && typeof window.insertLink === 'function') {
            window.insertLink();
        }
    }

    insertList(type) {
        if (!window.isEditLocked && typeof window.insertList === 'function') {
            window.insertList(type);
        }
    }

    showFormatToolbar() {
        if (!window.isEditLocked && typeof window.toggleFormatMenu === 'function') {
            window.toggleFormatMenu();
        }
    }

    setPriority(priority) {
        if (typeof window.changePriority === 'function') {
            window.changePriority(priority);
            
            // Добавляем анимацию к квадрату приоритета
            const statusSquare = document.getElementById('statusSquare');
            if (statusSquare) {
                statusSquare.classList.add('priority-highlight');
                setTimeout(() => {
                    statusSquare.classList.remove('priority-highlight');
                }, 600);
            }
        }
    }

    editTitle() {
        if (typeof window.editTaskTitle === 'function') {
            window.editTaskTitle();
        }
    }

    openDatePicker() {
        if (typeof window.toggleDatePicker === 'function') {
            window.toggleDatePicker();
        }
    }

    moveBlockUp() {
        const activeBlock = this.getActiveBlock();
        if (activeBlock && !window.isEditLocked) {
            const blockId = activeBlock.id;
            if (typeof window.moveBlockUp === 'function') {
                window.moveBlockUp(blockId);
            }
        }
    }

    moveBlockDown() {
        const activeBlock = this.getActiveBlock();
        if (activeBlock && !window.isEditLocked) {
            const blockId = activeBlock.id;
            if (typeof window.moveBlockDown === 'function') {
                window.moveBlockDown(blockId);
            }
        }
    }

    deleteActiveBlock() {
        const activeBlock = this.getActiveBlock();
        if (activeBlock && !window.isEditLocked) {
            const blockId = activeBlock.id;
            if (typeof window.deleteBlock === 'function' && confirm('Удалить этот блок?')) {
                window.deleteBlock(blockId);
            }
        }
    }

    focusNextBlock() {
        this.navigateBlocks('next');
    }

    focusPrevBlock() {
        this.navigateBlocks('prev');
    }

    navigateBlocks(direction) {
        const blocks = Array.from(document.querySelectorAll('.content-block'));
        const currentIndex = blocks.findIndex(block => block === this.currentActiveElement?.closest('.content-block'));
        
        let newIndex;
        if (direction === 'next') {
            newIndex = currentIndex < blocks.length - 1 ? currentIndex + 1 : 0;
        } else {
            newIndex = currentIndex > 0 ? currentIndex - 1 : blocks.length - 1;
        }
        
        if (blocks[newIndex]) {
            // Убираем предыдущую подсветку
            document.querySelectorAll('.content-block.keyboard-focus').forEach(block => {
                block.classList.remove('keyboard-focus');
            });
            
            // Добавляем подсветку и фокус
            blocks[newIndex].classList.add('keyboard-focus');
            blocks[newIndex].focus();
            blocks[newIndex].scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Убираем подсветку через 2 секунды
            setTimeout(() => {
                blocks[newIndex].classList.remove('keyboard-focus');
            }, 2000);
        }
    }

    openImageModal() {
        const activeImage = this.currentActiveElement?.closest('.content-block')?.querySelector('img');
        if (activeImage && typeof window.showImageModal === 'function') {
            const src = activeImage.src;
            const alt = activeImage.alt || 'Изображение';
            window.showImageModal(src, alt);
        }
    }

    swiperPrev() {
        if (typeof window.swiperPrev === 'function') {
            window.swiperPrev();
        }
    }

    swiperNext() {
        if (typeof window.swiperNext === 'function') {
            window.swiperNext();
        }
    }

    getActiveBlock() {
        return this.currentActiveElement?.closest('.content-block');
    }

    // === УТИЛИТЫ ===

    toggleShortcuts() {
        this.isEnabled = !this.isEnabled;
        this.updateStatusIndicator();
        this.showNotification(
            this.isEnabled ? 'Горячие клавиши включены' : 'Горячие клавиши выключены',
            this.isEnabled ? 'success' : 'warning'
        );
    }

    createStatusIndicator() {
        const indicator = document.createElement('div');
        indicator.id = 'keyboard-status-indicator';
        indicator.className = 'keyboard-status-indicator';
        indicator.innerHTML = '<i class="fas fa-keyboard"></i>Горячие клавиши';
        document.body.appendChild(indicator);
        
        // Скрываем индикатор через 3 секунды
        setTimeout(() => {
            indicator.style.opacity = '0';
            setTimeout(() => {
                if (indicator.parentNode) {
                    indicator.parentNode.removeChild(indicator);
                }
            }, 300);
        }, 3000);
    }

    updateStatusIndicator() {
        const indicator = document.getElementById('keyboard-status-indicator');
        if (indicator) {
            indicator.className = `keyboard-status-indicator ${this.isEnabled ? '' : 'disabled'}`;
            indicator.innerHTML = `<i class="fas fa-keyboard"></i>${this.isEnabled ? 'Горячие клавиши' : 'Клавиши отключены'}`;
            
            // Показываем индикатор на 2 секунды
            indicator.style.opacity = '1';
            setTimeout(() => {
                indicator.style.opacity = '0';
            }, 2000);
        }
    }

    showShortcutFeedback(combination, description) {
        // Показываем небольшую подсказку о выполненном действии
        const feedback = document.createElement('div');
        feedback.className = 'keyboard-shortcut-feedback';
        feedback.innerHTML = `
            <kbd>${combination.replace(/\+/g, '</kbd> + <kbd>')}</kbd>
            <span>${description}</span>
        `;
        
        // Добавляем стили для feedback
        if (!document.getElementById('keyboard-shortcut-styles')) {
            const styles = document.createElement('style');
            styles.id = 'keyboard-shortcut-styles';
            styles.textContent = `
                .keyboard-shortcut-feedback {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    background: rgba(0, 0, 0, 0.8);
                    color: white;
                    padding: 8px 12px;
                    border-radius: 6px;
                    font-size: 12px;
                    z-index: 10000;
                    animation: shortcutFadeInOut 2s ease-in-out forwards;
                    pointer-events: none;
                }
                
                .keyboard-shortcut-feedback kbd {
                    background: rgba(255, 255, 255, 0.2);
                    padding: 2px 4px;
                    border-radius: 3px;
                    font-size: 10px;
                }
                
                @keyframes shortcutFadeInOut {
                    0% { opacity: 0; transform: translateY(-10px); }
                    20% { opacity: 1; transform: translateY(0); }
                    80% { opacity: 1; transform: translateY(0); }
                    100% { opacity: 0; transform: translateY(-10px); }
                }
            `;
            document.head.appendChild(styles);
        }
        
        document.body.appendChild(feedback);
        
        // Удаляем feedback через 2 секунды
        setTimeout(() => {
            if (feedback.parentNode) {
                feedback.parentNode.removeChild(feedback);
            }
        }, 2000);
    }

    showNotification(message, type = 'info', duration = 3000) {
        if (typeof window.showNotification === 'function') {
            window.showNotification(message, type);
        } else {
            // Fallback уведомление
            console.log(`[${type.toUpperCase()}] ${message}`);
        }
    }
}

// Инициализация системы горячих клавиш
let keyboardShortcuts;

document.addEventListener('DOMContentLoaded', function() {
    // Небольшая задержка для полной загрузки других скриптов
    setTimeout(() => {
        keyboardShortcuts = new KeyboardShortcuts();
        window.keyboardShortcuts = keyboardShortcuts;
        console.log('🚀 Система горячих клавиш инициализирована');
    }, 500);
});

// Экспорт для использования в других модулях
if (typeof module !== 'undefined' && module.exports) {
    module.exports = KeyboardShortcuts;
}
