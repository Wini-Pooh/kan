
<style>
/* Кнопка возврата к канбану */
.back-to-kanban-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: #007bff;
    color: white;
    border-radius: 8px;
    text-decoration: none;
    margin-right: 15px;
    transition: all 0.2s ease;
    border: 2px solid transparent;
    vertical-align: middle;
}

.back-to-kanban-btn:hover {
    background: #0056b3;
    color: white;
    text-decoration: none;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
}

.back-to-kanban-btn:focus {
    outline: none;
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.back-to-kanban-btn i {
    font-size: 16px;
}

/* Мобильная обертка для шапки */
.mobile-header-wrapper {
    background: white;
    border-bottom: 1px solid #e9ecef;
    position: relative;
    z-index: 1000;
}

@media (max-width: 768px) {
    .mobile-header-wrapper {
        position: sticky;
        top: 0;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
}

.task-title-container {
    display: flex;
    align-items: center;
}

/* Стили для отображения файлов */
.content-display {
    padding: 20px;
    line-height: 1.6;
    font-size: 16px;
    min-height: 200px;
}

.editable-content {
    min-height: 150px;
    padding: 15px;
    border: 2px dashed transparent;
    border-radius: 8px;
    transition: all 0.3s ease;
    background: transparent;
    outline: none;
}

.editable-content:hover {
    border-color: #dee2e6;
    background: #f8f9fa;
}

.editable-content:focus {
    border-color: #007bff;
    background: #fff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.editable-content:empty::before {
    content: attr(data-placeholder);
    color: #adb5bd;
    pointer-events: none;
}

.file-block {
    margin: 15px 0;
    padding: 10px;
    border-radius: 8px;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
}

.file-block img {
    display: block;
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: transform 0.2s ease;
}

.file-block img:hover {
    transform: scale(1.02);
    cursor: pointer;
}

.file-block video {
    display: block;
    max-width: 100%;
    height: auto;
    border-radius: 8px;
}

.file-info {
    margin-top: 8px;
    font-size: 14px;
    color: #6c757d;
}

.file-info i {
    margin-right: 5px;
}

.document-block .card {
    border: 1px solid #dee2e6;
    transition: box-shadow 0.2s ease;
}

.document-block .card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.document-block a {
    color: #495057;
    text-decoration: none;
}

.document-block a:hover {
    color: #007bff;
}

/* Responsive для файлов */
@media (max-width: 768px) {
    .content-display {
        padding: 15px;
    }
    
    .file-block {
        margin: 10px 0;
        padding: 8px;
    }
    
    .editable-content {
        padding: 10px;
    }
}

/* Фиксированная шапка */
.task-header-fixed {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    background: white;
    border-bottom: 2px solid #e9ecef;
    z-index: 1000;
    padding: 15px 0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.task-content {
    margin-top: 80px;
    margin-bottom: 100px;
    padding: 20px;
}

/* Название задачи */
.task-title-container {
    position: relative;
}

.task-title {
    margin: 0;
    cursor: pointer;
    padding: 8px 12px;
    border-radius: 6px;
    transition: background-color 0.2s;
    color: #2c3e50;
    font-weight: 600;
}

.task-title:hover {
    background-color: #f8f9fa;
}

.task-title-input {
    font-size: 1.5rem;
    font-weight: 600;
    border: 2px solid #007bff;
    border-radius: 6px;
}

/* Статус квадрат */
.status-container {
    display: inline-block;
}

.status-square {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    border: 2px solid transparent;
    position: relative;
}

.status-square:hover {
    transform: scale(1.1);
    border-color: #007bff;
}

.status-square[data-priority="low"] {
    background: linear-gradient(135deg, #28a745, #20c997);
}

.status-square[data-priority="medium"] {
    background: linear-gradient(135deg, #ffc107, #fd7e14);
}

.status-square[data-priority="high"] {
    background: linear-gradient(135deg, #fd7e14, #dc3545);
}

.status-square[data-priority="urgent"] {
    background: linear-gradient(135deg, #dc3545, #e83e8c);
}

.status-square[data-priority="critical"] {
    background: linear-gradient(135deg, #6f42c1, #e83e8c);
}

.status-square[data-priority="blocked"] {
    background: linear-gradient(135deg, #6c757d, #495057);
}

/* Меню статуса */
.status-menu {
    position: absolute;
    top: 100%;
    left: 50%;
    transform: translateX(-50%);
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    padding: 12px;
    min-width: 200px;
    z-index: 1001;
    margin-top: 8px;
}

.status-menu-header {
    font-weight: 600;
    margin-bottom: 8px;
    color: #495057;
    font-size: 14px;
}

.priority-option {
    display: flex;
    align-items: center;
    padding: 8px 12px;
    cursor: pointer;
    border-radius: 6px;
    transition: background-color 0.2s;
    gap: 10px;
}

.priority-option:hover {
    background-color: #f8f9fa;
}

.priority-circle {
    width: 20px;
    height: 20px;
    border-radius: 50%;
}

.priority-circle.low { background: #28a745; }
.priority-circle.medium { background: #ffc107; }
.priority-circle.high { background: #fd7e14; }
.priority-circle.urgent { background: #dc3545; }
.priority-circle.critical { background: #6f42c1; }
.priority-circle.blocked { background: #6c757d; }

/* Иконка времени */
.time-icon {
    font-size: 24px;
    color: #6c757d;
    cursor: pointer;
    transition: color 0.2s;
    padding: 8px;
    border-radius: 6px;
}

.time-icon:hover {
    color: #007bff;
    background-color: #f8f9fa;
}

/* Календарь */
.date-picker-container {
    position: absolute;
    top: 100%;
    left: 50%;
    transform: translateX(-50%);
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    padding: 16px;
    min-width: 280px;
    z-index: 1001;
    margin-top: 8px;
}

.date-picker-header {
    font-weight: 600;
    margin-bottom: 12px;
    color: #495057;
    font-size: 14px;
    text-align: center;
}

.date-field {
    margin-bottom: 12px;
}

.date-field label {
    display: block;
    margin-bottom: 4px;
    font-size: 13px;
    font-weight: 500;
    color: #495057;
}

.date-field input[type="date"] {
    width: 100%;
    padding: 6px 8px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    font-size: 13px;
}

.date-field input[type="date"]:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    outline: 0;
}

.date-picker-actions {
    margin-top: 12px;
    display: flex;
    gap: 8px;
    justify-content: flex-end;
}

/* Исполнитель */
.assignee-container {
    display: inline-block;
    position: relative;
}

/* Кнопки действий */
.task-actions {
    display: flex;
    gap: 8px;
    align-items: center;
}

.action-btn {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 16px;
    color: #495057;
}

.action-btn:hover {
    background: #e9ecef;
    border-color: #adb5bd;
    color: #212529;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.action-btn:active {
    transform: translateY(0);
    box-shadow: none;
}

.print-btn:hover {
    background: #e3f2fd;
    border-color: #2196f3;
    color: #1976d2;
}

.pdf-btn:hover {
    background: #fff3e0;
    border-color: #ff9800;
    color: #f57c00;
}

/* Состояние загрузки для PDF */
.pdf-btn.loading {
    pointer-events: none;
    opacity: 0.6;
}

.pdf-btn.loading i {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.assignee-container {
    display: inline-block;
}

.assignee-avatar, .no-assignee {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    padding: 6px 12px;
    border-radius: 6px;
    transition: background-color 0.2s;
    border: 1px solid transparent;
}

.assignee-avatar:hover, .no-assignee:hover {
    background-color: #f8f9fa;
    border-color: #dee2e6;
}

.user-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
}

.no-assignee {
    color: #6c757d;
    font-style: italic;
}

.no-assignee i {
    font-size: 20px;
}

/* Меню исполнителей */
.assignee-menu {
    position: absolute;
    top: 100%;
    right: 0;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    padding: 12px;
    min-width: 220px;
    z-index: 1001;
    margin-top: 8px;
}

.assignee-menu-header {
    font-weight: 600;
    margin-bottom: 8px;
    color: #495057;
    font-size: 14px;
}

.assignee-option {
    display: flex;
    align-items: center;
    padding: 8px 12px;
    cursor: pointer;
    border-radius: 6px;
    transition: background-color 0.2s;
    gap: 10px;
}

.assignee-option:hover {
    background-color: #f8f9fa;
}

.user-avatar-small {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    overflow: hidden;
    background: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
}

.user-avatar-small img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.user-avatar-small i {
    color: #6c757d;
    font-size: 14px;
}

/* Компактный селектор приоритетов */
.priority-selector-compact {
  padding: 12px 0;
    border-bottom: none;
    margin-bottom: 0;
    background: rgba(248, 249, 250, 0.5);
    border-radius: 6px;
    padding: 12px;
}

.priority-selector-header {
    font-size: 12px;
    color: #6c757d;
    margin-bottom: 10px;
    font-weight: 600;
    text-align: center;
}

.priority-circles-row {
    display: flex;
    gap: 4px;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
    padding: 8px;
    background: rgba(255, 255, 255, 0.8);
    border-radius: 8px;
}

.priority-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
    transition: all 0.2s ease;
}

.priority-item:hover {
    background: rgba(0, 123, 255, 0.1);
}

.priority-item.active {
    background: rgba(0, 123, 255, 0.2);
}

.priority-item.active .priority-label {
    color: #007bff;
    font-weight: 600;
}

.priority-label {
    font-size: 10px;
    color: #6c757d;
    font-weight: 500;
    text-align: center;
    min-height: 12px;
    line-height: 1;
}

.priority-circle-option {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid #fff;
    position: relative;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    margin: 2px;
}

.priority-circle-option:hover {
    transform: scale(1.15);
    box-shadow: 0 4px 16px rgba(0,0,0,0.3);
    border-width: 3px;
}

.priority-circle-option.active {
    border-color: #007bff;
    box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.25);
    transform: scale(1.1);
    border-width: 3px;
}

.priority-circle-option.low { 
    background-color: #28a745 !important;
}

.priority-circle-option.medium { 
    background-color: #ffc107 !important;
}

.priority-circle-option.high { 
    background-color: #fd7e14 !important;
}

.priority-circle-option.urgent { 
    background-color: #dc3545 !important;
}

.priority-circle-option.critical { 
    background-color: #6f42c1 !important;
}

.priority-circle-option.blocked { 
    background-color: #6c757d !important;
}

/* Responsive */
@media (max-width: 768px) {
    .task-header-fixed {
        padding: 8px 0;
        position: relative;
    }
    
    .task-header-fixed .container-fluid {
        padding: 0 15px;
    }
    
    .task-header-fixed .row {
        margin: 0;
        flex-direction: column !important;
        gap: 10px;
    }
    
    .task-header-fixed .col-md-4,
    .task-header-fixed .col-4 {
        flex: none !important;
        width: 100% !important;
        max-width: 100% !important;
        margin-bottom: 0;
    }
    
    .task-title-container {
        display: flex;
        align-items: center;
        width: 100%;
    }
    
    .task-title {
        font-size: 1.1rem;
        flex: 1;
        margin: 0;
        padding: 6px 8px;
    }
    
    .back-to-kanban-btn {
        width: 35px;
        height: 35px;
        margin-right: 10px;
        flex-shrink: 0;
    }
    
    .back-to-kanban-btn i {
        font-size: 14px;
    }
    
    /* Центральная секция с приоритетом и действиями */
    .task-header-fixed .col-md-4.text-center,
    .task-header-fixed .col-4.text-center {
        text-align: left !important;
    }
    
    .task-header-fixed .col-md-4.text-center .d-flex,
    .task-header-fixed .col-4.text-center .d-flex {
        justify-content: flex-start !important;
        align-items: center;
        flex-wrap: nowrap;
        gap: 12px;
        overflow-x: auto;
        padding-bottom: 4px;
    }
    
    /* Правая секция с исполнителем */
    .task-header-fixed .col-md-4.text-end,
    .task-header-fixed .col-4.text-end {
        text-align: left !important;
    }
    
    .task-header-fixed .col-md-4.text-end .d-flex,
    .task-header-fixed .col-4.text-end .d-flex {
        justify-content: flex-start !important;
        align-items: center;
        gap: 12px;
    }
    
    .assignee-name {
        display: none;
    }
    
    /* Упрощаем действия на мобильных */
    .task-actions {
        display: flex;
        gap: 8px;
        flex-shrink: 0;
    }
    
    .action-btn {
        width: 32px;
        height: 32px;
        flex-shrink: 0;
    }
    
    .action-btn svg {
        width: 14px;
        height: 14px;
    }
    
    /* Статус квадрат */
    .status-square {
        width: 32px;
        height: 32px;
        margin: 0;
        flex-shrink: 0;
    }
    
    /* Иконка времени */
    .time-icon {
        font-size: 16px;
        padding: 6px;
        cursor: pointer;
        border-radius: 6px;
        transition: background-color 0.2s;
    }
    
    .time-icon:hover {
        background-color: #f8f9fa;
    }
    
    /* Статус квадрат */
    .status-square {
        width: 35px;
        height: 35px;
        margin: 0;
    }
}

/* Индикаторы сохранения */
.save-status {
    font-size: 14px;
    margin-left: 8px;
    display: none;
    vertical-align: middle;
}

.save-status.saving {
    color: #007bff;
}

.save-status.saved {
    color: #28a745;
}

.save-status.error {
    color: #dc3545;
}

/* Уведомления */
.notification {
    position: fixed;
    top: 100px;
    right: -300px;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    padding: 16px 20px;
    z-index: 9999;
    transition: right 0.3s ease;
    display: flex;
    align-items: center;
    gap: 10px;
    max-width: 300px;
}

/* Стили для фотогалереи */
.gallery-block .block-content {
    padding: 0;
}

.gallery-container {
    width: 100%;
}

.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 10px;
    margin-bottom: 15px;
}

.gallery-item {
    position: relative;
    cursor: pointer;
    border-radius: 8px;
    overflow: hidden;
    aspect-ratio: 1;
    background: #f8f9fa;
}

.gallery-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.gallery-item:hover img {
    transform: scale(1.05);
}

.image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.gallery-item:hover .image-overlay,
.image-container:hover .image-overlay {
    opacity: 1;
}

.image-overlay i {
    color: white;
    font-size: 24px;
}

.gallery-caption {
    padding: 15px;
    border-top: 1px solid #e9ecef;
}

/* Стили для отдельных изображений */
.image-block .image-container {
    position: relative;
    cursor: pointer;
    border-radius: 8px;
    overflow: hidden;
}

.image-block .block-image {
    width: 100%;
    height: auto;
    display: block;
    transition: transform 0.3s ease;
}

.image-container:hover .block-image {
    transform: scale(1.02);
}

.image-caption {
    margin-top: 10px;
    padding: 8px 12px;
    font-style: italic;
    color: #6c757d;
    border-left: 3px solid #e9ecef;
    background: #f8f9fa;
}

/* Модальное окно для просмотра изображений */
.image-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10000;
    cursor: pointer;
}

.image-modal .modal-content {
    position: relative;
    max-width: 90%;
    max-height: 90%;
    cursor: default;
}

.modal-image {
    width: 100%;
    height: auto;
    max-width: 90vw;
    max-height: 80vh;
    object-fit: contain;
    border-radius: 8px;
}

.close-modal {
    position: absolute;
    top: -40px;
    right: 0;
    color: white;
    font-size: 30px;
    cursor: pointer;
    z-index: 10001;
}

.close-modal:hover {
    color: #ccc;
}

.modal-caption {
    color: white;
    text-align: center;
    margin-top: 10px;
    font-size: 14px;
}

/* Стили для компактной панели форматирования */
.format-toolbar {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
    font-size: 14px;
}

.format-toolbar-content {
    display: flex;
    align-items: center;
    gap: 2px;
    padding: 2px;
}

.format-group {
    position: relative;
    display: inline-block;
}

.format-btn {
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 6px 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 12px;
    display: flex;
    align-items: center;
    gap: 4px;
    min-height: 28px;
    white-space: nowrap;
}

.format-btn:hover {
    background: #f5f5f5;
    border-color: #999;
}

.format-btn:active {
    background: #e0e0e0;
}

.format-dropdown-btn {
    border-radius: 4px;
    position: relative;
    min-width: 32px;
    justify-content: space-between;
}

.format-arrow {
    font-size: 10px;
    margin-left: 4px;
    transition: transform 0.2s ease;
}

.format-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    background: white;
    border: 1px solid #ddd;
    border-radius: 6px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 10001;
    min-width: 160px;
    max-width: 200px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.2s ease;
    margin-top: 2px;
}

.format-dropdown.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.format-dropdown .format-btn {
    width: 100%;
    border: none;
    border-radius: 0;
    padding: 8px 12px;
    text-align: left;
    justify-content: flex-start;
    border-bottom: 1px solid #f0f0f0;
}

.format-dropdown .format-btn:last-child {
    border-bottom: none;
    border-radius: 0 0 6px 6px;
}

.format-dropdown .format-btn:first-child {
    border-radius: 6px 6px 0 0;
}

.format-dropdown .format-btn:hover {
    background: #007bff;
    color: white;
}

.format-color-dropdown {
    min-width: 140px;
}

.color-btn {
    display: flex;
    align-items: center;
    gap: 8px;
}

.color-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 1px solid #ccc;
    flex-shrink: 0;
}

.format-divider {
    width: 1px;
    height: 24px;
    background: #ddd;
    margin: 0 4px;
    flex-shrink: 0;
}

/* Адаптивность для панели форматирования */
@media (max-width: 768px) {
    .format-toolbar {
        max-width: 280px;
        font-size: 12px;
    }
    
    .format-btn {
        padding: 4px 6px;
        font-size: 11px;
        min-height: 26px;
    }
    
    .format-dropdown {
        min-width: 140px;
        font-size: 11px;
    }
    
    .format-dropdown .format-btn {
        padding: 6px 10px;
    }
}

@media (max-width: 480px) {
    .format-toolbar {
        max-width: 240px;
        font-size: 11px;
    }
    
    .format-toolbar-content {
        gap: 1px;
    }
    
    .format-btn {
        padding: 3px 5px;
        font-size: 10px;
        min-height: 24px;
    }
    
    .format-dropdown {
        min-width: 120px;
        font-size: 10px;
    }
    
    .format-arrow {
        font-size: 8px;
    }
}

/* Улучшенные стили для лучшего UX */
.format-btn:focus {
    outline: 2px solid #007bff;
    outline-offset: 1px;
}

.format-dropdown-btn:focus {
    outline: 2px solid #007bff;
    outline-offset: 1px;
}

.format-dropdown .format-btn:focus {
    outline: none;
    background: #007bff;
    color: white;
}

/* Стили для активных состояний форматирования */
.format-btn.active {
    background: #007bff;
    color: white;
    border-color: #0056b3;
}

.format-btn.active:hover {
    background: #0056b3;
}

/* Анимации для выпадающих меню */
@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.format-dropdown.show {
    animation: fadeInDown 0.2s ease-out;
}

/* Автосохранение индикатор */
.auto-save-indicator {
    position: fixed;
    top: 20px;
    right: 20px;
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 8px 12px;
    border-radius: 4px;
    font-size: 12px;
    z-index: 9999;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.auto-save-indicator.show {
    opacity: 1;
}

/* Стили для контентного полотна */
.task-content-canvas {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    overflow: hidden;
    margin-bottom: 20px;
}

.content-toolbar {
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    padding: 12px 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.toolbar-group {
    display: flex;
    gap: 8px;
}

.toolbar-btn {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 8px 12px;
    cursor: pointer;
    transition: all 0.2s;
    color: #495057;
    font-size: 14px;
}

.toolbar-btn:hover {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.content-area {
    min-height: 400px;
    padding: 20px;
    position: relative;
}

.empty-state {
    text-align: center;
    color: #6c757d;
    padding: 60px 20px;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 16px;
    color: #dee2e6;
}

.content-block {
    position: relative;
    margin-bottom: 20px;
    border: 2px solid transparent;
    border-radius: 8px;
    transition: all 0.2s;
    background: white;
}

.content-block:hover {
    border-color: #007bff;
    box-shadow: 0 2px 8px rgba(0,123,255,0.15);
}

.content-block.active {
    border-color: #007bff;
    box-shadow: 0 4px 12px rgba(0,123,255,0.2);
}

.block-content {
    padding: 16px;
}

.text-content {
    line-height: 1.6;
    color: #495057;
    font-size: 16px;
}

.text-editor {
    width: 100%;
    min-height: 120px;
    border: none;
    outline: none;
    resize: vertical;
    font-family: inherit;
    font-size: 16px;
    line-height: 1.6;
    padding: 0;
}

.image-content img {
    max-width: 100%;
    height: auto;
    border-radius: 6px;
}

.video-content video {
    width: 100%;
    border-radius: 6px;
}

.file-content {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 6px;
}

.file-icon {
    width: 32px;
    height: 32px;
    background: #007bff;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 14px;
}

.file-info {
    flex: 1;
}

.file-name {
    font-weight: 500;
    color: #495057;
}

.file-size {
    font-size: 12px;
    color: #6c757d;
}

.block-actions {
    position: absolute;
    top: 8px;
    right: 8px;
    display: flex;
    gap: 4px;
    opacity: 0;
    transition: opacity 0.2s;
    background: rgba(255,255,255,0.95);
    border-radius: 6px;
    padding: 4px;
}

.content-block:hover .block-actions {
    opacity: 1;
}

.action-btn {
    background: none;
    border: none;
    padding: 6px;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.2s;
    color: #6c757d;
    font-size: 12px;
}

.action-btn:hover {
    background: #e9ecef;
}

.edit-btn:hover {
    color: #007bff;
}

.delete-btn:hover {
    color: #dc3545;
}

/* Боковая панель */
.task-sidebar {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    padding: 20px;
}

.sidebar-section {
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e9ecef;
}

.sidebar-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.sidebar-title {
    font-weight: 600;
    color: #495057;
    margin-bottom: 16px;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.detail-item label {
    font-weight: 500;
    color: #6c757d;
    font-size: 14px;
}

.detail-item span {
    color: #495057;
    font-size: 14px;
}

.assignee-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-avatar-small {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #007bff;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 600;
}

/* Модальные окна загрузки */
.upload-area {
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    padding: 40px 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
    background: #f8f9fa;
}

.upload-area:hover {
    border-color: #007bff;
    background: #e3f2fd;
}

.upload-area i {
    font-size: 48px;
    color: #007bff;
    margin-bottom: 16px;
}

.upload-area p {
    color: #6c757d;
    margin: 0;
}

.upload-preview {
    margin-top: 16px;
    text-align: center;
}

.upload-preview img {
    max-width: 100%;
    max-height: 200px;
    border-radius: 6px;
}

/* Анимации */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.content-block {
    animation: fadeIn 0.3s ease;
}
}

.notification.show {
    right: 20px;
}

.notification.success {
    border-left: 4px solid #28a745;
}

.notification.error {
    border-left: 4px solid #dc3545;
}

.notification i {
    font-size: 18px;
}

.notification.success i {
    color: #28a745;
}

.notification.error i {
    color: #dc3545;
}

.notification span {
    font-weight: 500;
    color: #495057;
}

/* Стили для полотна контента */
.task-canvas {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-top: 20px;
    overflow: hidden;
}

.canvas-toolbar {
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    padding: 16px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.toolbar-section {
    display: flex;
    align-items: center;
    gap: 16px;
}

.toolbar-title {
    font-weight: 600;
    color: #495057;
    font-size: 16px;
}

.toolbar-buttons {
    display: flex;
    gap: 12px;
}

.toolbar-btn {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 8px 16px;
    display: flex;
    align-items: center;
    gap: 8px;
    color: #6c757d;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s;
}

.toolbar-btn:hover {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.toolbar-btn.save-btn {
    background: #28a745;
    color: white;
    border-color: #28a745;
}

.toolbar-btn.save-btn:hover {
    background: #218838;
    border-color: #1e7e34;
}

.content-canvas {
    min-height: 500px;
    padding: 20px;
    background: white;
}

.empty-canvas {
    text-align: center;
    padding: 80px 20px;
    color: #6c757d;
}

.empty-icon {
    font-size: 64px;
    color: #dee2e6;
    margin-bottom: 24px;
}

.empty-canvas h3 {
    color: #495057;
    margin-bottom: 16px;
}

.empty-canvas p {
    color: #6c757d;
    margin-bottom: 32px;
    font-size: 16px;
}

/* Блоки контента */
.content-block {
    position: relative;
    margin-bottom: 20px;
    border: 2px solid transparent;
    border-radius: 8px;
    transition: all 0.2s;
}

.content-block:hover {
    border-color: #007bff;
}

.content-block.active {
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
}

/* Текстовые блоки */
.text-block .block-content {
    padding: 16px;
    min-height: 60px;
}

.text-content {
    font-size: 16px;
    line-height: 1.6;
    color: #495057;
    outline: none;
    min-height: 40px;
}

.text-content:focus {
    outline: none;
}

.text-content:empty:before {
    content: "Начните писать...";
    color: #6c757d;
    font-style: italic;
}

/* Поддержка цветного и стилизованного текста */
.text-content span[style] {
    color: inherit;
    font-weight: inherit;
    font-style: inherit;
    text-decoration: inherit;
}

.text-content span[style*="color"] {
    /* Явно разрешаем цвет из inline стилей */
}

.text-content span[style*="font-weight: bold"],
.text-content span[style*="font-weight:bold"] {
    font-weight: bold !important;
}

.text-content span[style*="font-style: italic"],
.text-content span[style*="font-style:italic"] {
    font-style: italic !important;
}

.text-content span[style*="text-decoration: underline"],
.text-content span[style*="text-decoration:underline"] {
    text-decoration: underline !important;
}

.text-content span[style*="text-decoration: line-through"],
.text-content span[style*="text-decoration:line-through"] {
    text-decoration: line-through !important;
}

/* Блоки изображений */
.image-block .block-content {
    text-align: center;
    padding: 16px;
}

.image-block img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.image-caption {
    margin-top: 12px;
    font-size: 14px;
    color: #6c757d;
    font-style: italic;
}

/* Блоки видео */
.video-block .block-content {
    text-align: center;
    padding: 16px;
}

.video-block video {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
}

/* Блоки файлов */
.file-block .block-content {
    padding: 16px;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    background: #f8f9fa;
}

.file-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.file-icon {
    width: 48px;
    height: 48px;
    background: #007bff;
    color: white;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.file-details h4 {
    margin: 0;
    font-size: 16px;
    color: #495057;
}

.file-details p {
    margin: 4px 0 0 0;
    font-size: 14px;
    color: #6c757d;
}

/* Панель инструментов блока */
.block-toolbar {
    position: absolute;
    top: -12px;
    right: 16px;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 4px;
    display: flex;
    gap: 4px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    opacity: 0;
    visibility: hidden;
    transition: all 0.2s ease;
    z-index: 20;
}

/* Добавляем невидимую область для плавного перехода курсора */
.block-toolbar::before {
    content: '';
    position: absolute;
    top: 100%;
    left: -10px;
    right: -10px;
    height: 15px;
    background: transparent;
    z-index: -1;
}

.content-block:hover .block-toolbar,
.content-block.active .block-toolbar,
.swiper-block:hover .block-toolbar,
.swiper-block.active .block-toolbar,
.block-toolbar:hover {
    opacity: 1;
    visibility: visible;
}

/* Принудительное отображение панели инструментов в разблокированном режиме */
body:not(.content-locked) .content-block:hover .block-toolbar,
body:not(.content-locked) .content-block.active .block-toolbar,
body:not(.content-locked) .swiper-block:hover .block-toolbar,
body:not(.content-locked) .swiper-block.active .block-toolbar,
body:not(.content-locked) .block-toolbar:hover {
    opacity: 1 !important;
    visibility: visible !important;
    pointer-events: auto !important;
    display: flex !important;
}

/* Расширяем область hover для более стабильного отображения */
.content-block::before {
    content: '';
    position: absolute;
    top: -20px;
    right: 0;
    width: 150px;
    height: 20px;
    background: transparent;
    z-index: 1;
    pointer-events: none;
}

.content-block:hover::before {
    pointer-events: auto;
}

.block-btn {
    background: none;
    border: none;
    padding: 6px 8px;
    border-radius: 4px;
    color: #6c757d;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 14px;
}

.block-btn:hover {
    background: #f8f9fa;
    color: #007bff;
}

.block-btn.delete-btn:hover {
    color: #dc3545;
}

/* Drag and drop */
.content-canvas.drag-over {
    background: #e3f2fd;
    border: 2px dashed #007bff;
}

.drop-zone {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,123,255,0.1);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.drop-zone.active {
    display: flex;
}

.drop-zone-content {
    background: white;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    text-align: center;
}

.drop-zone-content i {
    font-size: 48px;
    color: #007bff;
    margin-bottom: 16px;
}

.drop-zone-content h3 {
    color: #495057;
    margin-bottom: 8px;
}

.drop-zone-content p {
    color: #6c757d;
    margin: 0;
}

/* Фиксированная нижняя панель */
.bottom-toolbar-fixed {
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(222, 226, 230, 0.8);
    border-radius: 30px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.12);
    z-index: 1000;
    padding: 8px 16px;
}

.bottom-toolbar-content {
    display: flex;
    align-items: center;
    gap: 12px;
    justify-content: space-between;
    width: 100%;
    min-width: 400px;
}

.toolbar-icon {
    width: 48px;
    height: 48px;
    background: rgba(248, 249, 250, 0.8);
    border: 1px solid rgba(222, 226, 230, 0.6);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    color: #6c757d;
    font-size: 18px;
    position: relative;
    overflow: hidden;
}

.toolbar-icon::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, #007bff, #0056b3);
    opacity: 0;
    transition: opacity 0.2s ease;
    border-radius: 50%;
}

.toolbar-icon i {
    position: relative;
    z-index: 1;
    transition: color 0.2s ease;
}

.toolbar-icon:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,123,255,0.25);
    border-color: #007bff;
}

.toolbar-icon:hover::before {
    opacity: 1;
}

.toolbar-icon:hover i {
    color: white;
}

.toolbar-icon:active {
    transform: translateY(0);
}

/* Стили для заблокированного состояния */
.toolbar-icon.disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
}

.toolbar-icon.disabled:hover {
    transform: none;
    box-shadow: none;
    border-color: rgba(222, 226, 230, 0.6);
}

.toolbar-icon.disabled:hover::before {
    opacity: 0;
}

.toolbar-icon.disabled:hover i {
    color: #6c757d;
}

/* Кнопка блокировки всегда должна оставаться активной */
.toolbar-icon.lock-icon {
    opacity: 1 !important;
    cursor: pointer !important;
    pointer-events: auto !important;
}

/* Стили для аватара создателя в toolbar */
.creator-avatar-container {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: auto;
}

.creator-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #28a745;
    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
    transition: all 0.2s ease;
}

.creator-avatar:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
}

/* Стили для исполнителя в toolbar */
.assignee-container {
    flex-shrink: 0;
    margin-left: auto;
}

.assignee-avatar-toolbar, .no-assignee-toolbar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    background: rgba(248, 249, 250, 0.8);
    border: 4px solid #007bff;
    position: relative;
    overflow: hidden;
}

.assignee-avatar-toolbar:hover, .no-assignee-toolbar:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,123,255,0.25);
    border-color: #0056b3;
}

.assignee-avatar-img {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    object-fit: cover;
}

.no-assignee-toolbar {
    background: rgba(248, 249, 250, 0.8);
    color: #6c757d;
    font-size: 18px;
}

.no-assignee-toolbar:hover {
    background: #007bff;
    color: white;
}

/* Стили для цветной обводки аватара по приоритетам */
.assignee-avatar-toolbar[data-priority="low"] {
    border-color: #28a745 !important;
    box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.2);
}

.assignee-avatar-toolbar[data-priority="medium"] {
    border-color: #ffc107 !important;
    box-shadow: 0 0 0 2px rgba(255, 193, 7, 0.2);
}

.assignee-avatar-toolbar[data-priority="high"] {
    border-color: #fd7e14 !important;
    box-shadow: 0 0 0 2px rgba(253, 126, 20, 0.2);
}

.assignee-avatar-toolbar[data-priority="urgent"] {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.2);
}

.assignee-avatar-toolbar[data-priority="critical"] {
    border-color: #6f42c1 !important;
    box-shadow: 0 0 0 2px rgba(111, 66, 193, 0.2);
}

.assignee-avatar-toolbar[data-priority="blocked"] {
    border-color: #6c757d !important;
    box-shadow: 0 0 0 2px rgba(108, 117, 125, 0.2);
}

/* Эффекты при наведении с учетом приоритетов */
.assignee-avatar-toolbar[data-priority="low"]:hover {
    border-color: #1e7e34 !important;
    box-shadow: 0 6px 20px rgba(40, 167, 69, 0.3);
}

.assignee-avatar-toolbar[data-priority="medium"]:hover {
    border-color: #d39e00 !important;
    box-shadow: 0 6px 20px rgba(255, 193, 7, 0.3);
}

.assignee-avatar-toolbar[data-priority="high"]:hover {
    border-color: #e55812 !important;
    box-shadow: 0 6px 20px rgba(253, 126, 20, 0.3);
}

.assignee-avatar-toolbar[data-priority="urgent"]:hover {
    border-color: #bd2130 !important;
    box-shadow: 0 6px 20px rgba(220, 53, 69, 0.3);
}

.assignee-avatar-toolbar[data-priority="critical"]:hover {
    border-color: #59359a  !important;
    box-shadow: 0 6px 20px rgba(111, 66, 193, 0.3);
}

.assignee-avatar-toolbar[data-priority="blocked"]:hover {
    border-color: #545b62 !important;
    box-shadow: 0 6px 20px rgba(108, 117, 125, 0.3);
}

/* Центральная группа кнопок */
.toolbar-center-group {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
    justify-content: center;
}

/* Специальные стили для assignee-menu в toolbar */
.bottom-toolbar-fixed .assignee-menu {
    position: absolute;
    bottom: 100%;
    right: 0;
    top: auto;
    margin-top: 0;
    margin-bottom: 8px;
}

/* Стили для иконки блокировки */
.toolbar-icon.lock-icon {
    background: rgba(220, 53, 69, 0.1);
    border-color: rgba(220, 53, 69, 0.3);
    color: #dc3545;
}

.toolbar-icon.lock-icon.unlocked {
    background: rgba(40, 167, 69, 0.1);
    border-color: rgba(40, 167, 69, 0.3);
    color: #28a745;
}

.toolbar-icon.lock-icon::before {
    background: linear-gradient(135deg, #dc3545, #c82333);
}

.toolbar-icon.lock-icon.unlocked::before {
    background: linear-gradient(135deg, #28a745, #218838);
}

/* Заблокированное состояние для контента */
.content-locked .content-canvas {
    pointer-events: none;
}

/* Исключения для интерактивных элементов в заблокированном режиме */
.content-locked .content-canvas img,
.content-locked .content-canvas .swiper-navigation,
.content-locked .content-canvas .swiper-pagination-bullet,
.content-locked .content-canvas .download-btn,
.content-locked .content-canvas .btn[href],
.content-locked .content-canvas a[href],
.content-locked .content-canvas .file-download-link {
    pointer-events: auto !important;
    cursor: pointer !important;
}

/* Исключения для блоков слайдеров и файлов */
.content-locked .content-canvas .swiper-block,
.content-locked .content-canvas .file-grid-block,
.content-locked .content-canvas .image-block,
.content-locked .content-canvas .document-block {
    pointer-events: auto !important;
}

/* Элементы слайдера должны работать в заблокированном режиме */
.content-locked .swiper-navigation,
.content-locked .swiper-pagination,
.content-locked .swiper-pagination-bullet,
.content-locked .swiper-prev,
.content-locked .swiper-next {
    pointer-events: auto !important;
    cursor: pointer !important;
}

/* Интерактивные элементы файлов */
.content-locked .document-actions .btn,
.content-locked .file-grid-item .download-btn,
.content-locked .file-grid-item a[href] {
    pointer-events: auto !important;
    cursor: pointer !important;
}

/* Но блокируем редактирование внутри этих блоков */
.content-locked .content-canvas .swiper-block .block-toolbar,
.content-locked .content-canvas .file-grid-block .block-toolbar,
.content-locked .content-canvas .image-block .block-toolbar,
.content-locked .content-canvas .document-block .block-toolbar {
    pointer-events: none !important;
    display: none !important;
}

.content-locked .editable-content {
    cursor: default;
}

.content-locked .block-actions {
    display: none !important;
}

.content-locked .block-toolbar {
    display: none !important;
}

/* Исключения для навигационных кнопок в заблокированном режиме */
.content-locked .back-to-kanban-btn,
.content-locked .print-btn,
.content-locked .pdf-btn {
    pointer-events: auto !important;
    cursor: pointer !important;
}

/* Модальные окна должны работать поверх заблокированного контента */
.modal,
.modal-backdrop,
.modal-dialog,
.modal-content {
    pointer-events: auto !important;
    z-index: 9999 !important;
}

/* Кнопки закрытия модальных окон */
.modal .btn-close,
.modal .close,
.modal [data-bs-dismiss="modal"] {
    pointer-events: auto !important;
    cursor: pointer !important;
}

/* Исключения для заголовка с кнопками навигации */
.content-locked .task-header-fixed {
    pointer-events: auto !important;
}

/* Исключения для всех кнопок действий в заголовке */
.content-locked .task-header-fixed .back-to-kanban-btn,
.content-locked .task-header-fixed .print-btn,
.content-locked .task-header-fixed .pdf-btn,
.content-locked .task-header-fixed .action-btn {
    pointer-events: auto !important;
    cursor: pointer !important;
}

/* Но блокируем редактирование внутри заголовка */
.content-locked .task-header-fixed .editable-content,
.content-locked .task-header-fixed .task-title,
.content-locked .task-header-fixed [contenteditable] {
    pointer-events: none !important;
}

.content-locked .drag-handle {
    display: none !important;
}

.content-locked .content-block:hover {
    cursor: default;
    transform: none;
    box-shadow: none;
}

.content-locked .content-block:hover .block-toolbar {
    display: none !important;
}

.content-locked .content-block.active {
    outline: none;
    box-shadow: none;
}

.content-locked .content-block.active .block-toolbar {
    display: none !important;
}

/* Заблокированное состояние для заголовка */
.content-locked .task-title {
    cursor: default !important;
    pointer-events: none;
}

.content-locked .status-square {
    cursor: default !important;
    pointer-events: none;
}

.content-locked .status-square:hover {
    transform: none !important;
}

.content-locked .time-icon {
    cursor: default !important;
    pointer-events: none;
}

.content-locked .time-icon:hover {
    transform: none !important;
    color: #6c757d !important;
}

.content-locked .assignee-avatar,
.content-locked .no-assignee {
    cursor: default !important;
    pointer-events: none;
}

.content-locked .assignee-avatar:hover,
.content-locked .no-assignee:hover {
    transform: none !important;
}

.content-locked .page-actions {
    display: none !important;
}

.content-locked .page-content[onclick] {
    pointer-events: none !important;
}

/* Разрешаем выделение и копирование текста в заблокированном режиме, но запрещаем редактирование */
.content-locked .text-content,
.content-locked .image-caption,
.content-locked p,
.content-locked div,
.content-locked span,
.content-locked h1,
.content-locked h2,
.content-locked h3,
.content-locked h4,
.content-locked h5,
.content-locked h6 {
    user-select: text !important;
    cursor: text !important;
    pointer-events: none !important;
}

/* Запрещаем редактирование всех contenteditable элементов */
.content-locked [contenteditable],
.content-locked .editable-content,
.content-locked .text-block,
.content-locked .content-block[contenteditable] {
    -webkit-user-modify: read-only !important;
    -moz-user-modify: read-only !important;
    user-modify: read-only !important;
    pointer-events: none !important;
    cursor: default !important;
}

/* Исключения для навигационных кнопок должны быть приоритетнее */
.content-locked .back-to-kanban-btn,
.content-locked .print-btn,
.content-locked .pdf-btn,
.content-locked .action-btn,
.content-locked .back-to-kanban-btn *,
.content-locked .print-btn *,
.content-locked .pdf-btn *,
.content-locked .action-btn * {
    -webkit-user-modify: inherit !important;
    -moz-user-modify: inherit !important;
    user-modify: inherit !important;
    pointer-events: auto !important;
    cursor: pointer !important;
}

/* Полное отключение всех событий редактирования */
.content-locked [contenteditable="false"],
.content-locked .text-content,
.content-locked .content-block {
    pointer-events: none !important;
    -webkit-user-modify: read-only !important;
    -moz-user-modify: read-only !important;
    user-modify: read-only !important;
}

/* Исключения для навигационных кнопок в любом месте */
.content-locked .back-to-kanban-btn,
.content-locked .print-btn,
.content-locked .pdf-btn,
.content-locked .action-btn {
    pointer-events: auto !important;
    cursor: pointer !important;
    -webkit-user-modify: inherit !important;
    -moz-user-modify: inherit !important;
    user-modify: inherit !important;
}

/* Специальное правило для разрешения выделения текста без редактирования */
.content-locked .text-content *,
.content-locked .content-block * {
    user-select: text !important;
    -webkit-user-select: text !important;
    -moz-user-select: text !important;
    -ms-user-select: text !important;
}

/* Запрещаем фокус и редактирование */
.content-locked *[contenteditable]:focus,
.content-locked .editable-content:focus,
.content-locked .text-block:focus {
    outline: none !important;
    border: none !important;
    box-shadow: none !important;
}

/* Полное отключение редактирования */
.content-locked * {
    -webkit-user-modify: read-only !important;
    -moz-user-modify: read-only !important;
    user-modify: read-only !important;
}

/* Исключения для навигационных кнопок и элементов управления */
.content-locked .back-to-kanban-btn,
.content-locked .print-btn,
.content-locked .pdf-btn,
.content-locked .back-to-kanban-btn *,
.content-locked .print-btn *,
.content-locked .pdf-btn * {
    -webkit-user-modify: inherit !important;
    -moz-user-modify: inherit !important;
    user-modify: inherit !important;
    pointer-events: auto !important;
    cursor: pointer !important;
}

/* Исключения для изображений, чтобы они оставались кликабельными */
.content-locked .block-image,
.content-locked .gallery-item img,
.content-locked .image-container img {
    -webkit-user-modify: initial !important;
    -moz-user-modify: initial !important;
    user-modify: initial !important;
}

/* Разрешаем клики по изображениям для просмотра */
.content-locked .block-image,
.content-locked .gallery-item img,
.content-locked .image-container img {
    pointer-events: auto !important;
    cursor: pointer !important;
}

.content-locked .block-image:hover,
.content-locked .gallery-item img:hover,
.content-locked .image-container img:hover {
    transform: scale(1.02) !important;
    transition: transform 0.2s ease !important;
}

/* Разрешаем работу с галереей изображений */
.content-locked .gallery-item,
.content-locked .image-container {
    pointer-events: auto !important;
}

.content-locked .image-overlay {
    pointer-events: auto !important;
}

/* Полное скрытие всех панелей инструментов блоков в заблокированном режиме */
.content-locked * .block-toolbar,
.content-locked .block-toolbar {
    display: none !important;
    opacity: 0 !important;
    visibility: hidden !important;
}

/* Предотвращение всех hover эффектов для блоков в заблокированном режиме */
.content-locked .content-block:hover .block-toolbar,
.content-locked .text-block:hover .block-toolbar,
.content-locked .image-block:hover .block-toolbar,
.content-locked .file-block:hover .block-toolbar,
.content-locked .gallery-block:hover .block-toolbar,
.content-locked .swiper-block:hover .block-toolbar {
    display: none !important;
    opacity: 0 !important;
    visibility: hidden !important;
}

/* Скрыть кнопки удаления отдельных изображений в заблокированном режиме */
.content-locked .image-delete-btn {
    display: none !important;
    opacity: 0 !important;
    visibility: hidden !important;
}

/* Адаптивность для мобильных устройств */
@media (max-width: 768px) {
    .bottom-toolbar-fixed {
        bottom: 15px;
        padding: 6px 12px;
    }
    
    .bottom-toolbar-content {
        gap: 10px;
    }
    
    .toolbar-icon {
        width: 44px;
        height: 44px;
        font-size: 16px;
    }
}

@media (max-width: 480px) {
    .bottom-toolbar-fixed {
        bottom: 10px;
        padding: 4px 8px;
    }
    
    .bottom-toolbar-content {
        gap: 8px;
    }
    
    .toolbar-icon {
        width: 40px;
        height: 40px;
        font-size: 14px;
    }
}

/* Стили для task-header-fixed */
.task-header-fixed {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    background: #fff;
    border-bottom: 1px solid #dee2e6;
    z-index: 1000;
    padding: 15px 0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Стили для названия задачи */
.task-title-container {
    position: relative;
}

.task-title {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 600;
    color: #2c3e50;
    cursor: pointer;
    padding: 8px 12px;
    border-radius: 6px;
    transition: all 0.2s ease;
    word-break: break-word;
}

.task-title:hover {
    background-color: #f8f9fa;
    color: #007bff;
}

.task-title-input {
    font-size: 1.5rem;
    font-weight: 600;
    border: 2px solid #007bff;
    border-radius: 6px;
    padding: 8px 12px;
}

.task-title-input:focus {
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

/* Стили для статуса/приоритета */
.status-container {
    display: inline-block;
}

.status-square {
    width: 24px;
    height: 24px;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s ease;
    border: 2px solid transparent;
}

.status-square:hover {
    transform: scale(1.1);
    border-color: rgba(0,0,0,0.2);
}

/* Цвета приоритетов */
.status-square.low {
    background-color: #28a745;
}

.status-square.medium {
    background-color: #ffc107;
}

.status-square.high {
    background-color: #fd7e14;
}

.status-square.urgent {
    background-color: #dc3545;
}

.status-square.critical {
    background-color: #6f42c1;
}

.status-square.blocked {
    background-color: #6c757d;
}

/* Стили для меню статуса */
.status-menu {
    position: absolute;
    top: 100%;
    left: 50%;
    transform: translateX(-50%);
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    min-width: 200px;
    z-index: 1001;
    margin-top: 8px;
}

.status-menu-header {
    padding: 12px 16px;
    font-weight: 600;
    color: #495057;
    border-bottom: 1px solid #dee2e6;
    background-color: #f8f9fa;
    border-radius: 8px 8px 0 0;
}

.priority-options {
    padding: 8px 0;
}

.priority-option {
    display: flex;
    align-items: center;
    padding: 10px 16px;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.priority-option:hover {
    background-color: #f8f9fa;
}

.priority-circle {
    width: 16px;
    height: 16px;
    border-radius: 3px;
    margin-right: 12px;
}

.priority-circle.low { background-color: #28a745; }
.priority-circle.medium { background-color: #ffc107; }
.priority-circle.high { background-color: #fd7e14; }
.priority-circle.urgent { background-color: #dc3545; }
.priority-circle.critical { background-color: #6f42c1; }
.priority-circle.blocked { background-color: #6c757d; }

/* Стили для времени/календаря */
.time-container {
    display: inline-block;
    margin-left: 15px;
}

.time-icon {
    font-size: 20px;
    color: #6c757d;
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    transition: all 0.2s ease;
}

.time-icon:hover {
    background-color: #f8f9fa;
    color: #007bff;
}

/* Стили для календаря */
.date-picker-container {
    position: absolute;
    top: 100%;
    left: 50%;
    transform: translateX(-50%);
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    min-width: 280px;
    z-index: 1001;
    margin-top: 8px;
}

.date-picker-header {
    padding: 12px 16px;
    font-weight: 600;
    color: #495057;
    border-bottom: 1px solid #dee2e6;
    background-color: #f8f9fa;
    border-radius: 8px 8px 0 0;
}

.date-field {
    padding: 0 16px;
}

.date-field:first-of-type {
    padding-top: 16px;
}

.date-picker-actions {
    padding: 12px 16px;
    border-top: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    gap: 8px;
}

/* Стили для исполнителей */
.assignee-container {
    display: inline-block;
}

.assignee-avatar, .no-assignee {
    display: flex;
    align-items: center;
    cursor: pointer;
    padding: 8px 12px;
    border-radius: 8px;
    transition: all 0.2s ease;
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
}

.assignee-avatar:hover, .no-assignee:hover {
    background-color: #e9ecef;
    border-color: #007bff;
}

.user-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 8px;
}

.assignee-name {
    font-weight: 500;
    color: #495057;
}

.no-assignee i {
    margin-right: 8px;
    color: #6c757d;
}

.no-assignee span {
    color: #6c757d;
    font-weight: 500;
}

/* Стили для меню исполнителей */
.assignee-menu {
    position: absolute;
    top: 100%;
    right: 0;
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    min-width: 250px;
    z-index: 1001;
    margin-top: 8px;
}

.assignee-menu-header {
    padding: 12px 16px;
    font-weight: 600;
    color: #495057;
    border-bottom: 1px solid #dee2e6;
    background-color: #f8f9fa;
    border-radius: 8px 8px 0 0;
}

.assignee-options {
    padding: 8px 0;
    max-height: 300px;
    overflow-y: auto;
}

.assignee-option {
    display: flex;
    align-items: center;
    padding: 10px 16px;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.assignee-option:hover {
    background-color: #f8f9fa;
}

.user-avatar-small {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    margin-right: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #e9ecef;
}

.user-avatar-small img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
}

.user-avatar-small i {
    color: #6c757d;
    font-size: 14px;
}

/* Адаптивность */
@media (max-width: 768px) {
    .task-header-fixed {
        padding: 8px 0;
        position: relative;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .task-header-fixed .container-fluid {
        padding: 0 15px;
    }
    
    .task-header-fixed .row {
        margin: 0;
        flex-direction: column;
        gap: 8px;
    }
    
    .task-header-fixed .col-md-4 {
        flex: none;
        width: 100%;
        max-width: 100%;
        margin-bottom: 0;
    }
    
    /* Заголовок с кнопкой возврата */
    .task-title-container {
        display: flex;
        align-items: center;
        width: 100%;
    }
    
    .task-title {
        font-size: 1.1rem;
        flex: 1;
        margin: 0;
        padding: 6px 8px;
        line-height: 1.3;
    }
    
    .task-title-input {
        font-size: 1.1rem;
        flex: 1;
    }
    
    .back-to-kanban-btn {
        width: 32px;
        height: 32px;
        margin-right: 8px;
        flex-shrink: 0;
    }
    
    .back-to-kanban-btn i {
        font-size: 13px;
    }
    
    /* Центральная секция */
    .task-header-fixed .col-md-4.text-center {
        text-align: left !important;
    }
    
    .task-header-fixed .col-md-4.text-center .d-flex {
        justify-content: flex-start !important;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }
    
    /* Правая секция */
    .task-header-fixed .col-md-4.text-end {
        text-align: left !important;
    }
    
    .task-header-fixed .col-md-4.text-end .d-flex {
        justify-content: flex-start !important;
        align-items: center;
        gap: 12px;
    }
    
    .assignee-name {
        display: none;
    }
    
    /* Упрощаем действия */
    .task-actions {
        display: flex;
        gap: 8px;
    }
    
    .action-btn {
        width: 32px;
        height: 32px;
        padding: 6px;
    }
    
    .action-btn svg {
        width: 14px;
        height: 14px;
    }
    
    .action-btn i {
        font-size: 12px;
    }
    
    /* Статус квадрат */
    .status-square {
        width: 32px;
        height: 32px;
        margin: 0;
    }
    
    /* Иконка времени */
    .time-icon {
        font-size: 16px;
        padding: 6px;
        cursor: pointer;
        border-radius: 4px;
        transition: background-color 0.2s;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .time-icon:hover {
        background-color: #f8f9fa;
    }
    
    /* Исполнитель */
    .assignee-avatar .user-avatar {
        width: 32px;
        height: 32px;
    }
    
    .no-assignee {
        font-size: 12px;
        padding: 6px 8px;
        border-radius: 4px;
        border: 1px solid #dee2e6;
        cursor: pointer;
        transition: background-color 0.2s;
    }
    
    .no-assignee:hover {
        background-color: #f8f9fa;
    }
    
    .no-assignee i {
        font-size: 14px;
        margin-right: 4px;
    }
    
    /* Выпадающие меню */
    .status-menu,
    .date-picker-container,
    .assignee-menu {
        min-width: 280px;
        max-width: calc(100vw - 30px);
        left: 50%;
        transform: translateX(-50%);
        right: auto;
    }
    
    .date-picker-container {
        min-width: 300px;
    }
}

/* Дополнительная адаптивность для очень маленьких экранов */
@media (max-width: 576px) {
    .task-header-fixed {
        padding: 6px 0;
    }
    
    .task-header-fixed .container-fluid {
        padding: 0 10px;
    }
    
    .task-title {
        font-size: 1rem;
        padding: 4px 6px;
    }
    
    .task-title-input {
        font-size: 1rem;
    }
    
    .back-to-kanban-btn {
        width: 28px;
        height: 28px;
        margin-right: 6px;
    }
    
    .back-to-kanban-btn i {
        font-size: 12px;
    }
    
    .action-btn,
    .status-square,
    .time-icon {
        width: 28px;
        height: 28px;
    }
    
    .action-btn svg {
        width: 12px;
        height: 12px;
    }
    
    .action-btn i {
        font-size: 11px;
    }
    
    .time-icon {
        font-size: 14px;
    }
    
    .assignee-avatar .user-avatar {
        width: 28px;
        height: 28px;
    }
    
    .no-assignee {
        font-size: 11px;
        padding: 4px 6px;
    }
    
    .no-assignee i {
        font-size: 12px;
    }
    
    .status-menu,
    .assignee-menu {
        min-width: 260px;
        max-width: calc(100vw - 20px);
    }
    
    .date-picker-container {
        min-width: 280px;
        max-width: calc(100vw - 20px);
    }
    
    /* Скрываем подписи на очень маленьких экранах */
    .no-assignee span {
        display: none;
    }
    
    .no-assignee i {
        margin: 0;
    }
    
    /* Улучшаем выпадающие меню для очень маленьких экранов */
    .priority-option {
        padding: 10px 12px;
    }
    
    .priority-option span {
        font-size: 14px;
    }
    
    .assignee-option {
        padding: 10px 12px;
    }
    
    .user-avatar-small img {
        width: 24px;
        height: 24px;
    }
}

/* Дополнительные мобильные улучшения */
@media (max-width: 480px) {
    .task-header-fixed .row {
        gap: 6px;
    }
    
    .task-header-fixed .d-flex {
        gap: 8px !important;
    }
    
    .back-to-kanban-btn {
        width: 26px;
        height: 26px;
        margin-right: 4px;
    }
    
    .back-to-kanban-btn i {
        font-size: 11px;
    }
    
    .task-title {
        font-size: 0.9rem;
        padding: 3px 5px;
    }
    
    .action-btn,
    .status-square,
    .time-icon {
        width: 26px;
        height: 26px;
    }
    
    .action-btn svg {
        width: 11px;
        height: 11px;
    }
    
    .time-icon {
        font-size: 13px;
    }
    
    .assignee-avatar .user-avatar {
        width: 26px;
        height: 26px;
    }
}

/* Мобильные классы JavaScript */
.mobile-view .status-menu,
.mobile-view .date-picker-container,
.mobile-view .assignee-menu {
    position: fixed !important;
    z-index: 9999 !important;
    box-shadow: 0 8px 32px rgba(0,0,0,0.2) !important;
}

.mobile-view .status-menu {
    animation: slideInUp 0.2s ease-out;
}

.mobile-view .date-picker-container {
    animation: slideInUp 0.2s ease-out;
}

.mobile-view .assignee-menu {
    animation: slideInUp 0.2s ease-out;
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Overlay для мобильных меню */
.mobile-view .status-menu:not(.d-none)::before,
.mobile-view .date-picker-container:not(.d-none)::before,
.mobile-view .assignee-menu:not(.d-none)::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.1);
    z-index: -1;
    pointer-events: none;
}

/* === СИСТЕМА ЛИСТОВ A4 === */

/* Панель инструментов для редактора */
.canvas-toolbar {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
}

.toolbar-section {
    display: flex;
    align-items: center;
    gap: 20px;
}

.toolbar-title {
    font-weight: 600;
    font-size: 16px;
    color: #495057;
    margin-right: 20px;
}

.toolbar-buttons {
    display: flex;
    gap: 10px;
}

.toolbar-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    color: #495057;
    text-decoration: none;
    font-size: 14px;
    transition: all 0.2s ease;
    cursor: pointer;
}

.toolbar-btn:hover {
    background: #e9ecef;
    border-color: #adb5bd;
    color: #212529;
    transform: translateY(-1px);
}

.toolbar-btn i {
    font-size: 14px;
}

.save-btn {
    background: #28a745 !important;
    color: white !important;
    border-color: #28a745 !important;
}

.save-btn:hover {
    background: #218838 !important;
    border-color: #1e7e34 !important;
}

/* Полотно для контента */
.content-canvas {
    min-height: 600px;
    background: #e9ecef;
    padding: 30px;
    border-radius: 8px;
    overflow-x: auto;
}

/* Лист A4 */
.a4-page {
    width: 794px; /* A4 ширина в пикселях при 96 DPI */
    min-height: 1123px; /* A4 высота в пикселях при 96 DPI */
    background: white;
    margin: 0 auto 30px auto;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    border-radius: 4px;
    position: relative;
    overflow: hidden;
}

.a4-page:last-child {
    margin-bottom: 0;
}

/* Заголовок листа */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    font-size: 12px;
    color: #6c757d;
}

.page-number {
    font-weight: 500;
}

.page-actions {
    display: flex;
    gap: 5px;
}

.page-btn {
    background: none;
    border: none;
    color: #6c757d;
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 4px;
    transition: all 0.2s ease;
}

.page-btn:hover {
    background: #e9ecef;
    color: #495057;
}

/* Содержимое листа */
.page-content {
    padding: 40px;
    min-height: calc(1123px - 41px); /* Высота минус заголовок */
    position: relative;
}

/* Пустой лист */
.empty-page {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 300px;
    color: #adb5bd;
    cursor: pointer;
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.empty-page:hover {
    border-color: #007bff;
    color: #007bff;
    background: rgba(0, 123, 255, 0.05);
}

.empty-page i {
    font-size: 48px;
    margin-bottom: 15px;
}

.empty-page p {
    font-size: 16px;
    margin: 0;
}

/* Блоки контента */
.content-block {
    margin-bottom: 20px;
    position: relative;
    border-radius: 6px;
    transition: all 0.2s ease;
}

.content-block:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.content-block.active {
    box-shadow: 0 0 0 2px #007bff;
}

/* Текстовые блоки */
.text-block {
    padding: 15px;
    background: transparent;
    border: 1px solid transparent;
    border-radius: 6px;
    transition: all 0.2s ease;
}

.text-block:hover {
    border-color: #e9ecef;
    background: rgba(248, 249, 250, 0.5);
}

.text-content {
    min-height: 60px;
    font-size: 16px;
    line-height: 1.6;
    color: #212529;
    outline: none;
    word-wrap: break-word;
}

.text-content:focus {
    background: white;
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
    border-radius: 4px;
    padding: 10px;
}

.text-content:empty::before {
    content: 'Введите текст...';
    color: #adb5bd;
    pointer-events: none;
}

/* Блоки изображений */
.image-block {
    text-align: center;
    padding: 15px;
    background: transparent;
    border: 1px solid transparent;
    border-radius: 6px;
    transition: all 0.2s ease;
}

.image-block:hover {
    border-color: #e9ecef;
    background: rgba(248, 249, 250, 0.5);
}

.image-block img {
    max-width: 100%;
    height: auto;
    border-radius: 6px;
    cursor: pointer;
    transition: transform 0.2s ease;
}

.image-block img:hover {
    transform: scale(1.02);
}

.image-caption {
    margin-top: 10px;
    font-size: 14px;
    color: #6c757d;
    font-style: italic;
}

/* Стили для Swiper блоков */
.swiper-block {
    position: relative;
}

.swiper-container {
    position: relative;
    width: 100%;
    height: 400px;
    border-radius: 8px;
    overflow: hidden;
    background: #f8f9fa;
}

.swiper-wrapper {
    display: flex;
    height: 100%;
    transition: transform 0.3s ease;
}

.swiper-slide {
    min-width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

.swiper-slide img,
.swiper-slide video {
    max-width: 100%;
    max-height: 100%;
       object-fit: cover;
    border-radius: 4px;
    cursor: pointer;
}

/* Кнопка удаления отдельного изображения в swiper */
.swiper-slide .image-delete-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(220, 53, 69, 0.9);
    color: white;
    border: none;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 14px;
    opacity: 0;
    visibility: hidden;
    transition: all 0.2s ease;
    z-index: 15;
}

.swiper-slide:hover .image-delete-btn {
    opacity: 1;
    visibility: visible;
}

.swiper-slide .image-delete-btn:hover {
    background: rgba(220, 53, 69, 1);
    transform: scale(1.1);
}

/* Навигация Swiper */
.swiper-navigation {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(0, 0, 0, 0.5);
    color: white;
    border: none;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 10;
    transition: background 0.3s ease;
}

.swiper-navigation:hover {
    background: rgba(0, 0, 0, 0.7);
}

.swiper-prev {
    left: 10px;
}

.swiper-next {
    right: 10px;
}

/* Пагинация Swiper */
.swiper-pagination {
    position: absolute;
    bottom: 15px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 8px;
    z-index: 10;
}

.swiper-pagination-bullet {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.5);
    cursor: pointer;
    transition: background 0.3s ease;
}

.swiper-pagination-bullet.active {
    background: white;
}

/* Счетчик Swiper */
.swiper-counter {
    position: absolute;
    top: 15px;
    right: 15px;
    background: rgba(0, 0, 0, 0.6);
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    z-index: 10;
}

.block-btn.delete-btn {
    background: #dc3545;
}

.block-btn.delete-btn:hover {
    background: #c82333;
}

/* Разделитель страниц */
.page-break {
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #adb5bd;
    font-size: 12px;
    margin: 20px 0;
    position: relative;
}

.page-break::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(to right, transparent, #dee2e6 20%, #dee2e6 80%, transparent);
}

.page-break span {
    background: #e9ecef;
    padding: 0 15px;
    position: relative;
    z-index: 1;
}

/* Индикатор сохранения */
.save-status {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #28a745;
    font-size: 14px;
    margin-left: 15px;
}

.save-status.saving {
    color: #ffc107;
}

.save-status.error {
    color: #dc3545;
}

.save-status i {
    font-size: 16px;
}

/* Индикатор автосохранения */
.auto-save-indicator {
    position: fixed;
    bottom: 30px;
    right: 30px;
    background: #007bff;
    color: white;
    padding: 10px 15px;
    border-radius: 25px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    z-index: 1000;
}

.auto-save-indicator.show {
    opacity: 1;
    visibility: visible;
}

/* Модальное окно для изображений */
.modal-xl .modal-dialog {
    max-width: 90vw;
}

#modalImage {
    max-height: 80vh;
    object-fit: contain;
}

/* Адаптивность для мобильных устройств */
@media (max-width: 992px) {
    .content-canvas {
        padding: 15px;
    }
    
    .a4-page {
        width: 100%;
        min-height: auto;
        transform: scale(0.85);
        transform-origin: top center;
        margin-bottom: 20px;
    }
    
    .page-content {
        padding: 20px;
    }
    
    .toolbar-section {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .toolbar-buttons {
        flex-wrap: wrap;
    }
}

@media (max-width: 768px) {
    .task-content {
        margin-top: 20px;
        padding: 10px;
    }
    
    .content-canvas {
        padding: 10px;
        background: #f8f9fa;
    }
    
    .a4-page {
        width: 100%;
        min-height: 400px;
        transform: none;
        transform-origin: none;
        margin-bottom: 15px;
        border-radius: 8px;
        overflow: visible;
    }
    
    .page-header {
        padding: 8px 15px;
        font-size: 11px;
    }
    
    .page-content {
        padding: 15px;
        min-height: 300px;
    }
    
    .toolbar-btn span {
        display: none;
    }
    
    .canvas-toolbar {
        padding: 10px;
        margin-bottom: 15px;
    }
    
    .toolbar-section {
        gap: 10px;
    }
    
    .toolbar-buttons {
        gap: 8px;
    }
    
    .toolbar-btn {
        min-width: 40px;
        padding: 8px;
    }
    
    .auto-save-indicator {
        bottom: 15px;
        right: 15px;
        padding: 6px 10px;
        font-size: 11px;
    }
}

@media (max-width: 576px) {
    .task-content {
        margin-top: 15px;
        padding: 5px;
    }
    
    .content-canvas {
        padding: 5px;
        border-radius: 4px;
    }
    
    .a4-page {
        margin-bottom: 10px;
        min-height: 350px;
        border-radius: 6px;
    }
    
    .page-header {
        padding: 6px 12px;
        font-size: 10px;
    }
    
    .page-content {
        padding: 12px;
        min-height: 250px;
    }
    
    .canvas-toolbar {
        padding: 8px;
        margin-bottom: 10px;
    }
    
    .toolbar-btn {
        min-width: 35px;
        padding: 6px;
        font-size: 14px;
    }
}

/* Анимации */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.content-block {
    animation: fadeIn 0.3s ease;
}

/* Drag and drop стили */
.content-canvas.drag-over {
    background: rgba(0, 123, 255, 0.1);
    border: 2px dashed #007bff;
}

.drop-zone {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 123, 255, 0.1);
    border: 2px dashed #007bff;
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 100;
}

.drop-zone.active {
    display: flex;
}

.drop-zone-content {
    text-align: center;
    color: #007bff;
}

.drop-zone-content i {
    font-size: 48px;
    margin-bottom: 15px;
}

.drop-zone-content h3 {
    margin: 0 0 10px 0;
    font-size: 24px;
}

.drop-zone-content p {
    margin: 0;
    font-size: 16px;
}

/* Стили для печати */
@media print {
    /* Скрываем все элементы интерфейса */
    .task-header-fixed,
    .bottom-toolbar-fixed,
    .task-actions,
    .status-menu,
    .date-picker-container,
    .assignee-menu,
    .block-toolbar,
    .page-actions,
    .auto-save-indicator,
    .notification,
    .modal,
    .toolbar-icon,
    .save-status {
        display: none !important;
    }

    /* Основные стили для печати */
    body {
        background: white !important;
        margin: 0;
        padding: 0;
        font-family: 'Times New Roman', serif;
        font-size: 12pt;
        line-height: 1.4;
        color: black !important;
    }

    /* Контейнер для печати */
    .task-content {
        margin: 0 !important;
        padding: 20px !important;
        background: white !important;
    }

    /* Заголовок задачи для печати */
    .print-header {
        margin-bottom: 20px;
        border-bottom: 2px solid #333;
        padding-bottom: 10px;
    }

    .print-title {
        font-size: 18pt;
        font-weight: bold;
        margin: 0 0 10px 0;
        color: black !important;
    }

    .print-meta {
        font-size: 10pt;
        color: #666 !important;
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }

    /* Листы A4 для печати */
    .a4-page {
        background: white !important;
        border: none !important;
        box-shadow: none !important;
        margin: 0 0 20px 0 !important;
        padding: 0 !important;
        width: 100% !important;
        max-width: none !important;
        page-break-inside: avoid;
    }

    .page-header {
        display: none !important;
    }

    .page-content {
        padding: 0 !important;
        min-height: auto !important;
    }

    /* Блоки контента для печати */
    .content-block {
        border: none !important;
        margin: 0 0 15px 0 !important;
        padding: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
    }

    .text-content {
        font-size: 12pt !important;
        line-height: 1.4 !important;
        color: black !important;
        background: transparent !important;
        border: none !important;
        padding: 0 !important;
    }

    /* Изображения для печати */
    .image-block img {
        max-width: 100% !important;
        height: auto !important;
        display: block;
        margin: 10px 0;
    }

    .image-caption {
        font-size: 10pt !important;
        color: #666 !important;
        text-align: center;
        margin-top: 5px;
    }

    /* Принудительный разрыв страницы */
    .page-break {
        page-break-before: always;
    }

    /* Убираем пустые страницы */
    .empty-page {
        display: none !important;
    }

    /* Стили для ссылок в печати */
    a {
        color: black !important;
        text-decoration: underline !important;
    }

    /* Списки для печати */
    ul, ol {
        margin: 10px 0;
        padding-left: 20px;
    }

    li {
        margin: 5px 0;
    }

    /* Заголовки для печати */
    h1, h2, h3, h4, h5, h6 {
        color: black !important;
        margin: 15px 0 10px 0;
    }
}

</style>