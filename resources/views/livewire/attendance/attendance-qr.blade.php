<div class="min-h-screen bg-slate-900 flex flex-col">

    {{-- Loading bar --}}
    <div wire:loading wire:loading.delay.shortest
        class="fixed top-0 left-0 right-0 z-[9999] pointer-events-none">
        <div class="h-0.5 bg-primary-800 overflow-hidden">
            <div class="h-full bg-primary-400 animate-[indeterminate_1.4s_ease-in-out_infinite]"></div>
        </div>
    </div>

    {{-- Header --}}
    <div class="flex-shrink-0 flex items-center justify-between px-4 py-3
                bg-slate-800 border-b border-slate-700">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard') }}"
                class="w-9 h-9 rounded-lg bg-slate-700 flex items-center justify-center
                       text-slate-300 hover:bg-slate-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div class="text-white font-semibold text-sm">Điểm danh QR</div>
        </div>

        {{-- Type toggle --}}
        <div class="flex items-center gap-1 bg-slate-700 rounded-lg p-1">
            <button wire:click="setType(1)"
                class="px-3 py-1.5 rounded-md text-sm font-medium transition-colors
                       {{ $type === 1
                           ? 'bg-primary-500 text-white'
                           : 'text-slate-400 hover:text-slate-200' }}">
                Học
            </button>
            <button wire:click="setType(2)"
                class="px-3 py-1.5 rounded-md text-sm font-medium transition-colors
                       {{ $type === 2
                           ? 'bg-primary-500 text-white'
                           : 'text-slate-400 hover:text-slate-200' }}">
                Lễ
            </button>
        </div>
    </div>

    {{-- Camera View --}}
    <div id="camera-container" class="relative flex-shrink-0 bg-black"
        style="height: 60vw; max-height: 320px;">

        {{-- Video element — JS inject stream vào đây --}}
        <video id="qr-video"
            class="w-full h-full object-cover"
            playsinline autoplay muted></video>

        {{-- Viewfinder overlay --}}
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
            <div class="relative w-52 h-52">
                <div class="absolute top-0 left-0 w-8 h-8 border-t-4 border-l-4 border-primary-400 rounded-tl-lg"></div>
                <div class="absolute top-0 right-0 w-8 h-8 border-t-4 border-r-4 border-primary-400 rounded-tr-lg"></div>
                <div class="absolute bottom-0 left-0 w-8 h-8 border-b-4 border-l-4 border-primary-400 rounded-bl-lg"></div>
                <div class="absolute bottom-0 right-0 w-8 h-8 border-b-4 border-r-4 border-primary-400 rounded-br-lg"></div>
                <div class="absolute left-2 right-2 h-0.5 bg-primary-400/70
                            animate-[scan-line_2s_ease-in-out_infinite]"
                    style="top: 50%"></div>
            </div>
        </div>

        {{-- Result overlay --}}
        @if($lastResult)
        <div class="absolute inset-0 flex items-center justify-center
                    {{ $lastResultType === 'success' ? 'bg-green-900/80' :
                       ($lastResultType === 'warning' ? 'bg-yellow-900/80' : 'bg-red-900/80') }}">
            <div class="text-center px-6">
                {{-- Icon --}}
                @if($lastResultType === 'success')
                <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                @elseif($lastResultType === 'warning')
                <div class="w-16 h-16 bg-yellow-400 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-9 h-9 text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                @else
                <div class="w-16 h-16 bg-red-500 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                @endif

                @if(isset($lastResult['student_name']))
                <div class="text-white font-bold text-lg">{{ $lastResult['student_name'] }}</div>
                @if(isset($lastResult['saint_name']) && $lastResult['saint_name'])
                <div class="text-white/70 text-sm">{{ $lastResult['saint_name'] }}</div>
                @endif
                @if(isset($lastResult['class_name']))
                <div class="text-white/60 text-xs mt-0.5">{{ $lastResult['class_name'] }}</div>
                @endif
                @endif

                <div class="mt-2 text-white/90 text-sm font-medium">{{ $lastResult['message'] }}</div>

                @if(isset($lastResult['time']))
                <div class="text-white/60 text-xs mt-1">Lúc {{ $lastResult['time'] }}</div>
                @endif
            </div>
        </div>
        @endif
    </div>

    {{-- Hint --}}
    <div class="flex-shrink-0 py-2 text-center text-xs text-slate-500">
        Đưa mã QR của học sinh vào khung hình
    </div>

    {{-- Scanned Log --}}
    <div class="flex-1 overflow-y-auto">
        @if(count($scannedLog) > 0)
        <div class="px-4 py-2 flex items-center justify-between
                    border-b border-slate-800">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                Vừa điểm danh
            </span>
            <span class="text-xs text-slate-500">{{ count($scannedLog) }} học sinh</span>
        </div>
        <div class="divide-y divide-slate-800">
            @foreach($scannedLog as $log)
            <div class="flex items-center gap-3 px-4 py-3">
                <div class="w-8 h-8 rounded-full bg-green-500/20 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-white text-sm font-medium">{{ $log['student_name'] }}</div>
                    <div class="text-slate-500 text-xs">
                        {{ $log['saint_name'] ? $log['saint_name'] . ' · ' : '' }}{{ $log['class_name'] }}
                    </div>
                </div>
                <div class="text-slate-500 text-xs flex-shrink-0">{{ $log['time'] }}</div>
            </div>
            @endforeach
        </div>
        @else
        <div class="flex items-center justify-center h-32">
            <p class="text-slate-600 text-sm">Chưa có học sinh nào được quét</p>
        </div>
        @endif
    </div>

    {{-- Footer: nút hoàn thành --}}
    <div class="flex-shrink-0 px-4 py-3 bg-slate-800 border-t border-slate-700">
        <div class="flex items-center justify-between mb-1">
            <span class="text-xs text-slate-400">
                Đã quét <span class="text-white font-semibold">{{ count($scannedLog) }}</span> học sinh
            </span>
            <span class="text-xs text-slate-500">{{ now()->format('d/m/Y') }}</span>
        </div>
        <a href="{{ route('dashboard') }}"
            onclick="if(window.stopCamera) window.stopCamera()"
            class="block w-full py-3 rounded-xl text-center font-semibold text-sm
                  transition-all touch-feedback active:scale-95
                  {{ count($scannedLog) > 0
                      ? 'bg-primary-500 hover:bg-primary-600 text-white'
                      : 'bg-slate-700 text-slate-400' }}">
            {{ count($scannedLog) > 0 ? 'Hoàn thành' : 'Thoát' }}
        </a>
    </div>
</div>

@push('styles')
<style>
    @keyframes scan-line {

        0%,
        100% {
            transform: translateY(-96px);
            opacity: 0.3;
        }

        50% {
            transform: translateY(96px);
            opacity: 1;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/jsqr@1.4.0/dist/jsQR.js"></script>
<script>
    (function() {
        let cooldown = false;
        let scanning = true;
        let currentStream = null;
        let qrDoneListenerAdded = false;

        window.stopCamera = function() {
            scanning = false;
            if (currentStream) {
                currentStream.getTracks().forEach(t => t.stop());
                currentStream = null;
            }
        };

        document.addEventListener('livewire:navigating', window.stopCamera);
        window.addEventListener('pagehide', window.stopCamera);
        document.addEventListener('livewire:load', function() {

            // ── Camera setup ──────────────────────────────────────────────
            waitForElement('qr-video', function(video) {
                startCamera(video);

                document.addEventListener('visibilitychange', function() {
                    if (document.hidden) {
                        scanning = false;
                    } else if (currentStream) {
                        scanning = true;
                        requestAnimationFrame(() => tick(video));
                    }
                });
            });

            // ── qr-done listener (chỉ đăng ký 1 lần) ────────────────────
            if (!qrDoneListenerAdded) {
                qrDoneListenerAdded = true;
                window.addEventListener('qr-done', function() {
                    setTimeout(function() {
                        cooldown = false;
                    }, 1500);
                });
            }

            // ── Helpers ───────────────────────────────────────────────────
            function waitForElement(id, callback, maxTries = 20) {
                const el = document.getElementById(id);
                if (el) {
                    callback(el);
                } else if (maxTries > 0) {
                    setTimeout(() => waitForElement(id, callback, maxTries - 1), 100);
                }
            }

            function startCamera(video) {
                if (!navigator.mediaDevices?.getUserMedia) {
                    showCameraError('Trình duyệt không hỗ trợ camera');
                    return;
                }

                const constraints = [{
                        video: {
                            facingMode: {
                                exact: 'environment'
                            }
                        }
                    },
                    {
                        video: {
                            facingMode: 'environment'
                        }
                    },
                    {
                        video: true
                    },
                ];

                tryGetUserMedia(constraints, 0, video);
            }

            function tryGetUserMedia(constraints, index, video) {
                if (index >= constraints.length) {
                    showCameraError('Không thể truy cập camera. Kiểm tra quyền trong Cài đặt.');
                    return;
                }

                navigator.mediaDevices.getUserMedia(constraints[index])
                    .then(function(stream) {
                        currentStream = stream;

                        stream.getVideoTracks().forEach(function(track) {
                            track.addEventListener('ended', function() {
                                showCameraError('Camera bị ngắt. Vui lòng tải lại trang.');
                                scanning = false;
                            });
                        });

                        video.srcObject = stream;

                        const p = video.play();
                        if (p !== undefined) {
                            p.then(() => requestAnimationFrame(() => tick(video)))
                                .catch(() => showPlayButton(video));
                        } else {
                            requestAnimationFrame(() => tick(video));
                        }
                    })
                    .catch(function(err) {
                        console.warn('Camera constraint ' + index + ' failed:', err.name);
                        tryGetUserMedia(constraints, index + 1, video);
                    });
            }

            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');

            function tick(video) {
                if (!scanning) return;

                if (video.readyState === video.HAVE_ENOUGH_DATA) {
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                    const code = jsQR(imageData.data, imageData.width, imageData.height);

                    if (code && !cooldown) {
                        cooldown = true;
                        @this.call('handleQrScanned', code.data);
                    }
                }

                requestAnimationFrame(() => tick(video));
            }

            function showCameraError(message) {
                const container = document.getElementById('camera-container');
                if (!container) return;
                container.innerHTML =
                    '<div class="absolute inset-0 flex flex-col items-center justify-center gap-3 px-6">' +
                    '<svg class="w-12 h-12 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" ' +
                    'd="M15 10l4.553-2.069A1 1 0 0121 8.868v6.264a1 1 0 01-1.447.894L15 14' +
                    'M3 8a2 2 0 00-2 2v4a2 2 0 002 2h8a2 2 0 002-2v-4a2 2 0 00-2-2H3z"/>' +
                    '</svg>' +
                    '<p class="text-slate-400 text-sm text-center">' + message + '</p>' +
                    '<p class="text-slate-600 text-xs text-center">iOS: Vào Cài đặt → Safari → Camera → Cho phép</p>' +
                    '</div>';
            }

            function showPlayButton(video) {
                const container = document.getElementById('camera-container');
                if (!container) return;
                const btn = document.createElement('button');
                btn.className = 'absolute inset-0 flex flex-col items-center justify-center gap-3 bg-black/60';
                btn.innerHTML =
                    '<div class="w-16 h-16 bg-primary-500 rounded-full flex items-center justify-center">' +
                    '<svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">' +
                    '<path d="M8 5v14l11-7z"/>' +
                    '</svg>' +
                    '</div>' +
                    '<span class="text-white text-sm">Nhấn để bật camera</span>';
                btn.addEventListener('click', function() {
                    video.play().then(function() {
                        btn.remove();
                        requestAnimationFrame(() => tick(video));
                    });
                });
                container.appendChild(btn);
            }
        });
    })();
</script>
@endpush