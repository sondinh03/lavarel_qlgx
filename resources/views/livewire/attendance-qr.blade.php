<div class="min-h-screen bg-slate-900 flex flex-col">

    {{-- Loading bar --}}
    <div wire:loading wire:loading.delay.shortest
        class="fixed top-0 left-0 right-0 z-[9999] pointer-events-none">
        <div class="h-0.5 bg-primary-800 overflow-hidden">
            <div class="h-full bg-primary-400
                        animate-[indeterminate_1.4s_ease-in-out_infinite]"></div>
        </div>
    </div>

    {{-- Header --}}
    <div class="flex-shrink-0 flex items-center justify-between px-4 py-3
                bg-slate-800 border-b border-slate-700">
        <div class="flex items-center gap-3">
            <a href="{{ route('attendance.show', ['classId' => $classId, 'type' => $type]) }}"
                class="w-9 h-9 rounded-lg bg-slate-700 flex items-center justify-center
                      text-slate-300 hover:bg-slate-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <div class="text-white font-semibold text-sm leading-tight">
                    {{ $classInfo['name'] ?? 'Quét QR' }}
                </div>
                <div class="text-slate-400 text-xs">
                    {{ $session['date'] ?? '' }}
                    • {{ $type == 1 ? 'Đi học' : 'Đi lễ' }}
                    @if($session['locked'] ?? false)
                    • <span class="text-red-400">Đã khóa</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Counter --}}
        <div class="text-right">
            <div class="text-2xl font-bold text-white">
                {{ count($scannedLog) }}
                <span class="text-slate-500 text-base font-normal">/ {{ $totalStudents }}</span>
            </div>
            <div class="text-xs text-slate-400">đã điểm danh</div>
        </div>
    </div>

    {{-- Camera View --}}
    @if(!($session['locked'] ?? false))
    <div id="camera-container" class="relative flex-shrink-0 bg-black" style="height: 60vw; max-height: 320px;">

        {{-- Video element — JS sẽ inject stream vào đây --}}
        <video id="qr-video"
            class="w-full h-full object-cover"
            playsinline autoplay muted></video>

        {{-- Viewfinder overlay --}}
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
            <div class="relative w-52 h-52">
                {{-- 4 góc --}}
                <div class="absolute top-0 left-0 w-8 h-8 border-t-4 border-l-4 border-primary-400 rounded-tl-lg"></div>
                <div class="absolute top-0 right-0 w-8 h-8 border-t-4 border-r-4 border-primary-400 rounded-tr-lg"></div>
                <div class="absolute bottom-0 left-0 w-8 h-8 border-b-4 border-l-4 border-primary-400 rounded-bl-lg"></div>
                <div class="absolute bottom-0 right-0 w-8 h-8 border-b-4 border-r-4 border-primary-400 rounded-br-lg"></div>

                {{-- Scan line animation --}}
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

                {{-- Student name --}}
                @if(isset($lastResult['student_name']))
                <div class="text-white font-bold text-lg">{{ $lastResult['student_name'] }}</div>
                @if(isset($lastResult['saint_name']))
                <div class="text-white/70 text-sm">{{ $lastResult['saint_name'] }}</div>
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

    @else
    {{-- Locked state --}}
    <div class="flex-1 flex items-center justify-center">
        <div class="text-center px-6">
            <div class="w-16 h-16 bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <div class="text-white font-semibold">Buổi học đã khóa</div>
            <div class="text-slate-400 text-sm mt-1">Không thể điểm danh QR cho buổi này</div>
        </div>
    </div>
    @endif

    {{-- Scanned Log --}}
    <div class="flex-1 overflow-y-auto">
        @if(count($scannedLog) > 0)
        <div class="px-4 py-2 text-xs font-semibold text-slate-400 uppercase tracking-wider
                    border-b border-slate-800">
            Vừa điểm danh
        </div>
        <div class="divide-y divide-slate-800">
            @foreach($scannedLog as $log)
            <div class="flex items-center gap-3 px-4 py-3">
                <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0
                            {{ $log['status'] == 1 ? 'bg-green-500/20' : 'bg-yellow-500/20' }}">
                    @if($log['status'] == 1)
                    <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                    @else
                    <span class="text-yellow-400 font-bold text-xs">P</span>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-white text-sm font-medium">{{ $log['student_name'] }}</div>
                    @if($log['saint_name'])
                    <div class="text-slate-500 text-xs">{{ $log['saint_name'] }}</div>
                    @endif
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
    let cooldown = false;
    let scanning = true;
    let currentStream = null;
    let qrDoneListenerAdded = false;

    function stopCamera() {
        scanning = false;
        if (currentStream) {
            currentStream.getTracks().forEach(t => t.stop());
            currentStream = null;
        }
    }

    // Stop khi rời trang
    document.addEventListener('livewire:navigating', stopCamera);
    window.addEventListener('pagehide', stopCamera); // iOS Safari

    // Safety net khi mất mạng
    document.addEventListener('livewire:request-error', function() {
        cooldown = false;
    });

    document.addEventListener('livewire:load', function() {

        function waitForElement(id, callback, maxTries = 20) {
            const el = document.getElementById(id);
            if (el) {
                callback(el);
            } else if (maxTries > 0) {
                setTimeout(() => waitForElement(id, callback, maxTries - 1), 100);
            } else {
                console.error('Element #' + id + ' not found after waiting');
            }
        }

        @if(!($session['locked'] ?? false))
        waitForElement('qr-video', function(video) {
            startCamera(video);

            // ← video trong scope đúng ở đây
            document.addEventListener('visibilitychange', function() {
                if (document.hidden) {
                    scanning = false;
                } else if (currentStream) {
                    scanning = true;
                    requestAnimationFrame(function() {
                        tick(video);
                    });
                }
            });
        });
        @endif

        if (!qrDoneListenerAdded) {
            qrDoneListenerAdded = true;
            window.addEventListener('qr-done', function() {
                setTimeout(function() {
                    cooldown = false;
                }, 1500);
            });
        }

        function startCamera(video) {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
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
                    currentStream = stream; // ← lưu reference để stop sau

                    video.srcObject = stream;

                    // Stop nếu track bị revoke (user tắt permission trong Settings)
                    stream.getVideoTracks().forEach(function(track) {
                        track.addEventListener('ended', function() {
                            showCameraError('Camera bị ngắt. Vui lòng tải lại trang.');
                            scanning = false;
                        });
                    });

                    const playPromise = video.play();
                    if (playPromise !== undefined) {
                        playPromise
                            .then(function() {
                                requestAnimationFrame(function() {
                                    tick(video);
                                });
                            })
                            .catch(function(err) {
                                console.error('Play error:', err);
                                showPlayButton(video);
                            });
                    } else {
                        requestAnimationFrame(function() {
                            tick(video);
                        });
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

            requestAnimationFrame(function() {
                tick(video);
            });
        }

        function showCameraError(message) {
            const container = document.getElementById('camera-container');
            if (container) {
                container.innerHTML =
                    '<div class="absolute inset-0 flex flex-col items-center justify-center gap-3 px-6">' +
                    '<svg class="w-12 h-12 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" ' +
                    'd="M15 10l4.553-2.069A1 1 0 0121 8.868v6.264a1 1 0 01-1.447.894L15 14M3 8a2 2 0 00-2 2v4a2 2 0 002 2h8a2 2 0 002-2v-4a2 2 0 00-2-2H3z"/>' +
                    '</svg>' +
                    '<p class="text-slate-400 text-sm text-center">' + message + '</p>' +
                    '<p class="text-slate-600 text-xs text-center">iOS: Vào Cài đặt → Safari → Camera → Cho phép</p>' +
                    '</div>';
            }
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
                    requestAnimationFrame(function() {
                        tick(video);
                    });
                });
            });
            container.appendChild(btn);
        }
    });
</script>
@endpush

{{--
## Tóm tắt flow
```
Catechist mở /attendance/qr?classId=X&sessionId=Y&type=1
↓
Camera bật tự động (camera sau)
↓
jsQR scan liên tục mỗi frame
↓
Tìm thấy QR → emit handleQrScanned(token) lên Livewire
↓
Server validate: token hợp lệ? → trong lớp? → đã quét chưa?
↓
Overlay kết quả 2.5s (xanh/vàng/đỏ) → tự động tắt → quét tiếp
↓
Log hiện ngay bên dưới theo thời gian thực
--}}