@php
    $mouseTrailEnabled = true;

    if (\Illuminate\Support\Facades\Schema::hasTable('pos_settings')) {
        $mouseTrailEnabled = \Illuminate\Support\Facades\DB::table('pos_settings')
            ->where('key', 'global_mouse_trail')
            ->value('value') !== '0';
    }
@endphp

@if ($mouseTrailEnabled)
@once
    <style>
        .global-cursor-trail-aura,
        .global-cursor-trail-dot {
            position: fixed;
            top: 0;
            left: 0;
            pointer-events: none;
            border-radius: 9999px;
            transform: translate3d(-999px, -999px, 0);
            opacity: 0;
            transition: opacity 180ms ease;
            z-index: 10000;
        }

        .global-cursor-trail-aura {
            width: 30px;
            height: 30px;
            background: radial-gradient(circle, rgba(16, 128, 255, 0.46) 0%, rgba(0, 192, 255, 0.16) 40%, transparent 72%);
            filter: blur(2px);
            mix-blend-mode: screen;
        }

        .global-cursor-trail-dot {
            width: 10px;
            height: 10px;
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 0 18px rgba(0, 192, 255, 0.58);
            mix-blend-mode: screen;
        }

        .global-cursor-trail-aura.is-active,
        .global-cursor-trail-dot.is-active {
            opacity: 1;
        }

        .global-cursor-star {
            position: fixed;
            left: 0;
            top: 0;
            width: var(--star-size, 7px);
            height: var(--star-size, 7px);
            pointer-events: none;
            z-index: 9999;
            color: var(--star-color, #fff);
            filter: drop-shadow(0 0 8px rgba(0, 192, 255, 0.85));
            transform: translate3d(var(--star-x), var(--star-y), 0) rotate(var(--star-rotate, 0deg)) scale(0.5);
            animation: global-cursor-star-drift 760ms ease-out forwards;
        }

        .global-cursor-star::before,
        .global-cursor-star::after {
            content: "";
            position: absolute;
            inset: 50% auto auto 50%;
            background: currentColor;
            border-radius: 999px;
            transform: translate(-50%, -50%);
        }

        .global-cursor-star::before {
            width: 100%;
            height: 2px;
        }

        .global-cursor-star::after {
            width: 2px;
            height: 100%;
        }

        @keyframes global-cursor-star-drift {
            0% {
                opacity: 0;
                transform: translate3d(var(--star-x), var(--star-y), 0) rotate(var(--star-rotate, 0deg)) scale(0.35);
            }

            20% {
                opacity: 1;
            }

            100% {
                opacity: 0;
                transform: translate3d(calc(var(--star-x) + var(--star-dx)), calc(var(--star-y) + var(--star-dy)), 0) rotate(calc(var(--star-rotate, 0deg) + 95deg)) scale(1);
            }
        }
    </style>
@endonce

<div class="global-cursor-trail-aura" data-global-cursor-aura aria-hidden="true"></div>
<div class="global-cursor-trail-dot" data-global-cursor-dot aria-hidden="true"></div>

@once
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

            const aura = document.querySelector('[data-global-cursor-aura]');
            const dot = document.querySelector('[data-global-cursor-dot]');
            if (!aura || !dot) return;

            let targetX = window.innerWidth / 2;
            let targetY = window.innerHeight / 2;
            let currentX = targetX;
            let currentY = targetY;
            let active = false;
            let lastStarAt = 0;
            const starColors = ['#ffffff', '#00c0ff', '#1080ff', '#dff7ff'];

            const spawnStar = (x, y) => {
                const now = performance.now();
                if (now - lastStarAt < 38) return;
                lastStarAt = now;

                const star = document.createElement('span');
                const size = 4 + Math.random() * 8;
                const offsetX = (Math.random() - 0.5) * 34;
                const offsetY = (Math.random() - 0.5) * 34;
                const driftX = (Math.random() - 0.5) * 72;
                const driftY = -18 - Math.random() * 48;
                const color = starColors[Math.floor(Math.random() * starColors.length)];

                star.className = 'global-cursor-star';
                star.style.setProperty('--star-x', `${x + offsetX}px`);
                star.style.setProperty('--star-y', `${y + offsetY}px`);
                star.style.setProperty('--star-dx', `${driftX}px`);
                star.style.setProperty('--star-dy', `${driftY}px`);
                star.style.setProperty('--star-size', `${size}px`);
                star.style.setProperty('--star-color', color);
                star.style.setProperty('--star-rotate', `${Math.random() * 180}deg`);
                document.body.appendChild(star);
                star.addEventListener('animationend', () => star.remove(), { once: true });
            };

            const tick = () => {
                currentX += (targetX - currentX) * 0.18;
                currentY += (targetY - currentY) * 0.18;
                aura.style.transform = `translate3d(${currentX - 15}px, ${currentY - 15}px, 0)`;
                dot.style.transform = `translate3d(${targetX - 5}px, ${targetY - 5}px, 0)`;
                requestAnimationFrame(tick);
            };
            tick();

            window.addEventListener('pointermove', (event) => {
                targetX = event.clientX;
                targetY = event.clientY;
                if (!active) {
                    active = true;
                    aura.classList.add('is-active');
                    dot.classList.add('is-active');
                }
                spawnStar(event.clientX, event.clientY);
            }, { passive: true });

            window.addEventListener('pointerleave', () => {
                aura.classList.remove('is-active');
                dot.classList.remove('is-active');
            });
        });
    </script>
@endonce
@endif
