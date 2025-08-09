
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

/* Responsive */
@media (max-width: 768px) {
    .task-header-fixed .col-md-4 {
        margin-bottom: 10px;
    }
    
    .task-title {
        font-size: 1.2rem;
    }
    
    .assignee-name {
        display: none;
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
    display: none;
    gap: 4px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.content-block:hover .block-toolbar,
.content-block.active .block-toolbar {
    display: flex;
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
    justify-content: center;
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
        padding: 10px 0;
    }
    
    .task-title {
        font-size: 1.25rem;
    }
    
    .task-title-input {
        font-size: 1.25rem;
    }
    
    .status-menu,
    .date-picker-container,
    .assignee-menu {
        min-width: 240px;
    }
    
    .col-md-4 {
        margin-bottom: 10px;
    }
    
    .col-md-4.text-center,
    .col-md-4.text-end {
        text-align: left !important;
    }
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

/* Панель инструментов блока */
.block-toolbar {
    position: absolute;
    top: -35px;
    right: 0;
    display: flex;
    gap: 5px;
    opacity: 0;
    visibility: hidden;
    transition: all 0.2s ease;
    z-index: 10;
}

.content-block:hover .block-toolbar,
.content-block.active .block-toolbar {
    opacity: 1;
    visibility: visible;
}

.block-btn {
    background: #007bff;
    color: white;
    border: none;
    padding: 6px 10px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
    transition: all 0.2s ease;
}

.block-btn:hover {
    background: #0056b3;
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
        transform: scale(0.8);
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
    .a4-page {
        transform: scale(0.7);
    }
    
    .toolbar-btn span {
        display: none;
    }
    
    .canvas-toolbar {
        padding: 10px;
    }
    
    .auto-save-indicator {
        bottom: 20px;
        right: 20px;
        padding: 8px 12px;
        font-size: 12px;
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