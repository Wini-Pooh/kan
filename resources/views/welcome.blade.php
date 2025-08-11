<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KanBan - Управление проектами</title>
    
    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom styles for landing -->
    <style>
        .hero-section {
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="25" r="1" fill="rgba(255,255,255,0.05)"/><circle cx="50" cy="50" r="1" fill="rgba(255,255,255,0.08)"/><circle cx="25" cy="75" r="1" fill="rgba(255,255,255,0.03)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
        }
        
        .feature-card {
            transition: all 0.3s ease;
            border: 1px solid var(--bs-border-color);
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--bs-box-shadow-lg);
        }
        
        .feature-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--bs-primary), var(--bs-info));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin: 0 auto 1rem;
        }
        
        .pricing-card {
            border: 2px solid var(--bs-border-color);
            transition: all 0.3s ease;
        }
        
        .pricing-card:hover {
            transform: scale(1.05);
            border-color: var(--bs-primary);
        }
        
        .pricing-card.featured {
            border-color: var(--bs-primary);
            background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.05), rgba(var(--bs-info-rgb), 0.05));
        }
        
        .section-padding {
            padding: 80px 0;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .btn-gradient {
            background: linear-gradient(135deg, var(--bs-primary), var(--bs-info));
            border: none;
            color: white;
            transition: all 0.3s ease;
        }
        
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(var(--bs-primary-rgb), 0.3);
            color: white;
        }
        
        .faq-item {
            border: 1px solid var(--bs-border-color);
            border-radius: var(--bs-border-radius);
            margin-bottom: 1rem;
        }
        
        .stats-number {
            font-size: 3rem;
            font-weight: 700;
            color: var(--bs-primary);
        }
    </style>
</head>
<body>
    <!-- Шапка -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
        <div class="container">
            <a class="navbar-brand text-primary" href="#">
                <i class="bi bi-kanban me-2"></i>KanBan
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#home">Главная</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Возможности</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#pricing">Тарифы</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#faq">FAQ</a>
                    </li>
                </ul>
                
                <div class="d-flex">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/home') }}" class="btn btn-outline-primary me-2">Панель управления</a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-outline-primary me-2">Войти</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="btn btn-gradient">Регистрация</a>
                            @endif
                        @endauth
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- Главная секция -->
    <section id="home" class="hero-section d-flex align-items-center">
        <div class="container hero-content">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold text-white mb-4">
                        Управляйте проектами эффективно с KanBan
                    </h1>
                    <p class="lead text-white-50 mb-4">
                        Организуйте свою работу, отслеживайте прогресс и достигайте целей с помощью современной системы управления проектами
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn btn-light btn-lg px-4">
                                Начать бесплатно
                            </a>
                        @endif
                        <a href="#features" class="btn btn-outline-light btn-lg px-4">
                            Узнать больше
                        </a>
                    </div>
                    
                    <!-- Статистика -->
                    <div class="row mt-5">
                        <div class="col-4 text-center">
                            <div class="stats-number text-white">500+</div>
                            <div class="text-white-50">Проектов</div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="stats-number text-white">10K+</div>
                            <div class="text-white-50">Задач</div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="stats-number text-white">99%</div>
                            <div class="text-white-50">Uptime</div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6 text-center">
                    <div class="position-relative">
                        <!-- Здесь может быть изображение или демо -->
                        <div class="bg-white rounded shadow-lg p-4 d-inline-block">
                            <div class="row g-2">
                                <div class="col-4">
                                    <div class="bg-secondary rounded p-3 text-center">
                                        <small class="text-muted">TODO</small>
                                        <div class="mt-2">
                                            <div class="bg-primary rounded p-2 mb-2">
                                                <small class="text-white">Задача 1</small>
                                            </div>
                                            <div class="bg-warning rounded p-2">
                                                <small class="text-white">Задача 2</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="bg-secondary rounded p-3 text-center">
                                        <small class="text-muted">В РАБОТЕ</small>
                                        <div class="mt-2">
                                            <div class="bg-info rounded p-2">
                                                <small class="text-white">Задача 3</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="bg-secondary rounded p-3 text-center">
                                        <small class="text-muted">ГОТОВО</small>
                                        <div class="mt-2">
                                            <div class="bg-success rounded p-2">
                                                <small class="text-white">Задача 4</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Возможности -->
    <section id="features" class="section-padding bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center mb-5">
                    <h2 class="display-5 fw-bold mb-3">Почему выбирают KanBan?</h2>
                    <p class="lead text-muted">Мощные возможности для эффективного управления проектами</p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card bg-white rounded p-4 h-100 text-center">
                        <div class="feature-icon">
                            <i class="bi bi-kanban"></i>
                        </div>
                        <h5 class="fw-bold">Kanban Доски</h5>
                        <p class="text-muted">Визуализируйте рабочий процесс с помощью интуитивных kanban досок</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-card bg-white rounded p-4 h-100 text-center">
                        <div class="feature-icon">
                            <i class="bi bi-people"></i>
                        </div>
                        <h5 class="fw-bold">Командная работа</h5>
                        <p class="text-muted">Сотрудничайте с командой в режиме реального времени</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-card bg-white rounded p-4 h-100 text-center">
                        <div class="feature-icon">
                            <i class="bi bi-graph-up"></i>
                        </div>
                        <h5 class="fw-bold">Аналитика</h5>
                        <p class="text-muted">Отслеживайте прогресс и анализируйте производительность</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-card bg-white rounded p-4 h-100 text-center">
                        <div class="feature-icon">
                            <i class="bi bi-bell"></i>
                        </div>
                        <h5 class="fw-bold">Уведомления</h5>
                        <p class="text-muted">Получайте уведомления о важных изменениях в проектах</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-card bg-white rounded p-4 h-100 text-center">
                        <div class="feature-icon">
                            <i class="bi bi-phone"></i>
                        </div>
                        <h5 class="fw-bold">Мобильная версия</h5>
                        <p class="text-muted">Работайте из любого места с адаптивным дизайном</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-card bg-white rounded p-4 h-100 text-center">
                        <div class="feature-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h5 class="fw-bold">Безопасность</h5>
                        <p class="text-muted">Ваши данные защищены современными методами шифрования</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Тарифы -->
    <section id="pricing" class="section-padding">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center mb-5">
                    <h2 class="display-5 fw-bold mb-3">Выберите свой план</h2>
                    <p class="lead text-muted">Гибкие тарифы для команд любого размера</p>
                </div>
            </div>
            
            <div class="row g-4 justify-content-center">
                <!-- Базовый план -->
                <div class="col-lg-4">
                    <div class="pricing-card rounded p-4 h-100 text-center">
                        <h5 class="fw-bold">Базовый</h5>
                        <div class="display-6 fw-bold text-primary my-3">Бесплатно</div>
                        <ul class="list-unstyled">
                            <li class="mb-2">✓ До 3 проектов</li>
                            <li class="mb-2">✓ До 5 участников</li>
                            <li class="mb-2">✓ Базовые отчеты</li>
                            <li class="mb-2">✓ Email поддержка</li>
                        </ul>
                        <a href="{{ route('register') }}" class="btn btn-outline-primary w-100 mt-3">Начать</a>
                    </div>
                </div>
                
                <!-- Профессиональный план -->
                <div class="col-lg-4">
                    <div class="pricing-card featured rounded p-4 h-100 text-center position-relative">
                        <div class="badge bg-primary position-absolute top-0 start-50 translate-middle">Популярный</div>
                        <h5 class="fw-bold">Профессиональный</h5>
                        <div class="display-6 fw-bold text-primary my-3">990₽<small class="fs-6 text-muted">/мес</small></div>
                        <ul class="list-unstyled">
                            <li class="mb-2">✓ Неограниченные проекты</li>
                            <li class="mb-2">✓ До 50 участников</li>
                            <li class="mb-2">✓ Расширенная аналитика</li>
                            <li class="mb-2">✓ Приоритетная поддержка</li>
                            <li class="mb-2">✓ Интеграции</li>
                        </ul>
                        <a href="{{ route('register') }}" class="btn btn-gradient w-100 mt-3">Выбрать план</a>
                    </div>
                </div>
                
                <!-- Корпоративный план -->
                <div class="col-lg-4">
                    <div class="pricing-card rounded p-4 h-100 text-center">
                        <h5 class="fw-bold">Корпоративный</h5>
                        <div class="display-6 fw-bold text-primary my-3">По запросу</div>
                        <ul class="list-unstyled">
                            <li class="mb-2">✓ Все функции Pro</li>
                            <li class="mb-2">✓ Неограниченные участники</li>
                            <li class="mb-2">✓ Собственные серверы</li>
                            <li class="mb-2">✓ SLA 99.9%</li>
                            <li class="mb-2">✓ Персональный менеджер</li>
                        </ul>
                        <a href="#" class="btn btn-outline-primary w-100 mt-3">Связаться</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section id="faq" class="section-padding bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center mb-5">
                    <h2 class="display-5 fw-bold mb-3">Частые вопросы</h2>
                    <p class="lead text-muted">Ответы на самые популярные вопросы</p>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="accordion" id="faqAccordion">
                        <div class="faq-item">
                            <div class="accordion-header" id="faq1">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1">
                                    Как начать работу с KanBan?
                                </button>
                            </div>
                            <div id="collapse1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Просто зарегистрируйтесь, создайте свой первый проект и начните добавлять задачи. Система интуитивно понятна и не требует специального обучения.
                                </div>
                            </div>
                        </div>
                        
                        <div class="faq-item">
                            <div class="accordion-header" id="faq2">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2">
                                    Можно ли работать в команде?
                                </button>
                            </div>
                            <div id="collapse2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Да, KanBan предназначен для командной работы. Вы можете приглашать участников, назначать задачи и отслеживать прогресс в режиме реального времени.
                                </div>
                            </div>
                        </div>
                        
                        <div class="faq-item">
                            <div class="accordion-header" id="faq3">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3">
                                    Есть ли мобильное приложение?
                                </button>
                            </div>
                            <div id="collapse3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    KanBan имеет адаптивный дизайн и отлично работает на всех устройствах через веб-браузер. Мобильное приложение находится в разработке.
                                </div>
                            </div>
                        </div>
                        
                        <div class="faq-item">
                            <div class="accordion-header" id="faq4">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse4">
                                    Как защищены мои данные?
                                </button>
                            </div>
                            <div id="collapse4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Мы используем современные методы шифрования, регулярные резервные копии и соблюдаем все стандарты безопасности для защиты ваших данных.
                                </div>
                            </div>
                        </div>
                        
                        <div class="faq-item">
                            <div class="accordion-header" id="faq5">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse5">
                                    Можно ли сменить тарифный план?
                                </button>
                            </div>
                            <div id="collapse5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Да, вы можете в любое время повысить или понизить свой тарифный план. Изменения вступают в силу немедленно.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Подвал -->
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5 class="fw-bold mb-3">
                        <i class="bi bi-kanban me-2"></i>KanBan
                    </h5>
                    <p class="text-white-50">
                        Современная система управления проектами для эффективной командной работы.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-white-50"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="text-white-50"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-white-50"><i class="bi bi-linkedin"></i></a>
                        <a href="#" class="text-white-50"><i class="bi bi-github"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="fw-bold mb-3">Продукт</h6>
                    <ul class="list-unstyled">
                        <li><a href="#features" class="text-white-50 text-decoration-none">Возможности</a></li>
                        <li><a href="#pricing" class="text-white-50 text-decoration-none">Тарифы</a></li>
                        <li><a href="#" class="text-white-50 text-decoration-none">Безопасность</a></li>
                        <li><a href="#" class="text-white-50 text-decoration-none">Интеграции</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="fw-bold mb-3">Поддержка</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white-50 text-decoration-none">Документация</a></li>
                        <li><a href="#faq" class="text-white-50 text-decoration-none">FAQ</a></li>
                        <li><a href="#" class="text-white-50 text-decoration-none">Контакты</a></li>
                        <li><a href="#" class="text-white-50 text-decoration-none">Статус системы</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="fw-bold mb-3">Компания</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white-50 text-decoration-none">О нас</a></li>
                        <li><a href="#" class="text-white-50 text-decoration-none">Блог</a></li>
                        <li><a href="#" class="text-white-50 text-decoration-none">Карьера</a></li>
                        <li><a href="#" class="text-white-50 text-decoration-none">Пресса</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="fw-bold mb-3">Правовое</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white-50 text-decoration-none">Политика конфиденциальности</a></li>
                        <li><a href="#" class="text-white-50 text-decoration-none">Условия использования</a></li>
                        <li><a href="#" class="text-white-50 text-decoration-none">GDPR</a></li>
                    </ul>
                </div>
            </div>
            
            <hr class="border-secondary my-4">
            
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="text-white-50 mb-0">&copy; 2025 KanBan. Все права защищены.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <small class="text-white-50">Сделано с ❤️ в России</small>
                </div>
            </div>
        </div>
    </footer>

    <!-- Плавная прокрутка -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Плавная прокрутка для ссылок-якорей
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        const offsetTop = target.offsetTop - 70; // Учитываем высоту навбара
                        window.scrollTo({
                            top: offsetTop,
                            behavior: 'smooth'
                        });
                    }
                });
            });
            
            // Изменение прозрачности навбара при скролле
            window.addEventListener('scroll', function() {
                const navbar = document.querySelector('.navbar');
                if (window.scrollY > 50) {
                    navbar.style.backgroundColor = 'rgba(255, 255, 255, 0.95)';
                    navbar.style.backdropFilter = 'blur(10px)';
                } else {
                    navbar.style.backgroundColor = '';
                    navbar.style.backdropFilter = '';
                }
            });
        });
    </script>
</body>
</html>
