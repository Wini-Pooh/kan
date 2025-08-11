<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/css/mobile-responsive.css', 'resources/css/custom-dropdown.css', 'resources/css/kanban-mobile.css', 'resources/js/app.js', 'resources/js/mobile-adaptive.js', 'resources/js/kanban-mobile.js', 'resources/js/column-mobile-optimizer.js', 'resources/js/keyboard-shortcuts.js'])
</head>
<body>
    <div id="app">
      
        <main class="">
            @yield('content')
        </main>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Дополнительная отладка Bootstrap -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Bootstrap загружен:', typeof bootstrap !== 'undefined');
            if (typeof bootstrap !== 'undefined') {
                console.log('Bootstrap version:', bootstrap.Tooltip.VERSION);
            }
        });
    </script>

    <!-- CSRF Token Management -->
    <script>
        class CSRFTokenManager {
            constructor() {
                this.tokenExpiration = null;
                this.checkInterval = 10 * 60 * 1000; // Проверка каждые 10 минут
                this.refreshThreshold = 5 * 60 * 1000; // Обновлять за 5 минут до истечения
                this.init();
            }

            init() {
                this.updateTokenExpiration();
                this.startTokenCheck();
                this.setupAjaxInterceptor();
            }

            // Устанавливаем время истечения токена (по умолчанию Laravel сессии живут 2 часа)
            updateTokenExpiration() {
                this.tokenExpiration = Date.now() + (2 * 60 * 60 * 1000); // 2 часа
            }

            // Получаем текущий CSRF токен
            getCurrentToken() {
                return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            }

            // Обновляем CSRF токен
            updateCSRFToken(newToken) {
                // Обновляем мета-тег
                const metaTag = document.querySelector('meta[name="csrf-token"]');
                if (metaTag) {
                    metaTag.setAttribute('content', newToken);
                }

                // Обновляем все скрытые поля с CSRF токеном
                document.querySelectorAll('input[name="_token"]').forEach(input => {
                    input.value = newToken;
                });

                // Обновляем заголовок для axios/jQuery
                if (window.axios) {
                    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = newToken;
                }

                if (window.$ && $.ajaxSetup) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': newToken
                        }
                    });
                }

                this.updateTokenExpiration();
                console.log('CSRF токен обновлен');
            }

            // Проверяем, нужно ли обновить токен
            shouldRefreshToken() {
                return this.tokenExpiration && (Date.now() > (this.tokenExpiration - this.refreshThreshold));
            }

            // Обновляем токен через AJAX
            async refreshToken() {
                try {
                    const response = await fetch('/csrf-token', {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (response.ok) {
                        const data = await response.json();
                        if (data.csrf_token) {
                            this.updateCSRFToken(data.csrf_token);
                            return true;
                        }
                    }
                } catch (error) {
                    console.error('Ошибка при обновлении CSRF токена:', error);
                }
                return false;
            }

            // Запускаем периодическую проверку токена
            startTokenCheck() {
                setInterval(() => {
                    if (this.shouldRefreshToken()) {
                        this.refreshToken();
                    }
                }, this.checkInterval);
            }

            // Настраиваем перехватчик AJAX запросов
            setupAjaxInterceptor() {
                // Для fetch API
                const originalFetch = window.fetch;
                const self = this;
                window.fetch = async (...args) => {
                    const response = await originalFetch.apply(window, args);
                    
                    // Если получили 419 (токен истек), обновляем токен и повторяем запрос
                    if (response.status === 419) {
                        console.warn('CSRF токен истек, обновляем...');
                        const refreshed = await self.refreshToken();
                        
                        if (refreshed) {
                            // Обновляем заголовки в оригинальном запросе
                            if (args[1] && args[1].headers) {
                                args[1].headers['X-CSRF-TOKEN'] = self.getCurrentToken();
                            }
                            // Повторяем запрос
                            return originalFetch.apply(window, args);
                        }
                    }
                    
                    return response;
                };

                // Для jQuery Ajax
                if (window.$ && $.ajaxPrefilter) {
                    const self = this;
                    $(document).ajaxError(function(event, xhr, settings) {
                        if (xhr.status === 419) {
                            console.warn('CSRF токен истек в jQuery запросе, обновляем...');
                            self.refreshToken().then(refreshed => {
                                if (refreshed) {
                                    // Повторяем запрос с новым токеном
                                    settings.headers = settings.headers || {};
                                    settings.headers['X-CSRF-TOKEN'] = self.getCurrentToken();
                                    $.ajax(settings);
                                }
                            });
                        }
                    });
                }
            }

            // Принудительное обновление токена
            async forceRefresh() {
                return await this.refreshToken();
            }
        }

        // Инициализируем менеджер токенов
        window.csrfManager = new CSRFTokenManager();

        // Глобальная функция для принудительного обновления токена
        window.refreshCSRFToken = function() {
            return window.csrfManager.forceRefresh();
        };
    </script>
</body>
</html>
