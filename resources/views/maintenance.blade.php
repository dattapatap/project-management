<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Under Maintenance | WMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #0f172a;
            color: #e2e8f0;
            overflow: hidden;
            position: relative;
        }

        /* Animated gradient background */
        body::before {
            content: '';
            position: fixed;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(ellipse at 20% 50%, rgba(99, 102, 241, 0.15) 0%, transparent 50%),
                        radial-gradient(ellipse at 80% 20%, rgba(139, 92, 246, 0.12) 0%, transparent 50%),
                        radial-gradient(ellipse at 60% 80%, rgba(59, 130, 246, 0.1) 0%, transparent 50%);
            animation: bgShift 15s ease-in-out infinite alternate;
            z-index: 0;
        }

        @keyframes bgShift {
            0%   { transform: translate(0, 0) rotate(0deg); }
            100% { transform: translate(-5%, -3%) rotate(3deg); }
        }

        /* Floating particles */
        .particles {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(99, 102, 241, 0.4);
            border-radius: 50%;
            animation: float linear infinite;
        }

        .particle:nth-child(1)  { left: 10%; animation-duration: 18s; animation-delay: 0s; width: 3px; height: 3px; }
        .particle:nth-child(2)  { left: 20%; animation-duration: 22s; animation-delay: 2s; width: 5px; height: 5px; background: rgba(139, 92, 246, 0.3); }
        .particle:nth-child(3)  { left: 35%; animation-duration: 16s; animation-delay: 4s; }
        .particle:nth-child(4)  { left: 50%; animation-duration: 20s; animation-delay: 1s; width: 6px; height: 6px; background: rgba(59, 130, 246, 0.3); }
        .particle:nth-child(5)  { left: 65%; animation-duration: 24s; animation-delay: 3s; width: 3px; height: 3px; }
        .particle:nth-child(6)  { left: 75%; animation-duration: 17s; animation-delay: 5s; background: rgba(139, 92, 246, 0.35); }
        .particle:nth-child(7)  { left: 85%; animation-duration: 21s; animation-delay: 0.5s; width: 5px; height: 5px; }
        .particle:nth-child(8)  { left: 45%; animation-duration: 19s; animation-delay: 6s; width: 4px; height: 4px; background: rgba(99, 102, 241, 0.25); }
        .particle:nth-child(9)  { left: 5%;  animation-duration: 23s; animation-delay: 2.5s; }
        .particle:nth-child(10) { left: 90%; animation-duration: 15s; animation-delay: 1.5s; width: 3px; height: 3px; background: rgba(59, 130, 246, 0.4); }

        @keyframes float {
            0%   { transform: translateY(100vh) scale(0); opacity: 0; }
            10%  { opacity: 1; }
            90%  { opacity: 1; }
            100% { transform: translateY(-10vh) scale(1); opacity: 0; }
        }

        .container {
            position: relative;
            z-index: 1;
            text-align: center;
            max-width: 600px;
            padding: 2rem;
        }

        /* Gear animation */
        .gear-wrapper {
            position: relative;
            width: 140px;
            height: 140px;
            margin: 0 auto 2.5rem;
        }

        .gear {
            position: absolute;
            border: 4px solid rgba(99, 102, 241, 0.5);
            border-radius: 50%;
        }

        .gear-outer {
            width: 140px;
            height: 140px;
            top: 0; left: 0;
            animation: spinCW 12s linear infinite;
            border-style: dashed;
            border-color: rgba(99, 102, 241, 0.3);
        }

        .gear-middle {
            width: 100px;
            height: 100px;
            top: 20px; left: 20px;
            animation: spinCCW 8s linear infinite;
            border-color: rgba(139, 92, 246, 0.4);
        }

        .gear-inner {
            width: 56px;
            height: 56px;
            top: 42px; left: 42px;
            animation: spinCW 5s linear infinite;
            border-color: rgba(59, 130, 246, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .gear-inner svg {
            width: 28px;
            height: 28px;
            fill: none;
            stroke: #818cf8;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        @keyframes spinCW  { from { transform: rotate(0deg); }   to { transform: rotate(360deg); } }
        @keyframes spinCCW { from { transform: rotate(0deg); }   to { transform: rotate(-360deg); } }

        h1 {
            font-size: 2.25rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #818cf8, #c084fc, #60a5fa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .subtitle {
            font-size: 1.1rem;
            color: #94a3b8;
            line-height: 1.7;
            margin-bottom: 2rem;
            font-weight: 400;
        }

        /* Progress bar */
        .progress-track {
            width: 280px;
            height: 6px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 3px;
            margin: 0 auto 2.5rem;
            overflow: hidden;
        }

        .progress-bar {
            width: 40%;
            height: 100%;
            background: linear-gradient(90deg, #6366f1, #8b5cf6, #3b82f6);
            border-radius: 3px;
            animation: progressPulse 2.5s ease-in-out infinite;
        }

        @keyframes progressPulse {
            0%   { width: 20%; opacity: 0.7; }
            50%  { width: 65%; opacity: 1; }
            100% { width: 20%; opacity: 0.7; }
        }

        /* Info cards */
        .info-row {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 2.5rem;
        }

        .info-card {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: 1rem 1.5rem;
            backdrop-filter: blur(10px);
            min-width: 160px;
            transition: transform 0.3s, border-color 0.3s;
        }

        .info-card:hover {
            transform: translateY(-2px);
            border-color: rgba(99, 102, 241, 0.3);
        }

        .info-card .label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #64748b;
            margin-bottom: 0.35rem;
            font-weight: 600;
        }

        .info-card .value {
            font-size: 0.95rem;
            font-weight: 600;
            color: #cbd5e1;
        }

        /* Contact link */
        .contact {
            font-size: 0.85rem;
            color: #64748b;
        }

        .contact a {
            color: #818cf8;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .contact a:hover {
            color: #a5b4fc;
        }

        /* Responsive */
        @media (max-width: 480px) {
            h1 { font-size: 1.75rem; }
            .subtitle { font-size: 0.95rem; }
            .info-row { flex-direction: column; align-items: center; }
            .gear-wrapper { width: 110px; height: 110px; }
            .gear-outer { width: 110px; height: 110px; }
            .gear-middle { width: 78px; height: 78px; top: 16px; left: 16px; }
            .gear-inner { width: 44px; height: 44px; top: 33px; left: 33px; }
        }
    </style>
</head>
<body>

    <!-- Floating particles -->
    <div class="particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>

    <div class="container">
        <!-- Animated gear icon -->
        <div class="gear-wrapper">
            <div class="gear gear-outer"></div>
            <div class="gear gear-middle"></div>
            <div class="gear gear-inner">
                <svg viewBox="0 0 24 24">
                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                </svg>
            </div>
        </div>

        <h1>Under Maintenance</h1>
        <p class="subtitle">
            We're currently performing scheduled maintenance to improve your experience.
            The system will be back online shortly.
        </p>

        <!-- Progress indicator -->
        <div class="progress-track">
            <div class="progress-bar"></div>
        </div>

        <!-- Info cards -->
        <div class="info-row">
            <div class="info-card">
                <div class="label">Status</div>
                <div class="value">🔧 Upgrading</div>
            </div>

        </div>


    </div>

</body>
</html>
