<?php
/**
 * Тестовый скрипт для проверки корректности работы ссылок в PDF
 * 
 * Этот файл содержит примеры различных типов контента с файлами
 * для тестирования PDF генерации.
 */

// Примеры контента для тестирования
$testContent = [
    // Одиночное изображение
    'single_image' => '[FILE:http://localhost/storage/tasks/1/image1.jpg|Тестовое изображение.jpg|500 КБ|image]',
    
    // Одиночный документ
    'single_document' => '[FILE:http://localhost/storage/tasks/1/document.pdf|Важный документ.pdf|2.5 МБ|file]',
    
    // Видео файл
    'single_video' => '[FILE:http://localhost/storage/tasks/1/video.mp4|Презентация.mp4|15.2 МБ|video]',
    
    // Множественные изображения (SWIPER)
    'multiple_images' => '[SWIPER:http://localhost/storage/tasks/1/img1.jpg|Изображение 1.jpg|300 КБ|image;http://localhost/storage/tasks/1/img2.jpg|Изображение 2.jpg|450 КБ|image;http://localhost/storage/tasks/1/img3.jpg|Изображение 3.jpg|600 КБ|image]',
    
    // Сетка файлов (FILEGRID)
    'file_grid' => '[FILEGRID:http://localhost/storage/tasks/1/doc1.pdf|Документ 1.pdf|1.2 МБ|file;http://localhost/storage/tasks/1/doc2.docx|Документ 2.docx|800 КБ|file;http://localhost/storage/tasks/1/table.xlsx|Таблица.xlsx|500 КБ|file]',
    
    // HTML блоки с документами
    'html_document_block' => '<div class="content-block document-block" id="block_12345">
        <div class="document-info">
            <div class="document-icon">
                <i class="fas fa-file-pdf fa-3x text-danger"></i>
            </div>
            <div class="document-details">
                <h4 class="document-title">Техническое задание.pdf</h4>
                <p class="document-size">3.5 МБ</p>
                <div class="document-actions">
                    <a href="http://localhost/storage/tasks/1/tech_specs.pdf" target="_blank" class="btn btn-primary btn-sm">
                        <i class="fas fa-download"></i> Скачать
                    </a>
                    <a href="http://localhost/storage/tasks/1/tech_specs.pdf" target="_blank" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-eye"></i> Просмотр
                    </a>
                </div>
            </div>
        </div>
    </div>',
    
    // HTML блок с изображением
    'html_image_block' => '<div class="content-block image-block" id="block_67890">
        <img src="http://localhost/storage/tasks/1/screenshot.png" alt="Скриншот интерфейса" onclick="showImageModal(\'http://localhost/storage/tasks/1/screenshot.png\', \'Скриншот интерфейса\')">
        <div class="image-caption">
            Скриншот главного интерфейса системы
        </div>
    </div>',
    
    // Смешанный контент
    'mixed_content' => '
        <h2>Отчет по проекту</h2>
        <p>Основные результаты работы:</p>
        
        [FILE:http://localhost/storage/tasks/1/report.pdf|Финальный отчет.pdf|5.2 МБ|file]
        
        <p>Скриншоты интерфейса:</p>
        
        [SWIPER:http://localhost/storage/tasks/1/ui1.png|Главная страница.png|800 КБ|image;http://localhost/storage/tasks/1/ui2.png|Страница настроек.png|750 КБ|image]
        
        <p>Дополнительные материалы:</p>
        
        [FILEGRID:http://localhost/storage/tasks/1/code.zip|Исходный код.zip|12.3 МБ|file;http://localhost/storage/tasks/1/db.sql|База данных.sql|2.1 МБ|file]
        
        <p>Видео демонстрация:</p>
        
        [FILE:http://localhost/storage/tasks/1/demo.mp4|Демонстрация функций.mp4|25.7 МБ|video]
    '
];

echo "Тестовые данные для проверки PDF генерации с файлами:\n\n";

foreach ($testContent as $type => $content) {
    echo "=== {$type} ===\n";
    echo "Длина контента: " . strlen($content) . " символов\n";
    echo "Содержит ссылки: " . (strpos($content, 'http://') !== false ? 'Да' : 'Нет') . "\n";
    echo "Содержит FILE блоки: " . (strpos($content, '[FILE:') !== false ? 'Да' : 'Нет') . "\n";
    echo "Содержит SWIPER блоки: " . (strpos($content, '[SWIPER:') !== false ? 'Да' : 'Нет') . "\n";
    echo "Содержит FILEGRID блоки: " . (strpos($content, '[FILEGRID:') !== false ? 'Да' : 'Нет') . "\n";
    echo "Содержит HTML блоки: " . (strpos($content, '<div class=') !== false ? 'Да' : 'Нет') . "\n\n";
}

echo "Проверки для правильной работы PDF:\n\n";

echo "1. Проверьте, что все ссылки в PDF становятся абсолютными (начинаются с http://)\n";
echo "2. Проверьте, что множественные файлы отображаются в виде списка со ссылками\n";
echo "3. Проверьте, что изображения отображаются с правильными размерами\n";
echo "4. Проверьте, что документы показывают иконки и ссылки для скачивания\n";
echo "5. Проверьте, что видео файлы показывают ссылки для просмотра\n";
echo "6. Проверьте, что блоки не разрываются между страницами (page-break-inside: avoid)\n\n";

echo "Тестирование завершено.\n";
?>
