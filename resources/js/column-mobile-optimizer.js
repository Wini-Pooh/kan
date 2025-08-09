// Функция для оптимизации отображения колонок на мобильных устройствах
function optimizeColumnsForMobile() {
    // Проверяем, находимся ли мы на мобильном устройстве
    if (window.innerWidth >= 768) return;

    // Находим все колонки
    const columns = document.querySelectorAll('.kanban-column');
    
    columns.forEach(column => {
        // Уменьшаем отступы и размеры для мобильного вида
        column.style.padding = '0.25rem';
        
        // Находим заголовок колонки
        const header = column.querySelector('.column-header');
        if (header) {
            header.style.padding = '0.5rem 0.25rem';
            
            // Уменьшаем размер шрифта заголовка
            const title = header.querySelector('.column-title');
            if (title) {
                title.style.fontSize = '0.9rem';
            }
        }
        
        // Находим список задач
        const taskList = column.querySelector('.task-list');
        if (taskList) {
            taskList.style.padding = '0.25rem';
            
            // Уменьшаем отступы между задачами
            const tasks = taskList.querySelectorAll('.task-card');
            tasks.forEach(task => {
                task.style.padding = '0.5rem';
                task.style.marginBottom = '0.35rem';
                
                // Уменьшаем размеры шрифтов в задачах
                const taskTitle = task.querySelector('.task-title');
                if (taskTitle) taskTitle.style.fontSize = '0.85rem';
                
                const taskDescription = task.querySelector('.task-description');
                if (taskDescription) taskDescription.style.fontSize = '0.75rem';
            });
        }
    });
}

// Вызываем функцию при загрузке страницы и при изменении размера окна
window.addEventListener('load', optimizeColumnsForMobile);
window.addEventListener('resize', optimizeColumnsForMobile);
