<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Painel Administrativo') - Sistema de Agendamento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            line-height: 1.6;
        }

        .header {
            background: #2c3e50;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            z-index: 1000;
        }

        .header h1 {
            font-size: 1.5rem;
        }

        .header .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .menu-toggle {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: -300px;
            width: 300px;
            height: 100vh;
            background: #34495e;
            transition: left 0.3s ease;
            z-index: 999;
            padding-top: 80px;
        }

        .sidebar.open {
            left: 0;
        }

        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 998;
            display: none;
        }

        .sidebar-overlay.show {
            display: block;
        }

        .nav {
            background: #34495e;
            padding: 0 2rem;
        }

        .nav ul {
            list-style: none;
            display: flex;
            gap: 2rem;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar li {
            border-bottom: 1px solid #2c3e50;
        }

        .nav a,
        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 1rem;
            display: block;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }

        .nav a {
            padding: 1rem 0;
        }

        .nav a:hover,
        .nav a.active {
            border-bottom-color: #3498db;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #2c3e50;
            border-left: 4px solid #3498db;
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .card h2 {
            margin-bottom: 1rem;
            color: #2c3e50;
        }

        .btn {
            display: inline-block;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background-color 0.3s;
        }

        .btn-primary {
            background: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background: #2980b9;
        }

        .btn-success {
            background: #27ae60;
            color: white;
        }

        .btn-success:hover {
            background: #229954;
        }

        .btn-warning {
            background: #f39c12;
            color: white;
        }

        .btn-warning:hover {
            background: #e67e22;
        }

        .btn-danger {
            background: #e74c3c;
            color: white;
        }

        .btn-danger:hover {
            background: #c0392b;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .table th,
        .table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .table th {
            background: #f8f9fa;
            font-weight: bold;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }

        .form-control {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .form-control:focus {
            outline: none;
            border-color: #3498db;
        }

        .alert {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .badge-success {
            background: #28a745;
            color: white;
        }

        .badge-danger {
            background: #dc3545;
            color: white;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: row;
                gap: 1rem;
            }

            .menu-toggle {
                display: block;
                order: -1;
            }

            .nav {
                display: none;
            }

            .container {
                padding: 0 1rem;
            }

            .table {
                font-size: 0.9rem;
            }

            .card {
                padding: 1rem;
            }
        }

        @media (min-width: 769px) {
            .sidebar {
                display: none;
            }
        }
    </style>
</head>

<body>
    <header class="header">
        <button class="menu-toggle" onclick="toggleSidebar()">☰</button>
        <h1>@yield('header', 'Painel Administrativo')</h1>
        <div class="user-info">

            <form action="{{ route('admin.logout') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm">Sair</button>
            </form>
        </div>
    </header>

    <!-- Sidebar para mobile -->
    <div class="sidebar-overlay" onclick="closeSidebar()"></div>
    <div class="sidebar" id="sidebar">
        @auth
            @if (auth()->user()->empresa_id === null)
                {{-- Menu para Super Admin --}}
                <ul>
                    <li><a href="{{ route('admin.empresas.index') }}"
                            class="{{ request()->routeIs('admin.empresas.*') ? 'active' : '' }}">Empresas</a></li>
                </ul>
            @else
                {{-- Menu para Admin da Empresa --}}
                <ul>
                    <li><a href="{{ route('admin.dashboard') }}"
                            class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a></li>
                    <li><a href="{{ route('admin.servicos.index') }}"
                            class="{{ request()->routeIs('admin.servicos.*') ? 'active' : '' }}">Serviços</a></li>
                    <li><a href="{{ route('admin.empresa-config.edit') }}"
                            class="{{ request()->routeIs('admin.empresa-config.*') ? 'active' : '' }}">Config</a></li>
                    <li><a href="{{ route('admin.relatorios.atendimentos') }}"
                            class="{{ request()->routeIs('admin.relatorios.*') ? 'active' : '' }}">Relatórios</a></li>
                    <li><a href="{{ route('admin.wpp.config.form') }}"
                            class="{{ request()->routeIs('admin.wpp.*') ? 'active' : '' }}">WhatsApp</a></li>
                    <li><a href="{{ route('admin.working_hours.form') }}"
                            class="{{ request()->routeIs('admin.working_hours.*') ? 'active' : '' }}">Horas</a></li>
                </ul>
            @endif
        @endauth
    </div>

    @auth
        @if (auth()->user()->empresa_id === null)
            {{-- Menu para Super Admin --}}
            <nav class="nav">
                <ul>
                    <li><a href="{{ route('admin.empresas.index') }}"
                            class="{{ request()->routeIs('admin.empresas.*') ? 'active' : '' }}">Empresas</a></li>
                    <li>
                        <a href="{{ route('admin.usuarios.index') }}"
                            class="{{ request()->routeIs('admin.usuarios.*') ? 'active' : '' }}">Usuários</a>
                    </li>
                </ul>
            </nav>
        @else
            {{-- Menu para Admin da Empresa --}}
            <nav class="nav">
                <ul>
                    <li><a href="{{ route('admin.dashboard') }}"
                            class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a></li>
                    <li><a href="{{ route('admin.servicos.index') }}"
                            class="{{ request()->routeIs('admin.servicos.*') ? 'active' : '' }}">Serviços</a></li>
                    <li><a href="{{ route('admin.empresa-config.edit') }}"
                            class="{{ request()->routeIs('admin.empresa-config.*') ? 'active' : '' }}">Config</a></li>
                    <li><a href="{{ route('admin.relatorios.atendimentos') }}"
                            class="{{ request()->routeIs('admin.relatorios.*') ? 'active' : '' }}">Relatórios</a></li>
                    <li><a href="{{ route('admin.wpp.config.form') }}"
                            class="{{ request()->routeIs('admin.wpp.*') ? 'active' : '' }}">WhatsApp</a></li>
                    <li><a href="{{ route('admin.working_hours.form') }}"
                            class="{{ request()->routeIs('admin.working_hours.*') ? 'active' : '' }}">Horas</a></li>
                </ul>
            </nav>
        @endif
    @endauth

    <div class="container">
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">
                <ul style="margin: 0; padding-left: 1rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
    @stack('scripts')

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');

            sidebar.classList.toggle('open');
            overlay.classList.toggle('show');
        }

        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');

            sidebar.classList.remove('open');
            overlay.classList.remove('show');
        }

        // Fechar sidebar ao clicar em um link
        document.querySelectorAll('.sidebar a').forEach(link => {
            link.addEventListener('click', closeSidebar);
        });
    </script>
</body>

</html>
