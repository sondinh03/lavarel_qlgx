<div class="min-h-screen bg-[#0B0B0F] flex flex-col"
    style="min-height: calc(100vh - var(--bottom-offset));">

    {{-- Loading bar --}}
    <div wire:loading wire:loading.delay.shortest
        class="fixed top-0 left-0 right-0 z-[9999] pointer-events-none">
        <div class="h-0.5 bg-white/10 overflow-hidden">
            <div class="h-full bg-primary-400 animate-[indeterminate_1.4s_ease-in-out_infinite]"></div>
        </div>
    </div>

    {{-- Header dark glass --}}
    <div class="sticky top-0 z-40 flex-shrink-0 flex items-center justify-between px-3 py-3
                bg-black/50 backdrop-blur-xl border-b border-white/[0.08]">
        <div class="flex items-center gap-3 min-w-0">
            <a href="{{ route('dashboard') }}"
                onclick="if(window.stopCamera) window.stopCamera()"
                class="w-9 h-9 rounded-xl bg-white/10 ring-1 ring-white/10
                       flex items-center justify-center text-white/80
                       hover:bg-white/15 active:scale-95 transition touch-feedback"
                aria-label="Quay lại">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div class="text-white font-semibold text-sm tracking-tight leading-tight min-w-0">
                Điểm danh QR
                <div class="text-[11px] text-white/45 font-medium">Mở camera · Quét liên tục</div>
            </div>
        </div>

        {{-- Type toggle --}}
        <div class="flex items-center gap-0.5 bg-white/10 ring-1 ring-white/10 rounded-xl p-1 flex-shrink-0">
            <button wire:click="setType(1)" type="button"
                class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all
                       {{ $type === 1
                           ? 'bg-primary-500 text-white shadow-mac-sm'
                           : 'text-white/50 hover:text-white/80' }}">
                Đi học
            </button>
            <button wire:click="setType(2)" type="button"
                class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all
                       {{ $type === 2
                           ? 'bg-primary-500 text-white shadow-mac-sm'
                           : 'text-white/50 hover:text-white/80' }}">
                Đi lễ
            </button>
        </div>
    </div>

    {{-- Camera View --}}
    <div id="camera-container" class="relative flex-shrink-0 bg-black overflow-hidden"
        style="height: min(62vw, 360px);">

        <video id="qr-video"
            class="w-full h-full object-cover"
            playsinline autoplay muted></video>

        {{-- Soft vignette --}}
        <div class="absolute inset-0 pointer-events-none
            bg-[radial-gradient(ellipse_at_center,transparent_45%,rgba(0,0,0,0.45)_100%)]"></div>

        {{-- Viewfinder overlay --}}
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
            <div class="relative w-[min(68vw,16rem)] h-[min(68vw,16rem)] max-w-[85%] max-h-[85%]">
                <div class="absolute top-0 left-0 w-8 h-8 border-t-[3px] border-l-[3px] border-primary-400/90 rounded-tl-xl"></div>
                <div class="absolute top-0 right-0 w-8 h-8 border-t-[3px] border-r-[3px] border-primary-400/90 rounded-tr-xl"></div>
                <div class="absolute bottom-0 left-0 w-8 h-8 border-b-[3px] border-l-[3px] border-primary-400/90 rounded-bl-xl"></div>
                <div class="absolute bottom-0 right-0 w-8 h-8 border-b-[3px] border-r-[3px] border-primary-400/90 rounded-br-xl"></div>
                <div class="absolute inset-x-2 top-[12%] bottom-[12%]">
                    <div class="qr-scan-line absolute left-0 right-0 h-0.5
                        bg-gradient-to-r from-transparent via-primary-400 to-transparent"></div>
                </div>
            </div>
        </div>

        {{-- Flip camera --}}
        <button type="button"
            id="camera-flip-btn"
            onclick="if(window.switchCamera) window.switchCamera()"
            class="absolute bottom-3 right-3 z-10 w-10 h-10 rounded-full
                   bg-black/55 backdrop-blur-md ring-1 ring-white/15 text-white/90
                   flex items-center justify-center touch-feedback active:scale-95
                   hover:bg-black/70 transition"
            aria-label="Đổi camera">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
        </button>

        {{-- Result overlay --}}
        @if($lastResult)
        <div class="absolute inset-0 flex items-center justify-center backdrop-blur-sm
                    {{ $lastResultType === 'success' ? 'bg-emerald-950/75' :
                       ($lastResultType === 'warning' ? 'bg-amber-950/75' : 'bg-red-950/75') }}">
            <div class="mx-5 w-full max-w-xs rounded-2xl bg-black/40 ring-1 ring-white/10
                backdrop-blur-xl px-6 py-5 text-center shadow-mac">
                @if($lastResultType === 'success')
                <div class="w-14 h-14 bg-emerald-500/90 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-mac-sm">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                @elseif($lastResultType === 'warning')
                <div class="w-14 h-14 bg-amber-400/95 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-mac-sm">
                    <svg class="w-8 h-8 text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                @else
                <div class="w-14 h-14 bg-red-500/90 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-mac-sm">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                @endif

                @if(isset($lastResult['saint_name']) && $lastResult['saint_name'])
                <div class="text-white/55 text-sm">{{ $lastResult['saint_name'] }}</div>
                @endif
                @if(isset($lastResult['student_name']))
                <div class="text-white font-semibold tracking-tight text-lg">{{ $lastResult['student_name'] }}</div>
                @if(isset($lastResult['class_name']))
                <div class="text-white/45 text-xs mt-0.5">{{ $lastResult['class_name'] }}</div>
                @endif
                @endif

                <div class="mt-2 text-white/90 text-sm font-medium">{{ $lastResult['message'] }}</div>

                @if(isset($lastResult['time']))
                <div class="text-white/45 text-xs mt-1">Lúc {{ $lastResult['time'] }}</div>
                @endif
            </div>
        </div>
        @endif
    </div>

    {{-- Hint --}}
    <div class="flex-shrink-0 py-2.5 text-center text-xs text-white/35">
        Đưa mã QR vào khung hình · Giữ ổn định 1–2 giây
    </div>

    {{-- Scanned Log --}}
    <div class="flex-1 overflow-y-auto px-3 pb-2">
        @if(count($scannedLog) > 0)
        <div class="rounded-2xl bg-white/[0.04] ring-1 ring-white/[0.08] overflow-hidden">
            <div class="px-4 py-2.5 flex items-center justify-between border-b border-white/[0.06]">
                <span class="text-xs font-semibold text-white/45 uppercase tracking-wide">
                    Vừa điểm danh
                </span>
                <span class="text-xs text-white/35">{{ count($scannedLog) }} học sinh</span>
            </div>
            <div class="divide-y divide-white/[0.06]">
                @foreach($scannedLog as $log)
                <div class="flex items-center gap-3 px-4 py-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500/15 ring-1 ring-emerald-400/20
                        flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-white text-sm font-medium tracking-tight truncate">{{ $log['student_name'] }}</div>
                        <div class="text-white/40 text-xs truncate">
                            @php
                                $saint = ($log['saint_name'] ?? '') && ($log['saint_name'] ?? '') !== '-' ? $log['saint_name'] : '';
                            @endphp
                            {{ $saint ? $saint . ' · ' : '' }}{{ $log['class_name'] }}
                        </div>
                    </div>
                    <div class="text-white/35 text-xs flex-shrink-0">{{ $log['time'] }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @else
        <div class="flex items-center justify-center h-32 px-4 rounded-2xl
            bg-white/[0.03] ring-1 ring-white/[0.06]">
            <p class="text-white/30 text-sm text-center leading-relaxed">
                Chưa có ai được quét.<br>Đưa QR vào khung và chờ nhận diện.
            </p>
        </div>
        @endif
    </div>

    {{-- Footer --}}
    <div class="sticky bottom-0 z-40 flex-shrink-0 px-4 py-3
        bg-black/55 backdrop-blur-xl border-t border-white/[0.08]"
        style="padding-bottom: calc(12px + var(--safe-bottom));">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs text-white/45">
                Đã quét <span class="text-white font-semibold">{{ count($scannedLog) }}</span> học sinh
            </span>
            <span class="text-xs text-white/30">{{ now()->format('d/m/Y') }}</span>
        </div>
        <a href="{{ route('dashboard') }}"
            onclick="if(window.stopCamera) window.stopCamera()"
            class="block w-full py-3 rounded-xl text-center font-semibold text-sm
                  transition-all touch-feedback active:scale-95
                  {{ count($scannedLog) > 0
                      ? 'bg-primary-500 hover:bg-primary-600 text-white shadow-mac-sm'
                      : 'bg-white/10 text-white/50 ring-1 ring-white/10' }}">
            {{ count($scannedLog) > 0 ? 'Hoàn thành' : 'Thoát' }}
        </a>
    </div>
</div>

@push('styles')
<style>
    .qr-scan-line {
        top: 0;
        will-change: top, opacity;
        animation: qr-scan-line 2s ease-in-out infinite;
    }

    @keyframes qr-scan-line {
        0%,
        100% {
            top: 0;
            opacity: 0.35;
        }

        50% {
            top: calc(100% - 2px);
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
        let facingMode = 'environment';
        let activeVideo = null;

        window.stopCamera = function() {
            scanning = false;
            if (currentStream) {
                currentStream.getTracks().forEach(t => t.stop());
                currentStream = null;
            }
        };

        window.switchCamera = function() {
            const video = document.getElementById('qr-video');
            if (!video) return;

            if (currentStream) {
                currentStream.getTracks().forEach(t => t.stop());
                currentStream = null;
            }

            facingMode = facingMode === 'environment' ? 'user' : 'environment';
            scanning = true;
            startCamera(video, facingMode);
        };

        document.addEventListener('livewire:navigating', window.stopCamera);
        window.addEventListener('pagehide', window.stopCamera);
        document.addEventListener('livewire:request-error', function() {
            cooldown = false;
        });

        document.addEventListener('livewire:load', function() {
            waitForElement('qr-video', function(video) {
                activeVideo = video;
                startCamera(video, facingMode);

                document.addEventListener('visibilitychange', function() {
                    if (document.hidden) {
                        scanning = false;
                    } else if (currentStream && activeVideo) {
                        scanning = true;
                        requestAnimationFrame(() => tick(activeVideo));
                    }
                });
            });

            if (!qrDoneListenerAdded) {
                qrDoneListenerAdded = true;
                window.addEventListener('qr-done', function() {
                    setTimeout(function() {
                        cooldown = false;
                    }, 1500);
                });
            }

            function waitForElement(id, callback, maxTries = 20) {
                const el = document.getElementById(id);
                if (el) {
                    callback(el);
                } else if (maxTries > 0) {
                    setTimeout(() => waitForElement(id, callback, maxTries - 1), 100);
                }
            }

            function buildConstraints(mode) {
                return [{
                        video: {
                            facingMode: {
                                exact: mode
                            }
                        }
                    },
                    {
                        video: {
                            facingMode: mode
                        }
                    },
                    {
                        video: true
                    },
                ];
            }

            function startCamera(video, mode) {
                if (!navigator.mediaDevices?.getUserMedia) {
                    showCameraError('Trình duyệt không hỗ trợ camera');
                    return;
                }

                facingMode = mode || facingMode;
                tryGetUserMedia(buildConstraints(facingMode), 0, video);
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
                        @this.call('handleQrScanned', String(code.data).trim());
                    }
                }

                requestAnimationFrame(() => tick(video));
            }

            function showCameraError(message) {
                const container = document.getElementById('camera-container');
                if (!container) return;
                container.innerHTML =
                    '<div class="absolute inset-0 flex flex-col items-center justify-center gap-3 px-6">' +
                    '<svg class="w-12 h-12 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" ' +
                    'd="M15 10l4.553-2.069A1 1 0 0121 8.868v6.264a1 1 0 01-1.447.894L15 14' +
                    'M3 8a2 2 0 00-2 2v4a2 2 0 002 2h8a2 2 0 002-2v-4a2 2 0 00-2-2H3z"/>' +
                    '</svg>' +
                    '<p class="text-white/50 text-sm text-center">' + message + '</p>' +
                    '<p class="text-white/25 text-xs text-center">iOS: Vào Cài đặt → Safari → Camera → Cho phép</p>' +
                    '</div>';
            }

            function showPlayButton(video) {
                const container = document.getElementById('camera-container');
                if (!container) return;
                const btn = document.createElement('button');
                btn.className = 'absolute inset-0 flex flex-col items-center justify-center gap-3 bg-black/60 backdrop-blur-sm';
                btn.innerHTML =
                    '<div class="w-16 h-16 bg-primary-500 rounded-full flex items-center justify-center shadow-mac">' +
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
