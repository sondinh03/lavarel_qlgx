<style>
    @import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=DM+Sans:wght@300;400;500&display=swap');

    :root {
        --gold:       #C9A84C;
        --gold-light: #E8D5A3;
        --navy:       #1B2A4A;
        --navy-light: #2A3F6B;
        --cream:      #FAF7F2;
        --stone:      #8B8378;
    }

    .module-select-page {
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

    .module-select-page::before {
        content: '';
        position: fixed;
        inset: 0;
        background-image:
            radial-gradient(circle at 20% 20%, rgba(201, 168, 76, 0.08) 0%, transparent 50%),
            radial-gradient(circle at 80% 80%, rgba(27, 42, 74, 0.06) 0%, transparent 50%);
        pointer-events: none;
        z-index: 0;
    }

    .module-select-page::after {
        content: '';
        position: fixed;
        inset: 0;
        background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23C9A84C' fill-opacity='0.04'%3E%3Cpath d='M30 0 L32 28 L60 30 L32 32 L30 60 L28 32 L0 30 L28 28 Z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        pointer-events: none;
        z-index: 0;
    }

    .ms-wrapper {
        position: relative;
        z-index: 1;
        width: 100%;
        max-width: 860px;
        animation: msFadeUp 0.5s ease both;
    }

    @keyframes msFadeUp {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* Header */
    .ms-header {
        text-align: center;
        margin-bottom: 2.5rem;
    }

    .ms-cross-icon {
        width: 44px;
        height: 44px;
        margin: 0 auto 1rem;
        color: var(--gold);
    }

    .ms-parish-name {
        font-family: 'Cormorant Garamond', serif;
        font-size: 0.75rem;
        letter-spacing: 0.3em;
        text-transform: uppercase;
        color: var(--stone);
        margin-bottom: 0.4rem;
    }

    .ms-greeting {
        font-family: 'Cormorant Garamond', serif;
        font-size: 2rem;
        font-weight: 600;
        color: var(--navy);
        line-height: 1.2;
        margin-bottom: 0.4rem;
    }

    .ms-greeting em {
        font-style: italic;
        color: var(--gold);
    }

    .ms-subtitle {
        font-size: 0.875rem;
        color: var(--stone);
        font-weight: 300;
    }

    /* Divider */
    .ms-divider {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .ms-divider-line {
        flex: 1;
        height: 1px;
        background: linear-gradient(to right, transparent, var(--gold-light), transparent);
    }

    .ms-divider-diamond {
        width: 6px;
        height: 6px;
        background: var(--gold);
        transform: rotate(45deg);
        flex-shrink: 0;
    }

    /* Cards */
    .ms-grid {
        display: grid;
        gap: 1.25rem;
        grid-template-columns: repeat(2, 1fr);
    }

    .ms-card {
        position: relative;
        background: white;
        border: 1px solid rgba(201, 168, 76, 0.2);
        border-radius: 1.25rem;
        padding: 2rem 2rem 3.5rem;
        cursor: pointer;
        text-decoration: none;
        display: block;
        transition: all 0.3s ease;
        overflow: hidden;
        animation: msFadeUp 0.5s ease both;
    }

    .ms-card:nth-child(1) { animation-delay: 0.1s; }
    .ms-card:nth-child(2) { animation-delay: 0.2s; }
    .ms-card:nth-child(3) { animation-delay: 0.3s; }

    .ms-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--gold), var(--gold-light));
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.3s ease;
    }

    .ms-card:hover {
        border-color: var(--gold);
        box-shadow: 0 10px 40px rgba(201, 168, 76, 0.15), 0 2px 8px rgba(0,0,0,0.06);
        transform: translateY(-4px);
    }

    .ms-card:hover::before {
        transform: scaleX(1);
    }

    .ms-card-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.25rem;
        background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    }

    .ms-card-icon svg {
        width: 26px;
        height: 26px;
        color: var(--gold-light);
    }

    .ms-card-label {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--navy);
        margin-bottom: 0.5rem;
    }

    .ms-card-desc {
        font-size: 0.8125rem;
        color: var(--stone);
        line-height: 1.7;
        font-weight: 300;
    }

    .ms-card-arrow {
        position: absolute;
        bottom: 1.5rem;
        right: 1.5rem;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        border: 1px solid rgba(201, 168, 76, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .ms-card:hover .ms-card-arrow {
        background: var(--gold);
        border-color: var(--gold);
    }

    .ms-card-arrow svg {
        width: 14px;
        height: 14px;
        color: var(--stone);
        transition: color 0.3s ease;
    }

    .ms-card:hover .ms-card-arrow svg {
        color: white;
    }

    /* Footer */
    .ms-footer {
        margin-top: 1.75rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1.25rem 1.5rem;
        background: white;
        border: 1px solid rgba(201, 168, 76, 0.15);
        border-radius: 1rem;
    }

    .ms-user-block {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .ms-user-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--navy), var(--navy-light));
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--gold-light);
        flex-shrink: 0;
    }

    .ms-user-name {
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--navy);
        line-height: 1.3;
    }

    .ms-user-role {
        font-size: 0.75rem;
        color: var(--stone);
        margin-top: 0.1rem;
    }

    .ms-logout-btn {
        font-size: 0.8125rem;
        color: var(--stone);
        letter-spacing: 0.04em;
        transition: all 0.2s;
        border: none;
        background: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.5rem 0.875rem;
        border-radius: 0.625rem;
        text-decoration: none;
    }

    .ms-logout-btn:hover {
        color: #e53e3e;
        background: #fff5f5;
    }

    .ms-logout-btn svg {
        width: 15px;
        height: 15px;
    }
</style>

<div class="module-select-page">
    <div class="ms-wrapper">

        {{-- Header --}}
        <div class="ms-header">
            <svg class="ms-cross-icon" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M24 4V44M10 16H38" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
            </svg>
            <p class="ms-parish-name">{{ auth()->user()->parish?->name ?? config('app.name') }}</p>
            <h1 class="ms-greeting">Chào mừng, <em>{{ auth()->user()->name }}</em></h1>
            <p class="ms-subtitle">Vui lòng chọn phân hệ để tiếp tục</p>
        </div>

        {{-- Divider --}}
        <div class="ms-divider">
            <div class="ms-divider-line"></div>
            <div class="ms-divider-diamond"></div>
            <div class="ms-divider-line"></div>
        </div>

        {{-- Module cards --}}
        <div class="ms-grid">
            @foreach($modules as $module)
            <a href="{{ route($module['route']) }}" class="ms-card">

                <div class="ms-card-icon">
                    @if($module['icon'] === 'parishioner')
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25-2.25h-3m1.5-1.5v3" />
                    </svg>
                    @elseif($module['icon'] === 'catechism')
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0118 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                    </svg>
                    @else
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                    </svg>
                    @endif
                </div>

                <p class="ms-card-label">{{ $module['label'] }}</p>
                <p class="ms-card-desc">{{ $module['description'] }}</p>

                <div class="ms-card-arrow">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </div>

            </a>
            @endforeach
        </div>

        {{-- Footer --}}
        <div class="ms-footer">

            <div class="ms-user-block">
                <div class="ms-user-avatar">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div>
                    <p class="ms-user-name">{{ auth()->user()->name }}</p>
                    <p class="ms-user-role">
                        @php
                        $roleName = match(auth()->user()->roles->first()?->name) {
                            'super_admin'  => 'Quản trị hệ thống',
                            'parish_admin' => 'Cha xứ / Quản trị xứ',
                            'catechist'    => 'Giáo lý viên',
                            default        => auth()->user()->roles->first()?->name ?? 'Người dùng',
                        };
                        @endphp
                        {{ $roleName }}
                        @if(auth()->user()->parish?->name)
                        · {{ auth()->user()->parish->name }}
                        @endif
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="ms-logout-btn">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Đăng xuất
                </button>
            </form>

        </div>

    </div>
</div>