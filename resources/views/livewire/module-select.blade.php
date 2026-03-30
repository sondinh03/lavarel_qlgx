<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Chọn phân hệ — {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet" />
    <style>
        :root {
            --gold: #C9A84C;
            --gold-light: #E8D5A3;
            --navy: #1B2A4A;
            --navy-light: #2A3F6B;
            --cream: #FAF7F2;
            --stone: #8B8378;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background-color: var(--cream);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }

        /* Background pattern */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                radial-gradient(circle at 20% 20%, rgba(201, 168, 76, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(27, 42, 74, 0.06) 0%, transparent 50%);
            pointer-events: none;
        }

        body::after {
            content: '';
            position: fixed;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23C9A84C' fill-opacity='0.04'%3E%3Cpath d='M30 0 L32 28 L60 30 L32 32 L30 60 L28 32 L0 30 L28 28 Z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            pointer-events: none;
            z-index: 0;
        }

        .wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 680px;
            animation: fadeUp 0.6s ease both;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(24px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .cross-icon {
            width: 48px;
            height: 48px;
            margin: 0 auto 1.25rem;
            color: var(--gold);
        }

        .parish-name {
            font-family: 'Cormorant Garamond', serif;
            font-size: 0.8rem;
            letter-spacing: 0.25em;
            text-transform: uppercase;
            color: var(--stone);
            margin-bottom: 0.5rem;
        }

        .greeting {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2rem;
            font-weight: 600;
            color: var(--navy);
            line-height: 1.2;
            margin-bottom: 0.5rem;
        }

        .greeting em {
            font-style: italic;
            color: var(--gold);
        }

        .subtitle {
            font-size: 0.875rem;
            color: var(--stone);
            font-weight: 300;
        }

        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .divider-line {
            flex: 1;
            height: 1px;
            background: linear-gradient(to right, transparent, var(--gold-light), transparent);
        }

        .divider-diamond {
            width: 6px;
            height: 6px;
            background: var(--gold);
            transform: rotate(45deg);
            flex-shrink: 0;
        }

        /* Module cards */
        .modules-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        }

        .module-card {
            position: relative;
            background: white;
            border: 1px solid rgba(201, 168, 76, 0.2);
            border-radius: 1rem;
            padding: 2rem;
            cursor: pointer;
            text-decoration: none;
            display: block;
            transition: all 0.3s ease;
            overflow: hidden;
            animation: fadeUp 0.6s ease both;
        }

        .module-card:nth-child(1) {
            animation-delay: 0.1s;
        }

        .module-card:nth-child(2) {
            animation-delay: 0.2s;
        }

        .module-card:nth-child(3) {
            animation-delay: 0.3s;
        }

        .module-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--gold), var(--gold-light));
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
        }

        .module-card:hover {
            border-color: var(--gold);
            box-shadow: 0 8px 32px rgba(201, 168, 76, 0.15), 0 2px 8px rgba(0, 0, 0, 0.06);
            transform: translateY(-3px);
        }

        .module-card:hover::before {
            transform: scaleX(1);
        }

        .card-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.25rem;
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
        }

        .card-icon svg {
            width: 24px;
            height: 24px;
            color: var(--gold-light);
        }

        .card-label {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.375rem;
            font-weight: 600;
            color: var(--navy);
            margin-bottom: 0.375rem;
        }

        .card-desc {
            font-size: 0.8125rem;
            color: var(--stone);
            line-height: 1.6;
            font-weight: 300;
        }

        .card-arrow {
            position: absolute;
            bottom: 1.5rem;
            right: 1.5rem;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: 1px solid rgba(201, 168, 76, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .module-card:hover .card-arrow {
            background: var(--gold);
            border-color: var(--gold);
        }

        .card-arrow svg {
            width: 14px;
            height: 14px;
            color: var(--stone);
            transition: color 0.3s ease;
        }

        .module-card:hover .card-arrow svg {
            color: white;
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 2.5rem;
        }

        .user-info {
            font-size: 0.8125rem;
            color: var(--stone);
            margin-bottom: 0.75rem;
        }

        .user-info strong {
            color: var(--navy);
            font-weight: 500;
        }

        .logout-btn {
            font-size: 0.8125rem;
            color: var(--stone);
            text-decoration: none;
            letter-spacing: 0.05em;
            transition: color 0.2s;
            border: none;
            background: none;
            cursor: pointer;
        }

        .logout-btn:hover {
            color: var(--navy);
        }
    </style>
</head>

<body>

    <div class="wrapper">

        {{-- Header --}}
        <div class="header">
            {{-- Cross icon --}}
            <svg class="cross-icon" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M24 4V44M10 16H38" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
            </svg>

            <p class="parish-name">{{ auth()->user()->parish?->name ?? config('app.name') }}</p>
            <h1 class="greeting">Chào mừng, <em>{{ auth()->user()->name }}</em></h1>
            <p class="subtitle">Vui lòng chọn phân hệ để tiếp tục</p>
        </div>

        {{-- Divider --}}
        <div class="divider">
            <div class="divider-line"></div>
            <div class="divider-diamond"></div>
            <div class="divider-line"></div>
        </div>

        {{-- Module cards --}}
        <div class="modules-grid">
            @foreach($modules as $module)
            <a href="{{ route($module['route']) }}" class="module-card">

                <div class="card-icon">
                    @if($module['icon'] === 'parishioner')
                    {{-- Icon giáo dân: người + thánh giá --}}
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25-2.25h-3m1.5-1.5v3" />
                    </svg>
                    @elseif($module['icon'] === 'catechism')
                    {{-- Icon giáo lý: sách --}}
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                    </svg>
                    @else
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                    </svg>
                    @endif
                </div>

                <p class="card-label">{{ $module['label'] }}</p>
                <p class="card-desc">{{ $module['description'] }}</p>

                <div class="card-arrow">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </div>

            </a>
            @endforeach
        </div>

        {{-- Footer --}}
        <div class="footer">
            <p class="user-info">
                Đăng nhập với tư cách <strong>{{ auth()->user()->name }}</strong>
                @php
                $roleName = match(auth()->user()->roles->first()?->name) {
                'super_admin' => 'Quản trị hệ thống',
                'parish_admin' => 'Cha xứ / Quản trị xứ',
                'catechism_admin'=> 'Quản lý giáo lý',
                'catechist' => 'Giáo lý viên',
                'secretary' => 'Thư ký',
                default => auth()->user()->roles->first()?->name ?? '',
                };
                @endphp
                · {{ $roleName }}
            </p>

            <form method="POST" action="{{ route('logout') }}" style="display:inline">
                @csrf
                <button type="submit" class="logout-btn">Đăng xuất</button>
            </form>
        </div>

    </div>

</body>

</html>