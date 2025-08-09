// Функционал мобильной адаптации

// Функция для переключения видимости боковой панели
function toggleSidebar() {
    const sidebarContainer = document.querySelector('.sidebar-container');
    const sidebar = document.querySelector('.sidebar');
    const backdrop = document.querySelector('.sidebar-backdrop');
    
    if (sidebarContainer && sidebar) {
        // Проверяем, находимся ли мы на мобильном устройстве
        if (window.innerWidth < 768) {
            sidebarContainer.style.display = sidebarContainer.style.display === 'block' ? 'none' : 'block';
        }
        sidebar.classList.toggle('active');
    }
    
    if (backdrop) {
        backdrop.classList.toggle('active');
    }
}

// Функция для закрытия сайдбара по клику на фон
function closeSidebarOnBackdropClick() {
    const backdrop = document.querySelector('.sidebar-backdrop');
    if (backdrop) {
        backdrop.addEventListener('click', function() {
            const sidebarContainer = document.querySelector('.sidebar-container');
            const sidebar = document.querySelector('.sidebar');
            if (sidebar) {
                if (sidebarContainer && window.innerWidth < 768) {
                    sidebarContainer.style.display = 'none';
                }
                sidebar.classList.remove('active');
                backdrop.classList.remove('active');
            }
        });
    }
}

// Инициализация компонентов мобильного адаптива
function initMobileAdaptive() {
    // Находим существующую кнопку для переключения сайдбара или создаем новую
    let toggleBtn = document.querySelector('.sidebar-toggle');
    if (!toggleBtn) {
        toggleBtn = document.createElement('button');
        toggleBtn.className = 'sidebar-toggle d-md-none';
        toggleBtn.innerHTML = '<i class="fas fa-bars"></i>';
        document.body.appendChild(toggleBtn);
    }
    
    // В любом случае добавляем обработчик клика
    toggleBtn.addEventListener('click', toggleSidebar);
    
    // Находим существующий фон или создаем новый
    let backdrop = document.querySelector('.sidebar-backdrop');
    if (!backdrop) {
        backdrop = document.createElement('div');
        backdrop.className = 'sidebar-backdrop';
        document.body.appendChild(backdrop);
    }
    
    // Инициализируем обработчик клика по фону
    closeSidebarOnBackdropClick();
    
    // Адаптивное изменение кнопок в заголовках для мобильных устройств
    const actionButtons = document.querySelectorAll('.d-flex.justify-content-between.align-items-center > div:last-child .btn');
    if (window.innerWidth < 768 && actionButtons.length > 0) {
        actionButtons.forEach(btn => {
            btn.classList.add('btn-sm-mobile', 'mb-2', 'me-0');
        });
    }
}

// Запуск после загрузки DOM
document.addEventListener('DOMContentLoaded', function() {
    initMobileAdaptive();
    
    // Инициализация сайдбара в зависимости от размера экрана
    const sidebarContainer = document.querySelector('.sidebar-container');
    if (sidebarContainer && window.innerWidth < 768) {
        sidebarContainer.style.display = 'none';
    }
    
    // Обработка изменения размера окна
    window.addEventListener('resize', function() {
        const sidebarContainer = document.querySelector('.sidebar-container');
        const sidebar = document.querySelector('.sidebar');
        const backdrop = document.querySelector('.sidebar-backdrop');
        
        // Если окно стало больше мобильного размера и сайдбар открыт, скрываем его
        if (window.innerWidth >= 768) {
            // На десктопе сайдбар всегда показан
            if (sidebarContainer) {
                sidebarContainer.style.display = 'block';
            }
            
            if (sidebar) {
                sidebar.classList.remove('active');
            }
            
            if (backdrop) {
                backdrop.classList.remove('active');
            }
        } else {
            // На мобильном сайдбар скрыт по умолчанию
            if (sidebarContainer && !sidebar.classList.contains('active')) {
                sidebarContainer.style.display = 'none';
            }
        }
    });
});

// Функция для мобильного адаптива выпадающих меню
function handleDropdownsOnMobile() {
    const dropdowns = document.querySelectorAll('.custom-dropdown');
    
    if (dropdowns.length > 0) {
        dropdowns.forEach(dropdown => {
            const toggle = dropdown.querySelector('.custom-dropdown-toggle');
            const menu = dropdown.querySelector('.custom-dropdown-menu');
            
            if (toggle && menu) {
                // Закрывать меню по клику вне его области
                document.addEventListener('click', function(event) {
                    const isClickInside = dropdown.contains(event.target);
                    if (!isClickInside && menu.classList.contains('show')) {
                        menu.classList.remove('show');
                    }
                });
                
                // Позиционировать меню корректно на мобильных устройствах
                if (window.innerWidth < 768) {
                    menu.style.position = 'fixed';
                    menu.style.top = '50%';
                    menu.style.left = '50%';
                    menu.style.transform = 'translate(-50%, -50%)';
                    menu.style.maxHeight = '80vh';
                    menu.style.overflowY = 'auto';
                }
            }
        });
    }
}

// Функция для переключения кастомных выпадающих меню
function toggleCustomDropdown(spaceId) {
    const dropdown = document.getElementById(`customDropdown${spaceId}`);
    
    // Закрыть все другие меню сначала
    document.querySelectorAll('.custom-dropdown-menu').forEach(menu => {
        if (menu !== dropdown) {
            menu.classList.remove('show');
        }
    });
    
    // Переключить текущее меню
    dropdown.classList.toggle('show');
    
    // Создать/удалить затемнение фона для мобильных устройств
    if (window.innerWidth < 768) {
        let backdrop = document.querySelector('.custom-dropdown-backdrop');
        
        if (!backdrop) {
            backdrop = document.createElement('div');
            backdrop.className = 'custom-dropdown-backdrop';
            document.body.appendChild(backdrop);
            
            backdrop.addEventListener('click', function() {
                document.querySelectorAll('.custom-dropdown-menu').forEach(menu => {
                    menu.classList.remove('show');
                });
                backdrop.classList.remove('show');
            });
        }
        
        if (dropdown.classList.contains('show')) {
            backdrop.classList.add('show');
        } else {
            backdrop.classList.remove('show');
        }
    }
}

// Запускаем обработку выпадающих меню
document.addEventListener('DOMContentLoaded', function() {
    handleDropdownsOnMobile();
    
    // Закрытие всех меню по клику вне их области
    document.addEventListener('click', function(event) {
        const isDropdownToggle = event.target.closest('.custom-dropdown-toggle');
        if (!isDropdownToggle) {
            document.querySelectorAll('.custom-dropdown-menu').forEach(menu => {
                menu.classList.remove('show');
            });
            
            const backdrop = document.querySelector('.custom-dropdown-backdrop');
            if (backdrop) {
                backdrop.classList.remove('show');
            }
        }
    });
});
